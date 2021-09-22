<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a get_product_images plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: get_product_images
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "get_product_images"
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
        foreach ($sku_group->children() as $child) {
          if ($child->getName() === 'Product') {
            $sku = (string) $child->attributes()->ID;
          }
        }
        foreach ($sku_group->children() as $child) {
          if ($child->getName() === 'Product') {
            foreach ($child->children() as $item) {
              if ($item->getName() === 'AssetCrossReference' && (string) $child->attributes()->ID === $sku && ((string) $item->attributes()->Type === 'Primary Image' || (string) $item->attributes()->Type === 'Beauty-Glamour Image')) {
                $assets[] = [
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
          foreach ($sku_group->children() as $child) {
            if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
              $assets[] = [
                'imagetype' => 'SKU Group Level',
                'asset_id' => (string) $child->attributes()->AssetID,
                'drupal_file_path' => 'public://pim_images/' . (string) $child->attributes()->AssetID . '.jpg',
                'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . (string) $child->attributes()->AssetID . '.jpg',
              ];
            }
          }
        }
      }

      foreach ($assets as $asset) {
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
      }
    }

    return $media_ids;
  }

}
