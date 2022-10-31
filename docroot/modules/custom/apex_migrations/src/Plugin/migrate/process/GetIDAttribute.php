<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get ID Attribute.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_id_attribute"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_id_attribute
 *   source: text
 * @endcode
 */
class GetIDAttribute extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($value)) {
      $product = $value->xpath('parent::Product');

      if (!empty($product[0])) {
        $product = $product[0];
        $attributes = $product->attributes();

        if (isset($attributes->ParentID)) {
          return $attributes->ParentID;
        }

        $parentProduct = $product->xpath('parent::Product');

        if (isset($parentProduct[0])) {
          $parentAttributes = $parentProduct[0]->attributes();

          if (isset($parentAttributes)) {
            return $parentAttributes->ID;
          }
        }
      }
    }

    throw new MigrateSkipRowException('No Group ID found for Product: ' . $attributes->ID);
  }

}
