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
      $attribute_value = $this->findAttribute($value, $attribute);

      if (empty($attribute_value)) {
        $product = $value->xpath('parent::Product');

        if (!empty($product[0])) {
          $product = $product[0];
          $groupValues = $product->xpath('parent::Product/Values');
          $attribute_value = $this->findAttribute($groupValues[0], $attribute);
        }
      }
    }

    return $attribute_value;
  }

  /**
   * Find the attribute given.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The XML object.
   * @param string $attribute
   *   The attribute ID.
   *
   * @return string|null
   *   The value we found.
   */
  protected function findAttribute($element, $attribute) {
    $attribute_value = NULL;

    foreach ($element->children() as $child) {
      if ($child->attributes()->AttributeID == $attribute) {
        $attribute_value = (string) $child;
      }
    }

    return $attribute_value;
  }

}
