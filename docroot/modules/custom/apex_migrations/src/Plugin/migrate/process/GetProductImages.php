<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageNotFoundOnFtpException;
use Drupal\apex_migrations\ImageOperations;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use League\Flysystem\FilesystemException;

/**
 * Provides a apex_get_product_images plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: apex_get_product_images
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_product_images"
 * )
 */
class GetProductImages extends ProcessPluginBase {

  /**
   * The image operations class.
   *
   * @var \Drupal\apex_migrations\ImageOperations
   */
  protected ImageOperations $imageOps;

  /**
   * The list of types of Asset Cross References we use as images.
   *
   * @var array|string[]
   */
  protected static array $allowedTypes = [
    'Beauty-Glamour Image', 'Cutaway Image', 'Highlight Image',
    'In Package Image', 'In Use Image', 'Literature', 'Part Shot 1',
    'Part Shot 2', 'Part Shot 3', 'Part Shot 4', 'Part Shot 5',
    'Product Logo', 'Secondary Image', 'Warning Image', 'ICON', 'eGraphics'
  ];

  /**
   * The media IDs for existing media elements.
   *
   * @var array
   */
  protected $mediaIds = [];

  /**
   * The media ID for an existing primary image.
   *
   * @var null|int|string
   */
  protected $primaryImageMediaId;

  /**
   * The primary image asset to download.
   *
   * @var array
   */
  protected $primaryImageAsset = [];

  /**
   * The list of image assets to download.
   *
   * @var array
   */
  protected $imageAssets = [];

  /**
   * The list of video URLs to use on the product.
   *
   * @var array
   */
  protected $videos = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->imageOps = new ImageOperations();

    // Prep Directory.
    ImageOperations::prepImageDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->mediaIds = [];
    $this->primaryImageMediaId = NULL;
    $this->primaryImageAsset = [];
    $this->imageAssets = [];
    $this->videos = [];
    $sku = NULL;

    // $value is the Product.
    if (!empty($value)) {
      $product = $value->xpath('parent::Product');

      if (!empty($product[0])) {
        $product = $product[0];
        $alt_text = $product->Name;
      }

      $asset_cross_reference = $value->xpath('parent::Product/AssetCrossReference');

      // Are there product level images?
      $sku = $row->getSourceIdValues()['remote_sku'];
      $this->scanElementForImages($asset_cross_reference);

      if (!empty($product)) {
        $multivalue_elements = $product->xpath('.//Values/MultiValue');

        if (!empty($multivalue_elements)) {
          $this->scanForVideos($multivalue_elements);
        }
      }

      if (!empty($product)
        && (empty($this->imageAssets) || empty($this->primaryImageMediaId))) {
        $parentProductAssets = $product->xpath('parent::Product/AssetCrossReference');

        if (!empty($parentProductAssets)) {
          // If we didn't find anything at the product level then we scan at the parent level.
          if (empty($this->imageAssets) && empty($this->mediaIds)) {
            $this->scanElementForImages($parentProductAssets, 'SKU Group Level');
          }

          if (empty($this->videos)) {
            $parent = $product->xpath('parent::Product');

            if (!empty($parent[0])) {
              $multivalue_elements = $parent[0]->xpath('.//Values/MultiValue');

              if (!empty($multivalue_elements)) {
                $this->scanForVideos($multivalue_elements);
              }
            }
          }

          // We want to make sure we have a primary image selected.
          if (empty($this->primaryImageMediaId) && empty($this->primaryImageAsset)) {
            $this->scanParentForPrimaryImage($parentProductAssets);
          }
        }
      }

      // Now plug in the Primary Image at the beginning of the array.
      $final_asset_list = [];

      // This final array will now allow the primary image to be the priority.
      if (!empty($this->primaryImageAsset)) {
        $final_asset_list[] = $this->primaryImageAsset;
      }

      foreach ($this->imageAssets as $asset) {
        $final_asset_list[] = $asset;
      }

      // If we have no assets to download, no videos to process, and media IDs available then return.
      if (empty($final_asset_list) && !empty($this->mediaIds) && empty($this->videos)) {
        if (!empty($this->primaryImageMediaId)) {
          // Combines the media ID of the primary image with the rest of the media IDs while placing at the beginning of the array.
          return array_merge([0 => ['media_id' => $this->primaryImageMediaId]], $this->mediaIds);
        }

        return $this->mediaIds;
      }

      $store = \Drupal::service('tempstore.private')->get('apex_migrations');

      if (!empty($final_asset_list) && $store->get('image_server_available') === TRUE) {
        foreach ($final_asset_list as $asset) {
          try {
            $media_id = $this->imageOps->getAndSaveImageMedia($asset['asset_id'], $alt_text);

            if ($media_id === FALSE) {
              $migrate_executable->saveMessage(
                '[Product Images] During import of "'
                . $sku . '" - Unable to load image "'
                . $asset['asset_id'] . '.jpg"'
              );
            }
            else {
              $this->mediaIds[] = [
                'media_id' => $media_id
              ];
            }
          }
          catch (ImageNotFoundOnFtpException $e) {
            $migrate_executable->saveMessage(
              '[Product Images] During import of "'
              . $sku . '" - Unable to find the image on the FTP server for asset: "'
              . $asset['asset_id'] . '.jpg". '
              . $e->getMessage()
            );
          }
          catch (\Exception | FilesystemException $e) {
            $migrate_executable->saveMessage(
              '[Product Images] During import of "'
              . $sku . '" - There was a problem loading image "'
              . $asset['asset_id'] . '.jpg". Error: '
              . $e->getMessage()
            );
          }
        }
      }

      // Take all the videos and create media items.
      if (!empty($this->videos)) {
        foreach ($this->videos as $video) {
          try {
            // Process the video down to a normal URL, not a video series.
            $video_json = ImageOperations::getYoutubeData($video);
            $clean_url = ImageOperations::getYoutubeCleanUrl($video);

            if (!empty($video_json->title)) {
              $media = Media::create([
                'bundle'           => 'remote_video',
                'uid'              => 1,
                'field_media_video_embed_field' => [
                  'value' => $clean_url,
                ],
              ]);

              $media->setName($video_json->title)->setPublished(TRUE)->save();
              $this->mediaIds[] = [
                'media_id' => $media->id()
              ];
            }
            else {
              $migrate_executable->saveMessage(
                '[Product Images] During import of "'
                . $sku . '" - Unable to retrieve YouTube video metadata for url: '
                . $clean_url
              );
            }
          }
          catch (\Exception $e) {
            $migrate_executable->saveMessage(
              '[Product Images] During import of "'
              . $sku . '" - Unable to load the video. Error: ' . $e->getMessage()
            );
          }
        }
      }
    }

    if (!empty($this->primaryImageMediaId)) {
      // Combines the media ID of the primary image with the rest of the media IDs while placing at the beginning of the array.
      return array_merge([0 => ['media_id' => $this->primaryImageMediaId]], $this->mediaIds);
    }

    if (!empty($this->mediaIds)) {
      return $this->mediaIds;
    }

    throw new MigrateSkipProcessException();
  }

  /**
   * Scans a given XML element for child elements that contain images.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The SimpleXMLElement we are processing.
   * @param string $level
   *   The level we want to indicate for reporting purposes.
   */
  private function scanElementForImages(mixed $element, string $level = 'Product Level') {
    foreach ($element as $item) {
      $attributeType = (string) $item->attributes()->Type;
      $assetId = (string) $item->attributes()->AssetID;

      if (!empty($assetId)) {
        $media_id = ImageOperations::getImageMediaId($assetId);

        // If we find the file then we need to reference it in the return array.
        if (!empty($media_id)) {
          if ($attributeType === 'Primary Image') {
            $this->primaryImageMediaId = $media_id;
          }
          else {
            $this->mediaIds[] = [
              'media_id' => $media_id
            ];
          }
        }
        else {
          if ($attributeType === 'Primary Image') {
            $this->primaryImageAsset = [
              'imagetype' => $level,
              'asset_id' => $assetId,
              'remote_file_path' => $assetId . '.jpg',
            ];
          }
          elseif (in_array($attributeType, self::$allowedTypes)) {
            $this->imageAssets[] = [
              'imagetype' => $level,
              'asset_id' => $assetId,
            ];
          }
        }
      }
    }
  }

  /**
   * Scan for videos directly.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The array of multivalue elements.
   */
  private function scanForVideos(mixed $element) {
    if (!empty($element)) {
      foreach ($element as $single_value) {
        $single_value_attribute_id = (string) $single_value->attributes()->AttributeID;

        if ($single_value->getName() === 'MultiValue' && $single_value_attribute_id == 'ExternalVideoURL') {
          foreach ($single_value->children() as $single_value_child) {
            $this->videos[] = $single_value_child;
          }

          // We only visited the "Values" attribute group so that we could get the video. So leave. NOW!
          break;
        }
      }
    }
  }

  /**
   * Scan the parent element for the primary image.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The parent element.
   */
  private function scanParentForPrimaryImage(mixed $element) {
    foreach ($element as $item) {
      $attributeType = (string) $item->attributes()->Type;
      $assetId = (string) $item->attributes()->AssetID;

      if (!empty($assetId)) {
        if ($attributeType === 'Primary Image') {
          $media_id = ImageOperations::getImageMediaId($assetId);

          // If we find the file then we need to reference it in the return array.
          if (!empty($media_id)) {
            $this->primaryImageMediaId = $media_id;
          }
          else {
            $this->primaryImageAsset = [
              'imagetype' => 'SKU Group Level',
              'asset_id' => $assetId,
              'remote_file_path' => $assetId . '.jpg',
            ];
          }
        }
      }
    }
  }

}
