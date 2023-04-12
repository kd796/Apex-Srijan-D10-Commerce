<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;

/**
 * Get Attribute Meta Value.
 *
 * @MigrateProcessPlugin(
 *   id = "get_category_meta_value"
 * )
 */
class GetCategoryMetaValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $attribute = $this->configuration['allowed_attributes'];
    $metas = [];

    if (!empty($value)) {
      if (in_array("ATT880759", $attribute)) {
        $title_value = $this->findAttribute($value, 'ATT880759');
      }
      if (in_array("ATT880758", $attribute)) {
        $desc_value = $this->findAttribute($value, 'ATT880758');
      }
      $metas['title'] = $title_value;
      $metas['description'] = $desc_value;
    }

    return $metas ? serialize($metas) : [];
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
