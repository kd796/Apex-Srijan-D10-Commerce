<?php

namespace Drupal\campbell_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\Plugin\migrate\process\GetProductFeaturesArray as ApexGetProductFeaturesArray;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Get Product Features Array.
 *
 * @MigrateProcessPlugin(
 *   id = "campbell_get_product_features_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: campbell_get_product_features_array
 *   source: text
 * @endcode
 */
class GetProductFeaturesArray extends ApexGetProductFeaturesArray {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $data = parent::transform($value, $migrate_executable, $row, $destination_property);

    foreach ($data as &$item) {
      if (!isset($item['copy_point'])) {
        continue;
      }
      $item['copy_point'] = strip_tags($item['copy_point']);
    }

    return $data;
  }
}
