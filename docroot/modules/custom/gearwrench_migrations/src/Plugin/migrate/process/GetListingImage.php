<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a get_listing_image plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: get_listing_image
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "get_listing_image"
 * )
 */
class GetListingImage extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $assets = [];
    $media_id = NULL;
    $alt_text = NULL;
    $sku = NULL;

    if (!empty($value)) {
      $sku_group = $value;
      $alt_text = $value->Name;
      $sku = $row->getSourceIdValues()['remote_sku'];

      foreach ($value->children() as $child) {
        if ($child->getName() === 'Product' && (string) $child->attributes()->ID === $sku) {
          foreach ($child->children() as $item) {
            if ($item->getName() === 'AssetCrossReference' && ((string) $item->attributes()->Type === 'Primary Image')) {
              $assetId = gearwrench_migrations_clean_asset_id((string) $item->attributes()->AssetID);

              if (!empty($assetId)) {
                $assets[] = [
                  'sku' => $sku,
                  'imagetype' => 'Product Level',
                  'asset_id' => $assetId,
                  'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
                  'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
                ];
              }
              else {
                $migrate_executable->saveMessage(
                  '[Listing Image] Product Level Asset ID empty for "'
                  . $sku
                );
              }
            }
          }
        }
      }

      if (empty($assets)) {
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
            else {
              $migrate_executable->saveMessage(
                '[Listing Image] SKU Group Level Asset ID empty for "'
                . $sku
              );
            }
          }
        }
      }

      if (empty($assets)) {
        $migrate_executable->saveMessage(
          '[Listing Image] While loading the primary image for "'
          . $sku . '" - Unable to find the primary image.'
        );
      }

      // Prep Directory.
      $image_directory = 'public://pim_images/';
      \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      foreach ($assets as $asset) {
        try {
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
              }
              else {
                $media = Media::create([
                  'bundle'           => 'image',
                  'uid'              => 1,
                  'field_media_image' => [
                    'target_id' => $file->id(),
                    'alt' => 'Image of ' . $alt_text
                  ],
                ]);

                $media->setName($asset['asset_id'])->setPublished(TRUE)->save();
                $media_id = $media->id();
              }
            }
          }
          else {
            $migrate_executable->saveMessage(
              '[Listing Image] During import of "'
              . $sku . '" - Unable to load image "'
              . $asset['remote_file_path']
              . '". Header response: "' . $headers_check . '"'
            );
          }
        }
        catch (\Exception $e) {
          $migrate_executable->saveMessage(
            '[Listing Image] During import of "'
            . $sku . '" - Unable to load the video. Error: ' . $e->getMessage()
          );
        }
      }
    }

    return $media_id;
  }

}
