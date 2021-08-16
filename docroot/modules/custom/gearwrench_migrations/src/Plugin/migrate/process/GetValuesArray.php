<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Get Values Array.
 *
 * @MigrateProcessPlugin(
 *   id = "get_values_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_values_array
 *   source: text
 * @endcode
 */
class GetValuesArray extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    foreach ($value->children() as $child) {
      if ($child->getName() !== 'MultiValue') {
        $vid = strtolower((string) $child->attributes()->AttributeID);
        $vid = str_replace(' ', '_', $vid);
        if ($tid = $this->getTidByName((string) $child, $vid)) {
          $term = Term::load($tid);
        }
        else {
          $term = Term::create([
            'name' => (string) $child,
            'vid'  => $vid,
          ])->save();
          if ($tid = $this->getTidByName((string) $child, $vid)) {
            $term = Term::load($tid);
          }
        }
      }
      else {
        $vid = strtolower((string) $child->attributes()->AttributeID);
        $vid = str_replace(' ', '_', $vid);

        if ($tid = $this->getTidByName((string) $child->Value, $vid)) {
          $term = Term::load($tid);
        }
        else {
          $term = Term::create([
            'name' => (string) $child->Value,
            'vid'  => $vid,
          ])->save();

          if ($tid = $this->getTidByName((string) $child->Value, $vid)) {
            $term = Term::load($tid);
          }
        }
      }
    }
    return [
      'target_id' => is_object($term) ? $term->id() : 0,
    ];
  }

  /**
   * Load term by name.
   */
  protected function getTidByName($name = NULL, $vocabulary = NULL) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vocabulary)) {
      $properties['vid'] = $vocabulary;
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    return !empty($term) ? $term->id() : 0;
  }

}
