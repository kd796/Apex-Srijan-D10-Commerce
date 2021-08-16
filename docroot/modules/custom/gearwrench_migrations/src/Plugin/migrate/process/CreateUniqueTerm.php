<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "create_unique_term"
 * )
 */
class CreateUniqueTerm extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $values_array = [];
    foreach ($value->children() as $child) {
      $term = NULL;
      if ($child->getName() !== 'MultiValue') {
        $vid = strtolower((string) $child->attributes()->AttributeID);
        $vid = str_replace(' ', '_', $vid);
        // Check if vocab exists.
        $vocab = Vocabulary::load($vid);
        if (!empty($vocab)) {
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
      }
      else {
        $vid = strtolower((string) $child->attributes()->AttributeID);
        $vid = str_replace(' ', '_', $vid);
        // Check if vocab exists.
        $vocab = Vocabulary::load($vid);
        if (!empty($vocab)) {
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
      if (is_object($term)) {
        $values_array[] = [
          'vid' => $vid,
          'target_id' => $term->id()
        ];
      }
    }
    $values_array = json_encode($values_array);
    return json_decode($values_array, TRUE);
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
