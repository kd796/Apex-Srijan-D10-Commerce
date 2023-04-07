<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\cleco_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * This plugin works around data.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_get_media_type",
 *   handle_multiples = TRUE
 * )
 */
class GetMediaType extends ProcessPluginBase {

  use MigrationHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $id = (string) $row->getSourceProperty('asset_id');
    $message = "Started for ::" . $id;
    $logfile = $this->getDefaultLogfile('notification');
    $this->logMessage($logfile, $message);
    return $this->geMediaType($value);
  }

  /**
   * Get media type.
   */
  public function geMediaType($user_type_id) {
    $type = 'image';
    $user_type_list = [
      'pdf' => 'product_downloads',
      'xls' => 'product_downloads',
      'dxf file' => 'product_downloads',
      'stp file' => 'product_downloads',
      'word' => 'product_downloads',
      'xml' => 'product_downloads',
      'zip' => 'product_downloads',
      'igs file' => 'product_downloads',
      'exe' => 'product_downloads',
      'utf8' => 'product_downloads',
      'mp4' => 'video',
    ];
    if (array_key_exists(strtolower($user_type_id), $user_type_list)) {
      $type = $user_type_list[strtolower($user_type_id)];
    }
    return $type;
  }

}
