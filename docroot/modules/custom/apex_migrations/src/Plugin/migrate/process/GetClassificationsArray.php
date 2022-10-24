<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\taxonomy\Entity\Term;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Classifications Array.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_classifications_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_classifications_array
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
      $values_array = $this->findCategories($value, $terms, $vid);

      if (empty($values_array)) {
        $parentProduct = $value->xpath('parent::Product');

        if (!empty($parentProduct[0])) {
          $values_array = $this->findCategories($parentProduct[0], $terms, $vid);
        }
        else {
          throw new MigrateSkipProcessException();
        }
      }

      $values_array = json_encode($values_array);
    }

    return json_decode($values_array, TRUE);
  }

  /**
   * Find Categories.
   *
   * @param \SimpleXMLElement|mixed $value
   *   The SimpleXmlElement to check.
   * @param array $terms
   *   Array of Term Entities.
   * @param string $vid
   *   The vocabulary ID.
   *
   * @return array
   *   An array of terms.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function findCategories(mixed $value, array $terms, string $vid): array {
    $values_array = [];

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

              // Does Item have Parent?
              $parent_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term->id());

              if (!empty(reset($parent_term))) {
                $parent = reset($parent_term);
                $values_array[] = [
                  'vid' => $vid,
                  'target_id' => $parent->id()
                ];

                // Does Item have Grandparent?
                $grandparent_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($parent->id());

                if (!empty(reset($grandparent_term))) {
                  $grandparent = reset($grandparent_term);
                  $values_array[] = [
                    'vid' => $vid,
                    'target_id' => $grandparent->id()
                  ];
                }
              }
            }
          }
        }
      }
    }

    return $values_array;
  }

}
