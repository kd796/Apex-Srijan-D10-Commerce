<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

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
              $assetId = gearwrench_migrations_clean_asset_id((string) $item->attributes()->AssetID);

              if (!empty($assetId) && $item->getName() === 'AssetCrossReference' && $attributeId === $sku) {
                if ($attributeType === 'Primary Image') {
                  $primary_image = [
                    'imagetype' => 'Product Level',
                    'asset_id' => $assetId,
                    'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
                    'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
                  ];
                }
                elseif (in_array($attributeType, $allowed_types)) {
                  $assets[] = [
                    'imagetype' => 'Product Level',
                    'asset_id' => $assetId,
                    'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
                    'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
                  ];
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

        if (empty($assets) && empty($primary_image)) {
          foreach ($sku_group->children() as $child) {
            if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
              $assetId = gearwrench_migrations_clean_asset_id((string) $child->attributes()->AssetID);

              if (!empty($assetId)) {
                $assets[] = [
                  'imagetype' => 'SKU Group Level',
                  'asset_id' => $assetId,
                  'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
                  'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
                ];
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

      if (empty($final_asset_list)) {
        $migrate_executable->saveMessage(
          '[Product Images] While loading the product images for "'
          . $sku . '" - Unable to find product images.'
        );
      }

      // Prep Directory.
      $image_directory = 'public://pim_images/';
      \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      foreach ($final_asset_list as $asset) {
        try {
          /*
           * @todo: Investigate to see if there is a way to check for the existing file before even sending a network request.
           *
           * Ideas:
           *  - Calculate the destination file name and check for that in media/files.
           *  - Store the origin file path somewhere and relate to imported file.
           */
          $headers_array = @get_headers($asset['remote_file_path']);
          $headers_check = $headers_array[0];

          if (strpos($headers_check, "200")) {
            $file_data = file_get_contents($asset['remote_file_path']);

            if ($file_data) {
              $file = file_save_data($file_data, $asset['drupal_file_path'], FileSystemInterface::EXISTS_REPLACE);

              // See if there's a media item we can use already.
              $usage = \Drupal::service('file.usage')->listUsage($file);

              if (count($usage) > 0 && !empty($usage['file']['media'])) {
                $media_id = array_key_first($usage['file']['media']);
                $media_ids[] = [
                  'media_id' => $media_id
                ];
              }
              else {
                $media = Media::create([
                  'bundle'           => 'image',
                  'uid'              => 1,
                  'field_media_image' => [
                    'target_id' => $file->id(),
                  ],
                ]);

                $media->setName($asset['asset_id'])->setPublished(TRUE)->save();
                $media_ids[] = [
                  'media_id' => $media->id()
                ];
              }
            }
          }
          else {
            $migrate_executable->saveMessage(
              '[Product Images] During import of "'
              . $sku . '" - Unable to load image "'
              . $asset['remote_file_path']
              . '". Header response: "' . $headers_check . '"'
            );
          }
        }
        catch (\Exception $e) {
          $migrate_executable->saveMessage(
            '[Product Images] During import of "'
            . $sku . '" - There was a problem loading image "'
            . $asset['remote_file_path']
            . '". Error: ' . $e->getMessage()
          );
        }
      }

      // Take all the videos and create media items.
      if (!empty($videos)) {
        foreach ($videos as $video) {
          try {
            // Process the video down to a normal URL, not a video series.
            $video_json = apex_migrations_get_youtube_data($video);
            $clean_url = apex_migrations_get_youtube_clean_url($video);

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
