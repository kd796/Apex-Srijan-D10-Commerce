<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Get Related Products.
 *
 *
 * @MigrateProcessPlugin(
 *   id = "get_related_products"
 * )
 */
class GetRelatedProducts extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $related_product_skus = [];
    $related_product_ids = [];
    if (!empty($value)) {
      foreach ($value->children() as $child) {
        if ((string) $child->attributes()->AttributeID === 'ATT207') {
          $attribute_value = (string) $child;
          $related_product_skus = explode('  ', $attribute_value);
        }
      }
      foreach ($related_product_skus as $related_product_sku) {
        $related_product = \Drupal::entityTypeManager()->getStorage('node')
          ->loadByProperties(['title' => $related_product_sku]);
        $related_product = reset($related_product);
        if (!empty($related_product->nid->value)) {
          $related_product_ids[] = [
            'target_id' => $related_product->nid->value,
          ];
        }
      }
      $related_product_ids = json_encode($related_product_ids);
    }
    return json_decode($related_product_ids, TRUE);
  }

}
