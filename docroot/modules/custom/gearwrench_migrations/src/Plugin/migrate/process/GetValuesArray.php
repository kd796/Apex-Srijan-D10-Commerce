<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Values Array.
 *
 * @MigrateProcessPlugin(
 *   id = "get_values_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_values_array
 *   source: text
 * @endcode
 */
class GetValuesArray extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    foreach ($value->children() as $child) {
      if ($child->getName() !== 'MultiValue') {
        $vid = strtolower((string) $child->attributes()->AttributeID);
        $vid = str_replace(' ', '_', $vid);
        $values_array[] = [
          'vid' => $vid,
          'term_name' => (string) $child
        ];
      }
      else {
        $vid = strtolower((string) $child->attributes()->AttributeID);
        $vid = str_replace(' ', '_', $vid);
        $values_array[] = [
          'vid' => $vid,
          'term_name' => (string) $child->Value
        ];
      }
    }
    $values_array = json_encode($values_array);
    return json_decode($values_array, TRUE);
  }

}
