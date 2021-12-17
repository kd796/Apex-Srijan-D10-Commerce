<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a apex_get_listing_image_file plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: apex_get_remote_image_url
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_remote_image_url"
 * )
 */
class GetRemoteImageUrl extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $remote_asset_paths = [];

    if (!empty($value)) {
      foreach ($value->children() as $child) {
        if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
          $remote_asset_paths[] = [
            'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . (string) $child->attributes()->AssetID . '.jpg',
          ];
        }
      }
    }

    return $remote_asset_paths;
  }

}
