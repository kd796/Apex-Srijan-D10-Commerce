<?php

namespace Drupal\cleco_vuejs\Utils;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;

/**
 * ProductsParentChildMapping trait for Apex Tools.
 */
trait ProductsParentChildMapping {

  /**
   * Protected variable to store list of products.
   *
   * @var array
   */
  protected $listProductHierarchy;

  /**
   * Vocabulary machine name.
   *
   * @var array
   */
  private $vocabularyMachineName;

  /**
   * Initialize trait.
   */
  protected function initialize() {
    $this->vocabularyMachineName = 'product_classifications';
    $this->listProductHierarchy = $this->getProductHierarchy();
  }

  /**
   * Product hierarchy array keyed by main parent's term id.
   */
  public function getProductHierarchy() {
    $data = [];
    // Load the taxonomy vocabulary by its machine name.
    $vocabulary = Vocabulary::load($this->vocabularyMachineName);

    // Check if the vocabulary exists.
    if ($vocabulary) {
      $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
        ->loadTree($vocabulary->id(), 0, NULL, TRUE);
      foreach ($tree as $term) {
        $parentField = $term->get('parent');
        $parent = NULL;
        if (!$parentField->isEmpty()) {
          // Get the parent term entity.
          $parentTerm = $parentField->entity;
          if ($parentTerm instanceof Term) {
            $parent = [
              'id' => $parentTerm->id(),
              'name' => $parentTerm->getName(),
            ];
            // For nested hierarchy, traverse towards top level parent term.
            $topLevelParent = $this->getTopLevelParentTerm($term);
            if ($topLevelParent && $topLevelParent['id'] == $term->id()) {
              $topLevelParent = NULL;
            }
            $data[$topLevelParent['id']][] = [
              'name' => $term->getName(),
              'id' => $term->id(),
              'top_parent' => $topLevelParent,
              'parent' => $parent,
            ];
          }
        }
        else {
          $data[$term->id()] = [
            'name' => $term->getName(),
            'id' => $term->id(),
          ];
        }
      }

      return $data;
    }
  }

  /**
   * List of children based on parent.
   */
  public function getChildren(array $parent, $currentTerms = []) {
    $children = [];
    if (is_null($this->listProductHierarchy)) {
      $this->initialize();
    }
    // In case we have multiple parents.
    foreach ($parent as $pname) {
      // Load term name and id by name.
      $parent_term = $this->loadTermByName($pname);
      if ($parent_term &&
        isset($this->listProductHierarchy[$parent_term['id']])) {
          if ($currentTerms && count($currentTerms) > 1) {
            foreach ($this->listProductHierarchy[$parent_term['id']] as $childTerm) {
              if ($childTerm['name'] == $currentTerms[key($currentTerms)]) {
                return array_column($this->listProductHierarchy[$parent_term['id']], "name");
              }
            }
            return [];
          }

        return array_column($this->listProductHierarchy[$parent_term['id']], "name");
      }
    }

    return $children;
  }

  /**
   * Load term by name.
   */
  protected function loadTermByName($term_name, $parent_term = '') {
    $data = [];
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    // Create an entity query to find the term.
    $query = $term_storage->getQuery()
      ->condition('name', $term_name)
      ->condition('vid', $this->vocabularyMachineName);
    if ($parent_term) {
      $query->condition('parent', $parent_term);
    }
    $tids = $query->accessCheck(FALSE)->execute();
    if ($tids) {
      // Retrieve the first matching term ID.
      $tid = reset($tids);
      $term = Term::load($tid);
      $data = [
        'name' => $term->getName(),
        'id' => $term->id(),
      ];
    }

    return $data;
  }

  /**
   * Recursively retrieve the top-level parent term.
   *
   * @param \Drupal\taxonomy\Entity\Term $term
   *   The current term.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   The top-level parent term or NULL if not found.
   */
  public function getTopLevelParentTerm(Term $term) {
    $parentField = $term->get('parent');

    if (!$parentField->isEmpty()) {
      $parentTerm = $parentField->entity;

      // Check if the current term has a parent.
      if ($parentTerm instanceof Term) {
        // Recursively get the top-level parent term.
        return $this->getTopLevelParentTerm($parentTerm);
      }
    }

    // If no parent is found, return the current term as the top-level parent.
    return [
      'id' => $term->id(),
      'name' => $term->getName(),
    ];
  }

}
