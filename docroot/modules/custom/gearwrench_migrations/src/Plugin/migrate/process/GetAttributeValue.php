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
    if (!empty($value)) {
      foreach ($value->children() as $child) {
        if ($child->attributes()->AttributeID == $attribute) {
          if ($attribute === 'ATT17339' || $attribute === 'ATT16491') {
            if ((string) $child === 'Yes') {
              $attribute_value = 1;
            }
            elseif ((string) $child === 'No') {
              $attribute_value = 0;
            }
          }
          else {
            $attribute_value = (string) $child;
          }
        }
      }
    }
    return $attribute_value;
  }

}
