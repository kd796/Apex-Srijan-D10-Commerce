<?php

namespace Drupal\crescenttool_migrations\Plugin\migrate\process;

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
    if (!empty($value)) {
      $alt_text = $value->Name;
      foreach ($value->children() as $child) {
        // Listing Image.
        if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
          $assets[] = [
            'asset_id' => (string) $child->attributes()->AssetID,
            'drupal_file_path' => 'public://pim_images/' . (string) $child->attributes()->AssetID . '.jpg',
            'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . (string) $child->attributes()->AssetID . '.jpg',
          ];
        }
        // Product Images.
        if ($child->getName() === 'Product') {
          $product = $child;
          foreach ($product->children() as $product_child) {
            if ($product_child->getName() === 'AssetCrossReference') {
              $assets[] = [
                'asset_id' => (string) $product_child->attributes()->AssetID,
                'drupal_file_path' => 'public://pim_images/' . (string) $product_child->attributes()->AssetID . '.jpg',
                'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . (string) $product_child->attributes()->AssetID . '.jpg',
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
