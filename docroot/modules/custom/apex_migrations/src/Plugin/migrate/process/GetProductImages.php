<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageNotFoundOnFtpException;
use Drupal\apex_migrations\ImageOperations;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
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
    $assets = [];
    $media_ids = [];
    $alt_text = NULL;
    $sku = NULL;

    if (!empty($value)) {
      $alt_text = $value->Name;

      // Are there product level images?
      if ($value->getName() === 'Product') {
        $sku_group = $value;
        $primary_image = NULL;
        $videos = [];

        $allowed_types = [
          'Beauty-Glamour Image', 'Cutaway Image', 'Highlight Image',
          'In Package Image', 'In Use Image', 'Literature', 'Part Shot 1',
          'Part Shot 2', 'Part Shot 3', 'Part Shot 4', 'Part Shot 5',
          'Product Logo', 'Secondary Image', 'Warning Image', 'ICON'
        ];

        $sku = $row->getSourceIdValues()['remote_sku'];

        foreach ($sku_group->children() as $child) {
          if ($child->getName() === 'Product') {
            foreach ($child->children() as $item) {
              $attributeId = (string) $child->attributes()->ID;
              $attributeType = (string) $item->attributes()->Type;
              $assetId = ImageOperations::cleanAssetId((string) $item->attributes()->AssetID);

              if (!empty($assetId) && $item->getName() === 'AssetCrossReference' && $attributeId === $sku) {
                $media_id = ImageOperations::getImageMediaId($assetId);

                // If we find the file then we need to reference it in the return array.
                if (!empty($media_id)) {
                  $media_ids[] = [
                    'media_id' => $media_id
                  ];
                }
                else {
                  if ($attributeType === 'Primary Image') {
                    $primary_image = [
                      'imagetype' => 'Product Level',
                      'asset_id' => $assetId,
                      'remote_file_path' => $assetId . '.jpg',
                    ];
                  }
                  elseif (in_array($attributeType, $allowed_types)) {
                    $assets[] = [
                      'imagetype' => 'Product Level',
                      'asset_id' => $assetId,
                    ];
                  }
                }
              }

              // Now we have to add in the video stuff.
              if ($item->getName() === 'Values' && $attributeId === $sku) {
                foreach ($item->children() as $single_value) {
                  $single_value_attribute_id = (string) $single_value->attributes()->AttributeID;

                  if ($single_value->getName() === 'MultiValue' && $single_value_attribute_id == 'ExternalVideoURL') {
                    foreach ($single_value->children() as $single_value_child) {
                      $videos[] = $single_value_child;
                    }

                    // We only visited the "Values" attribute group so that we could get the video. So leave. NOW!
                    break;
                  }
                }
              }
            }
          }
        }

        if (empty($assets) && empty($media_ids) && empty($primary_image)) {
          foreach ($sku_group->children() as $child) {
            if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
              $assetId = ImageOperations::cleanAssetId((string) $child->attributes()->AssetID);

              if (!empty($assetId)) {
                $media_id = ImageOperations::getImageMediaId($assetId);

                // If we find the file then we need to reference it in the return array.
                if (!empty($media_id)) {
                  $media_ids[] = [
                    'media_id' => $media_id
                  ];
                }
                else {
                  // If we don't find the file then we need to download it.
                  $assets[] = [
                    'imagetype' => 'SKU Group Level',
                    'asset_id' => $assetId,
                  ];
                }
              }
            }
          }
        }
      }

      // Now plug in the Primary Image at the beginning of the array.
      $final_asset_list = [];

      // This final array will now allow the primary image to be the priority.
      if (!empty($primary_image)) {
        $final_asset_list[] = $primary_image;
      }

      foreach ($assets as $asset) {
        $final_asset_list[] = $asset;
      }

      if (empty($final_asset_list) && !empty($media_ids)) {
        if (empty($videos)) {
          return $media_ids;
        }
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
              $media_ids[] = [
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
      if (!empty($videos)) {
        foreach ($videos as $video) {
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
              $media_ids[] = [
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

    return $media_ids;
  }

}
