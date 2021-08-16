<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Product Features Array.
 *
 * @MigrateProcessPlugin(
 *   id = "get_product_features_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_product_features_array
 *   source: text
 * @endcode
 */
class GetProductFeaturesArray extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $copy_array = [];
    $value_to_skip = [
      'Brand',
      'ATT919',
      'ATT17711',
      'ATT493',
      'ATT499',
      'UNSPSC_V7.0901'
    ];
    foreach ($value->children() as $child) {
      if (!in_array((string) $child->attributes()->AttributeID, $value_to_skip)) {
        if ($child->getName() !== 'MultiValue') {
          $copy_array[] = [
            'copy_point' => (string) $child
          ];
        }
        else {
          $copy_array[] = [
            'copy_point' => (string) $child->Value,
          ];
        }
      }
    }
    $copy_array = json_encode($copy_array);
    return json_decode($copy_array, TRUE);
  }

}
