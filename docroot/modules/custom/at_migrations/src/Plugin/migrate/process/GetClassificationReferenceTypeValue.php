<?php

namespace Drupal\at_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Classification reference type Value.
 *
 * @MigrateProcessPlugin(
 *   id = "at_get_classification_reference_type_value"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: at_get_classification_reference_type_value
 *   source: text
 *   attribute: text
 * @endcode
 */
class GetClassificationReferenceTypeValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $attribute_value = NULL;
    $attribute = $this->configuration['attribute'];
    $compare_atrribute = (isset($this->configuration['compare_atrribute'])
      && !empty($this->configuration['compare_atrribute'])) ? $this->configuration['compare_atrribute'] : "Type";
    $fetch_attribute = (isset($this->configuration['fetch_attribute'])
      && !empty($this->configuration['fetch_attribute'])) ? $this->configuration['fetch_attribute'] : "ClassificationID";
    if (!empty($value)) {
      $classification_reference = $value->xpath('parent::Product/ClassificationReference');
      $attribute_value = $this->findAttribute($classification_reference, $attribute, $compare_atrribute, $fetch_attribute);
    }

    if (empty($attribute_value)) {
      throw new MigrateSkipProcessException();
    }
    return [$attribute_value];
  }

  /**
   * Find the attribute given.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The XML object.
   * @param string $attribute
   *   The attribute ID.
   * @param string $compare_atrribute
   *   The compare attribute name.
   * @param string $fetch_attribute
   *   The fetch attribute name.
   *
   * @return string|null
   *   The value we found.
   */
  protected function findAttribute($element, $attribute, $compare_atrribute = "Type", $fetch_attribute = "ClassificationID") {
    $attribute_value = [];
    foreach ($element as $child) {
      if ($child->attributes()->{$compare_atrribute} == $attribute) {
        if (empty($child->attributes()->{$fetch_attribute})) {
          continue;
        }
        $attribute_value[] = (string) $child->attributes()->{$fetch_attribute};
      }
    }
    return $attribute_value;
  }

}
