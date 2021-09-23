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
      $alt_text = $value->Name;
      foreach ($value->children() as $child) {
        if ($child->getName() === 'Product') {
          $sku = (string) $child->attributes()->ID;
        }
      }
      foreach ($value->children() as $child) {
        if ($child->getName() === 'Product' && (string) $child->attributes()->ID === $sku) {
          foreach ($child->children() as $item) {
            if ($item->getName() === 'AssetCrossReference' && ((string) $item->attributes()->Type === 'Primary Image')) {
              $assets[] = [
                'sku' => $sku,
                'imagetype' => 'Product Level',
                'asset_id' => (string) $item->attributes()->AssetID,
                'drupal_file_path' => 'public://pim_images/' . (string) $item->attributes()->AssetID . '.jpg',
                'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . (string) $item->attributes()->AssetID . '.jpg',
              ];
            }
          }
        }
      }
      if (empty($assets)) {
        foreach ($value->children() as $child) {
          if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
            $assets[] = [
              'sku' => $sku,
              'imagetype' => 'SKU Group Level',
              'asset_id' => (string) $child->attributes()->AssetID,
              'drupal_file_path' => 'public://pim_images/' . (string) $child->attributes()->AssetID . '.jpg',
              'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . (string) $child->attributes()->AssetID . '.jpg',
            ];
          }
        }
      }

      // Prep Directory.
      $image_directory = 'public://pim_images/';
      \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      foreach ($assets as $asset) {
        $headers_array = @get_headers($asset['remote_file_path']);
        $headers_check = $headers_array[0];
        if (strpos($headers_check, "200")) {
          $file_data = file_get_contents($asset['remote_file_path']);
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
    }
    return $media_id;
  }

}
