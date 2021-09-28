<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "get_all_product_classifications"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_all_product_classifications
 *   source: text
 * @endcode
 */
class GetAllProductClassifications extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // 1 to get only immediate children, NULL to load entire tree.
    $depth = $this->configuration['depth'];
    $all_terms_array = [];
    $properties = [];
    $vid = 'product_classifications';
    $name = $value;

    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $parent_term_array = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    foreach ($parent_term_array as $parent_term) {
      $parent_tid = $parent_term->id();
      $all_terms_array[] = [
        'vid' => $vid,
        'target_id' => $parent_tid
      ];

      // True will return loaded entities rather than ids.
      $load_entities = FALSE;
      $child_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, $depth, $load_entities);
      foreach ($child_terms as $child_term) {
        $all_terms_array[] = [
          'vid' => $vid,
          'target_id' => $child_term->tid
        ];
      }
    }
    $all_terms_array = json_encode($all_terms_array);

    return json_decode($all_terms_array, TRUE);
  }

}
