<?php

namespace Drupal\apex_tools_instagram_feed\Services;

use Exceptions\FacebookSDKException;
use Exceptions\FacebookResponseException;
use Facebook\Facebook;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use GuzzleHttp\Exception\RequestException;
use Facebook\Exceptions;

/**
 * ApexToolsInstagramFeedServiceProvider service.
 */
class ApexToolsInstagramFeedServiceProvider {

  /**
   * The http client provider.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * A LoggerChannelInterface viewsreference_filter.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The base url of the endpoint.
   *
   * @var string
   */
  private $endpointBase;

  /**
   * The app id.
   *
   * @var string
   */
  private $appId;

  /**
   * The app secret.
   *
   * @var string
   */
  private $appSecret;

  /**
   * The maximum number of items we import.
   *
   * @var int
   */
  private $importLimit = 5;

  /**
   * Constructs a new object.
   */
  public function __construct() {
    $this->logger = \Drupal::logger('apex_tools_instagram_feed');
    $this->httpClient = \Drupal::httpClient();
    $this->messenger = \Drupal::messenger();
    $this->endpointBase = 'https://graph.facebook.com/v12.0/';
    $this->appId = \Drupal::state()->get('app_id');
    $this->appSecret = \Drupal::state()->get('app_secret');
  }

  /**
   * Get Access Token from Facebook.
   */
  public function getAccessToken() {
    $access_token = NULL;

    // Create Facebook object.
    $facebook_creds = [
      'app_id' => $this->appId,
      'app_secret' => $this->appSecret,
      'default_graph_version' => 'v12.0',
      'persistent_data_handler' => 'session'
    ];

    // Create FB Object.
    $facebook = new Facebook($facebook_creds);

    // Set FB Services.
    $helper = $facebook->getRedirectLoginHelper();
    $oAuth2Client = $facebook->getOAuth2Client();

    try {
      $access_token = $helper->getAccessToken();
    }
    catch (FacebookResponseException $e) {
      $this->logger->warning('Graph returned an error "%error".', ['%error' => $e->getMessage()]);
      // Validation error.
    }
    catch (FacebookSDKException $e) {
      $this->logger->warning('Facebook SDK returned an error "%error".', ['%error' => $e->getMessage()]);
    }

    // Exchange short for long.
    if (!$access_token->isExpired()) {
      try {
        $access_token = $oAuth2Client->getLongLivedAccessToken($access_token);
        $access_token = (string) $access_token;

        \Drupal::state()->set('access_token', $access_token);
      }
      catch (FacebookSDKException $e) {
        $this->logger->warning('Error getting long lived access token "%error".', ['%error' => $e->getMessage()]);
      }
    }

    return $access_token;
  }

  /**
   * Get Facebook Page ID.
   */
  public function getFaceBookPageId() {
    $facebook_page_id = NULL;
    $access_token = \Drupal::state()->get('access_token');
    $facebook_account_endpoint = $this->endpointBase . 'me/accounts';

    // Endpoint Params.
    $pages_params = [
      'access_token' => (string) $access_token
    ];

    // Add Params to Endpoint.
    $facebook_account_endpoint .= '?' . http_build_query($pages_params);

    try {
      $pages_response = $this->httpClient->get($facebook_account_endpoint);
      $pages_response_data = json_decode($pages_response->getBody()->getContents());
      $facebook_page_data = $pages_response_data->data;
      $facebook_page_id = $facebook_page_data[0]->id;
      \Drupal::state()->set('facebook_page_id', $facebook_page_id);
    }
    catch (RequestException $e) {
      $this->logger->warning('Failed to get page_id due to "%error".', ['%error' => $e->getMessage()]);
      $this->messenger->addStatus(t('Failed to get page_id due to "%error".', ['%error' => $e->getMessage()]));
    }
    return $facebook_page_id;
  }

  /**
   * Get Instagram Account ID.
   */
  public function getInstagramAccountId($facebook_page_id) {
    $instagram_account_id = NULL;

    // Get Instagram Account ID.
    $instagram_account_endpoint = $this->endpointBase . $facebook_page_id;

    // Endpoint Params.
    $instagram_params = [
      'fields' => 'instagram_business_account',
      'access_token' => \Drupal::state()->get('access_token')
    ];

    // Add Params to Endpoint.
    $instagram_account_endpoint .= '?' . http_build_query($instagram_params);

    try {
      $instagram_account_response = $this->httpClient->get($instagram_account_endpoint);
      $instagram_account_response_data = json_decode($instagram_account_response->getBody()->getContents());
      $instagram_account_id = $instagram_account_response_data->instagram_business_account->id;

      \Drupal::state()->set('instagram_account_id', $instagram_account_id);
    }
    catch (RequestException $e) {
      $this->logger->warning('Failed to get instagram_account_id due to "%error".', ['%error' => $e->getMessage()]);
      $this->messenger->addStatus(t('Failed to get instagram_account_id due to "%error".', ['%error' => $e->getMessage()]));
    }

    return $instagram_account_id;
  }

  /**
   * Get Instagram Media Items.
   */
  public function getInstagramMediaItems() {
    $instagram_account_id = \Drupal::state()->get('instagram_account_id');

    // Get Media.
    $instagram_media_endpoint = $this->endpointBase . $instagram_account_id . '/media';
    $instagram_media_params = [
      'access_token' => \Drupal::state()->get('access_token'),
    ];

    // Add params to endpoint.
    $instagram_media_endpoint .= '?' . http_build_query($instagram_media_params);

    try {
      $instagram_media_response = $this->httpClient->get($instagram_media_endpoint);
      $instagram_media_response_data = json_decode($instagram_media_response->getBody()->getContents());

      /*
       * The idea here is that we will skip over the responses that say VIDEO
       * for the MEDIA TYPE until we can support pulling that in. Thus, we grab
       * more than we would actually need in case we skip some.
       */
      $howManyToLoad = $this->importLimit * 2;
      $media_items = $instagram_media_response_data->data;
      $media_items = array_slice($media_items, 0, $howManyToLoad, TRUE);
      $this->clearSocialPosts();
      $imported = 0;

      foreach ($media_items as $media_item) {
        $instagram_media_item_endpoint = $this->endpointBase . $media_item->id;

        $instagram_media_item_params = [
          'fields' => 'caption,comments_count,id,ig_id,is_comment_enabled,like_count,media_product_type,media_type,media_url,owner,permalink,shortcode,thumbnail_url,timestamp,username,video_title',
          'access_token' => \Drupal::state()->get('access_token'),
        ];

        $instagram_media_item_endpoint .= '?' . http_build_query($instagram_media_item_params);

        try {
          $instagram_media_item_response = $this->httpClient->get($instagram_media_item_endpoint);
          $instagram_media_item_response_data = json_decode($instagram_media_item_response->getBody()->getContents());

          if ($instagram_media_item_response_data->media_type !== 'VIDEO') {
            $this->createSocialPosts($instagram_media_item_response_data);
            $imported++;

            // When we have reached the import limit, lets leave this loop.
            if ($imported === $this->importLimit) {
              break;
            }
          }
        }
        catch (RequestException $e) {
          $this->logger->warning('Failed to get instagram_media_item due to "%error".', ['%error' => $e->getMessage()]);
          $this->messenger->addStatus(t('Failed to get instagram_media_item due to "%error".', ['%error' => $e->getMessage()]));
        }
      }
    }
    catch (RequestException $e) {
      $this->logger->warning('Failed to get instagram_media_response due to "%error".', ['%error' => $e->getMessage()]);
      $this->messenger->addStatus(t('Failed to get instagram_media_response due to "%error".', ['%error' => $e->getMessage()]));
    }
  }

  /**
   * Delete Social Posts.
   */
  public function clearSocialPosts() {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    // Delete all social posts and associated media items.
    $social_query = \Drupal::entityQuery('node')
      ->condition('type', 'social_post');
    $social_nids = $social_query->execute();
    $posts = $node_storage->loadMultiple($social_nids);

    if (!empty($posts)) {
      foreach ($posts as $post) {
        // Delete Associated Media and Files.
        if (!empty($post->get('field_media')->target_id)) {
          // Get fid from field_media.
          $mid = $post->get('field_media')->target_id;
          $media = Media::load($mid);
          $fid = $media->field_media_image->target_id;

          if ($mid) {
            $media_storage_handler = \Drupal::entityTypeManager()->getStorage('media');
            $media_item = $media_storage_handler->load($mid);

            if ($media_item) {
              $media_storage_handler->delete([$media_item]);
            }
          }

          if ($fid) {
            $file_storage_handler = \Drupal::entityTypeManager()->getStorage('file');
            $file = $file_storage_handler->load($fid);

            if ($file) {
              $file_storage_handler->delete([$file]);
            }
          }
        }

        // Delete Post.
        $post->delete();
      }
    }
  }

  /**
   * Create Social Posts.
   */
  public function createSocialPosts($instagram_media_item_response_data) {
    $instagram_post_id = $instagram_media_item_response_data->id;
    $post_title = 'Instagram Post | ' . $instagram_post_id;
    $post_url = $instagram_media_item_response_data->permalink;
    $post_media_url = $instagram_media_item_response_data->media_url;

    if (!empty($instagram_media_item_response_data->caption)) {
      $post_content = $instagram_media_item_response_data->caption;
    }
    else {
      $post_content = NULL;
    }

    $post_media_url_path = parse_url($post_media_url);
    $post_media_url_basename = basename($post_media_url_path['path']);

    // Prep Directory.
    $image_directory = 'public://social_images/' . date("Y-m") . '/';
    \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    // Get File.
    $headers_array = @get_headers($post_media_url);
    $headers_check = $headers_array[0];

    if (strpos($headers_check, "200")) {
      $file_data = file_get_contents($post_media_url);
      $file = file_save_data($file_data, $image_directory . $post_media_url_basename, FileSystemInterface::EXISTS_REPLACE);

      // See if there's a media item we can use already.
      $usage = \Drupal::service('file.usage')->listUsage($file);

      if (count($usage) > 0 && !empty($usage['file']['media'])) {
        $media_id = array_key_first($usage['file']['media']);
      }
      else {
        $media = Media::create([
          'bundle'           => 'image',
          'uid'              => 1,
          'field_media_image' => [
            'target_id' => $file->id(),
            'alt' => 'Image of ' . $post_title
          ],
        ]);

        $media->setName($post_title)->setPublished(TRUE)->save();
        $media_id = $media->id();
      }
    }

    $node = Node::create([
      'type'        => 'social_post',
      'title'       => $post_title,
      'body'        => $post_content,
      'field_media' => [
        'target_id' => $media_id,
      ],
      'field_post_url' => $post_url,
    ]);

    $node->save();
    $this->messenger->addStatus(t('A post with a title of "%title" has been created.', ['%title' => $post_title]));
  }

}
