<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_set_components"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_set_components
 *   source: text
 * @endcode
 */
class GetSetComponents extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $set_component_skus = [];
    $set_component_ids = [];

    if (!empty($value)) {
      /*
       * So this is kinda ugly. If, in the yml, we try to back up to the Product
       * level with "../Product" then it doesn't always return the current
       * product. But if we instead target the first instance of
       * ProductCrossReference as a tag just to get a SimpleXMLObject, then we
       * can use xpath to go back up to the current Product and search for all
       * of them. That is what I did here.
       */
      $product_cross_ref = $value->xpath('..//ProductCrossReference');

      if (!empty($product_cross_ref)) {
        foreach ($product_cross_ref as $ref) {
          $set_component_skus[] = (string) $ref->attributes()->ProductID;
        }
      }

      if (!empty($set_component_skus)) {
        foreach ($set_component_skus as $component_sku) {
          $set_component = \Drupal::entityTypeManager()->getStorage('node')
            ->loadByProperties(['title' => $component_sku]);
          $set_component = reset($set_component);

          if (is_object($set_component) && !empty($set_component->nid->value)) {
            $set_component_ids[] = [
              'target_id' => $set_component->nid->value,
            ];
          }
        }
      }
    }

    $set_component_ids = json_encode($set_component_ids);
    return json_decode($set_component_ids, TRUE);
  }

}
