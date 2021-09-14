<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\taxonomy\Entity\Term;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Classifications Array.
 *
 * @MigrateProcessPlugin(
 *   id = "get_classifications_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_classifications_array
 *   source: text
 * @endcode
 */
class GetClassificationsArray extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $vid = 'product_classifications';
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vid);
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $values_array = [];
    if (!empty($value)) {
      foreach ($value->children() as $child) {
        if (!empty($child->attributes()->Type) && $child->attributes()->Type == 'Web Reference') {
          foreach ($terms as $term) {
            if (isset($term->get('field_classification_id')->value)) {
              $classification_id = $term->get('field_classification_id')->value;
              if ($classification_id == $child->attributes()->ClassificationID) {
                $values_array[] = [
                  'vid' => $vid,
                  'target_id' => $term->id()
                ];
              }
            }
          }
        }
      }
      $values_array = json_encode($values_array);
    }

    return json_decode($values_array, TRUE);
  }

}
