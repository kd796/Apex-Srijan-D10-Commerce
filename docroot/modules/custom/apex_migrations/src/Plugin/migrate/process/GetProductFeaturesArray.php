<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Product Features Array.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_product_features_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_product_features_array
 *   source: text
 * @endcode
 */
class GetProductFeaturesArray extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $copy_array = [];
    $attribute_to_position = [
      'ATT100' => 0,
      'ATT101' => 1,
      'ATT102' => 2,
      'ATT103' => 3,
      'ATT104' => 4,
      'ATT105' => 5,
      'ATT106' => 6,
      'ATT107' => 7,
      'ATT108' => 8,
      'ATT109' => 9,
      'ATT22085' => 10,
      'ATT22086' => 11,
      'ATT22087' => 12,
      'ATT22088' => 13,
      'ATT22089' => 14,
      'ATT22090' => 15,
      'ATT22091' => 16,
      'ATT22092' => 17,
      'ATT22093' => 18,
      'ATT22094' => 19,
      'ATT17711' => 20,
    ];
    $attribute_ids_to_use = array_keys($attribute_to_position);

    if (!empty($value)) {
      /** @var \SimpleXMLElement $child */
      foreach ($value->children() as $child) {
        $att_id = (string) $child->attributes()->AttributeID;
        if (in_array($att_id, $attribute_ids_to_use)) {
          // Get the sorting position.
          $delta = $attribute_to_position[$att_id];

          if ($child->getName() !== 'MultiValue') {
            $copy_array[$delta] = [
              'copy_point' => (string) $child,
            ];
          }
          else {
            $copy_array[$delta] = [
              'copy_point' => (string) $child->Value,
            ];
          }
        }
      }

      // This forces it to sort in the order we want them in.
      ksort($copy_array);
      $copy_array = json_encode($copy_array);
      return json_decode($copy_array, TRUE);
    }

    return $copy_array;
  }

}
