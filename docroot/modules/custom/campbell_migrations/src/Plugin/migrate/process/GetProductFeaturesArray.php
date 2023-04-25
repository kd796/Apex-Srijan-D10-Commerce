<?php

namespace Drupal\campbell_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\Plugin\migrate\process\GetProductFeaturesArray as ApexGetProductFeaturesArray;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Get Product Features Array.
 *
 * @MigrateProcessPlugin(
 *   id = "campbell_get_product_features_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: campbell_get_product_features_array
 *   source: text
 * @endcode
 */
class GetProductFeaturesArray extends ApexGetProductFeaturesArray {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->copyArray = [];

    if (!empty($value)) {
      /** @var \SimpleXMLElement $child */

      // Find the parent Product and get all features.
      $product = $value->xpath('parent::Product');

      if (!empty($product[0])) {
        $product = $product[0];

        // We should now be in the "Product" tag that is the actual product.
        // Going for it's parent.
        $parentProductValues = $product->xpath('parent::Product/Values');

        if (!empty($parentProductValues[0])) {
          $this->findFeatures($parentProductValues[0], $migrate_executable, $row);
        }
      }

      // Overwrite child level features.
      $this->findFeatures($value, $migrate_executable, $row);

      // Strip HTML from copy points if any.
      foreach ($this->copyArray as &$item) {
        if (!isset($item['copy_point'])) {
          continue;
        }
        $item['copy_point'] = strip_tags($item['copy_point']);
      }

      // This forces it to sort in the order we want them in.
      ksort($this->copyArray);
      $list = json_encode($this->copyArray);
      return json_decode($list, TRUE);
    }

    return [];
  }

}
