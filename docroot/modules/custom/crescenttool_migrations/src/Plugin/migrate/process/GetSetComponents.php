<?php

namespace Drupal\crescenttool_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "get_set_components"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_set_components
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
      foreach ($value->children() as $child) {
        if ($child->getName() === 'ProductCrossReference') {
          $set_component_skus[] = (string) $child->attributes()->ProductID;
        };
      }

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

      $set_component_ids = json_encode($set_component_ids);
    }

    return json_decode($set_component_ids, TRUE);
  }

}
