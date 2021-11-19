<?php

namespace Drupal\crescenttool_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "get_product_classification_children"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_product_classification_children
 *   source: text
 * @endcode
 */
class GetProductClassificationChildren extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $terms_array = [];
    $parent_tid = NULL;
    $id_to_check = NULL;
    $properties = [];

    if (!empty($value->attributes()->ID) && !empty($value->Name)) {
      $classification_id = (string) $value->attributes()->ID;
      $parent_name = (string) $value->Name;
    }

    if (!empty($classification_id) && !empty($parent_name)) {
      $vid = 'product_classifications';
      $name = $parent_name;

      if (!empty($name)) {
        $properties['name'] = $name;
      }

      if (!empty($vid)) {
        $properties['vid'] = $vid;
      }

      $parent_term_array = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);

      foreach ($parent_term_array as $parent_term) {
        $id_to_check = NULL;

        if ($parent_term->hasField('field_classification_id')) {
          $field = $parent_term->get('field_classification_id');

          if (!empty($field->value)) {
            $id_to_check = $field->value;
          }
        }

        if ($id_to_check === $classification_id) {
          $parent_tid = $parent_term->id();
          $terms_array[] = [
            'vid' => $vid,
            'target_id' => $parent_tid
          ];

          $child_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadChildren($parent_tid);

          foreach ($child_terms as $child_term) {
            $terms_array[] = [
              'vid' => $vid,
              'target_id' => $child_term->id()
            ];
          }
        }
      }

      $terms_array = json_encode($terms_array);
    }

    return json_decode($terms_array, TRUE);
  }

}
