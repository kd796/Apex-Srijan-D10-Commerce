<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Get Attribute Value.
 *
 * @MigrateProcessPlugin(
 *   id = "get_attribute_value"
 * )
 */
class GetAttributeValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $attribute_value = NULL;
    $attribute = $this->configuration['attribute'];
    foreach ($value->children() as $child) {
      if ($child->attributes()->AttributeID == $attribute) {
        $attribute_value = (string) $child;
      }
    }
    return $attribute_value;
  }

}
