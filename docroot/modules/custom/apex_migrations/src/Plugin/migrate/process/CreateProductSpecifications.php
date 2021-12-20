<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_create_product_specifications"
 * )
 */
class CreateProductSpecifications extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['allowed_attributes']) && !array_key_exists('allowed_attributes', $this->configuration)) {
      throw new MigrateException('Skip on value plugin is missing the allowed attributes configuration.');
    }

    $values_array = [];
    $vid = 'product_specifications';
    $parent_id = NULL;
    $parent_term_id = NULL;

    $product_specifications = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'product_specifications'
    ]);

    if (!empty($value)) {
      foreach ($value->children() as $child) {
        $parent_label = NULL;
        $parent_term_id = NULL;
        $parent_id = (string) $child->attributes()->AttributeID;
        $validAttribute = $this->validateAttributeName($parent_id);

        if ($validAttribute) {
          foreach ($product_specifications as $product_specification) {
            if (str_contains($product_specification->label(), $parent_id)) {
              $parent_label = str_replace($parent_id . ' | ', '', $product_specification->label());
              $parent_term_id = $product_specification->id();
              break;
            }
          }

          if (!empty($parent_term_id) && !empty($parent_label)) {
            $term = NULL;

            if ($child->getName() === 'MultiValue') {
              if (count($child->children()) > 1) {
                foreach ($child->children() as $item) {
                  $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $item);
                }
              }
              else {
                $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child->Value);
              }
            }
            else {
              $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child);
            }

            if (is_object($term)) {
              $values_array[] = [
                'vid' => $vid,
                'target_id' => $term->id()
              ];
            }
          }
        }
      }

      $values_array = json_encode($values_array);
    }

    return json_decode($values_array, TRUE);
  }

  /**
   * Creates a term with a parent. Then returns the loaded or created term.
   *
   * @param string $parent_label
   *   The name/label of the parent term.
   * @param int $parent_term_id
   *   The term ID of the parent term.
   * @param string $item_label
   *   The name/label of the item you are loading/creating.
   * @param string $vid
   *   The vocabulary ID you are adding this term to.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term object or NULL.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function loadOrCreateChildTerm($parent_label, $parent_term_id, $item_label, $vid = 'product_specifications') {
    $term_name = $parent_label . ' : ' . (string) $item_label;

    if ($tid = $this->getTidByName($term_name, $vid)) {
      $term = Term::load($tid);
    }
    else {
      $term = Term::create([
        'name' => $term_name,
        'vid' => $vid,
      ]);
    }

    $term->set('parent', $parent_term_id);
    $term->save();

    if ($tid = $this->getTidByName($term_name, $vid)) {
      $term = Term::load($tid);
    }

    return $term;
  }

  /**
   * Load term by name.
   */
  protected function getTidByName($name = NULL, $vocabulary = NULL): int {
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

  /**
   * Check for valid term.
   */
  protected function validateAttributeName($attribute = NULL) {
    $attributes_to_include = $this->configuration['allowed_attributes'];

    if (in_array($attribute, $attributes_to_include)) {
      return TRUE;
    }

    return FALSE;
  }

}
