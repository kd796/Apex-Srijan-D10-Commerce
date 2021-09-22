<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Get Classification Parent Name.
 *
 * @MigrateProcessPlugin(
 *   id = "get_classification_parent_term"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_classification_parent_term
 *   source: text
 * @endcode
 */
class GetClassificationParentTerm extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $vocabulary = 'product_classifications';
    $name = NULL;
    $id = NULL;
    $id_to_check = NULL;
    $properties = [];
    if (empty($value)) {
      throw new MigrateSkipProcessException();
    }
    if (!empty($value->Name) && !empty($value->attributes()->ID)) {
      $name = $value->Name;
      $id = $value->attributes()->ID;

      if (!empty($name)) {
        $properties['name'] = $name;
      }
      if (!empty($vocabulary)) {
        $properties['vid'] = $vocabulary;
      }
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      foreach ($terms as $term) {
        if ($term->hasField('field_classification_id')) {
          $id_to_check = $term->get('field_classification_id')->value;
        }

        if ($id_to_check == $id) {
          return $term->id();
        }
      }
    }
    else {
      return 0;
    }
  }

}
