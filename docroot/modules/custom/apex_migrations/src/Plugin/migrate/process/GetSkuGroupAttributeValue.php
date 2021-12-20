<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get SKU Group Attribute Value.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_sku_group_attribute_value"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_sku_group_attribute_value
 *   source: text
 *   attribute: text
 * @endcode
 */
class GetSkuGroupAttributeValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $attribute_value = NULL;
    $attribute = $this->configuration['attribute'];

    if (!empty($value)) {
      foreach ($value->children() as $child) {
        if ($child->attributes()->AttributeID == $attribute) {
          $attribute_value = (string) $child;
        }
      }
    }

    return $attribute_value;
  }

}
