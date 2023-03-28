<?php

namespace Drupal\campbell_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageNotFoundOnFtpException;
use Drupal\apex_migrations\ImageOperations;
use Drupal\apex_migrations\Plugin\migrate\process\GetProductImages as ApexGetProductImages;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Row;
use League\Flysystem\FilesystemException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Provides a campbell_get_product_images plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: campbell_get_product_images
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "campbell_get_product_images"
 * )
 */
class GetProductImages extends ApexGetProductImages implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * The temp store object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private PrivateTempStoreFactory $tempStoreFactory;

  /**
   * The list of types of Asset Cross References we use as images.
   *
   * @var array|string[]
   */
  protected static array $allowedTypes = [
    'Beauty-Glamour Image', 'Cutaway Image', 'Highlight Image',
    'In Package Image', 'In Use Image', 'Literature', 'Part Shot 1',
    'Part Shot 2', 'Part Shot 3', 'Part Shot 4', 'Part Shot 5',
    'Product Logo', 'Secondary Image', 'Warning Image', 'ICON',
    'Dimensional Diagram'
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tempStoreFactory = $temp_store_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
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

      $store = $this->tempStoreFactory->get('apex_migrations');

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
              $query = $this->entityTypeManager->getStorage('media')->getQuery();
              $id = $query->condition('bundle', 'remote_video')
                ->condition('field_media_video_embed_field.value', $clean_url)
                ->execute();
              if (count($id)) {
                $this->mediaIds[] = [
                  'media_id' => reset($id)
                ];
                continue;
              }
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
   * @inerhitDoc
   */
  protected function scanElementForImages(mixed $element, string $level = 'Product Level') {
    foreach ($element as $item) {
      $attributeType = (string) $item->attributes()->Type;
      $assetId = (string) $item->attributes()->AssetID;

      if (empty($assetId)) {
        continue;
      }

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

        continue;
      }

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
