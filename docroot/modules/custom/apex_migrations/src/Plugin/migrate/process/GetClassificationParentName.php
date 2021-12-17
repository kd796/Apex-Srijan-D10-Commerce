<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Classification Parent Name.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_classification_parent_name"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_classification_parent_name
 *   source: text
 * @endcode
 */
class GetClassificationParentName extends ProcessPluginBase {

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
