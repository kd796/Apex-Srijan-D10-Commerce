<?php

namespace Drupal\crescenttool_au_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get the facets for the 3rd tier categories.
 *
 * Since there are no custom facets for 3rd tier, we fallback to the 1st.
 *
 * @MigrateProcessPlugin(
 *   id = "get_all_level_3_category_facets"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_all_level_3_category_facets
 *   source: text
 * @endcode
 */
class GetAllLevel3CategoryFacets extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $product_specifications = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree(
      'product_specifications',
      0,
      1,
      TRUE
    );

    $all_terms_array = [];
    $value = (string) $value;
    $facets = $this->mapCategoryToCustomFacets($value);

    if (empty($facets)) {
      $level_two_parent = $this->getParentTermFromClassificationId($value);

      if (!empty($level_two_parent)) {
        $level_two_parent_source_id = $this->getClassificationId($level_two_parent);

        if (!empty($level_two_parent_source_id)) {
          $top_level_parent = $this->getParentTermFromClassificationId($level_two_parent_source_id);

          if (!empty($top_level_parent)) {
            $top_level_parent_source_id = $this->getClassificationId($top_level_parent);

            if (!empty($level_two_parent_source_id)) {
              // Load the facets for the parent instead.
              $facets = $this->mapCategoryToFacetsList($top_level_parent_source_id);

              if (!empty($facets) && !empty($product_specifications)) {
                foreach ($product_specifications as $spec) {
                  $source_id = explode(' | ', $spec->label())[0];

                  if (in_array($source_id, $facets)) {
                    $all_terms_array[] = [
                      'vid' => 'product_specifications',
                      'target_id' => $spec->id(),
                    ];
                  }
                }
              }
            }
          }
        }
      }
    }

    $all_terms_array = json_encode($all_terms_array);

    return json_decode($all_terms_array, TRUE);
  }

  /**
   * Gets the parent term from a classification ID.
   */
  protected function getParentTermFromClassificationId($classificationID) {
    // Get the parent term ID of the current term.
    $product_categories = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'product_classifications',
      'field_classification_id' => $classificationID,
    ]);

    if (!empty($product_categories)) {
      /** @var \Drupal\taxonomy\Entity\Term $current_term */
      $current_term = array_pop($product_categories);

      if (!is_object($current_term)) {
        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($current_term->id());

        /** @var \Drupal\taxonomy\Entity\Term $parent */
        return reset($parent);
      }
    }

    return NULL;
  }

  /**
   * Get the classification ID from the parent of the passed in term.
   */
  protected function getClassificationId($term) {
    if (is_object($term)) {
      $term_arr = $term->toArray();

      if (!empty($term_arr['field_classification_id'][0]['value'])) {
        return $term_arr['field_classification_id'][0]['value'];
      }
    }

    return NULL;
  }

  /**
   * Maps only the 2nd level categories that differ from their parents.
   */
  protected function mapCategoryToCustomFacets($category_remote_id) {
    $mapping = [
      'W3_792548' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
      'W3_792549' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
      'W3_792550' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
      'W3_792551' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
      'W3_792552' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
      'W3_803453' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
    ];

    if (!empty($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

  /**
   * Check for list of facets.
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = [
      // Cutting - W1_719495.
      'W1_719495' => [
        'ATT496',
        'ATT802893',
        'ATT340',
        'ATT769436',
        'ATT278',
        'ATT686141',
      ],
      // Demolition Tools - W1_22487.
      'W1_22487' => [
        'ATT496',
        'ATT802893',
        'ATT584',
        'ATT582',
        'ATT583',
      ],
      // Hex Keys - W1_706367.
      'W1_706367' => [
        'ATT496',
        'ATT802893',
        'ATT493',
        'ATT660',
        'ATT592',
        'ATT659'
      ],
      // Measuring - W1_719524.
      'W1_719524' => [
        'ATT802893',
        'ATT807193',
        'ATT127',
        'ATT130',
        'ATT592',
        'ATT593',
      ],
      // Pliers - W1_22486.
      'W1_22486' => [
        'ATT802893',
        'ATT496',
        'ATT259',
        'ATT226',
        'ATT880',
        'ATT451',
        'ATT115',
        'ATT714720'
      ],
      // Power Tool Accessories - W1_755886.
      'W1_755886' => [
        'ATT802893',
        'ATT496',
        'ATT804086',
        'ATT755881',
        'ATT592'
      ],
      // Ratchets and Drive Tools - W1_22482.
      'W1_22482' => [
        'ATT496',
        'ATT802893',
        'ATT804086',
        'ATT585',
        'ATT589',
        'ATT631',
        'ATT491',
        'ATT586',
        'ATT749756',
        'ATT714694',
        'ATT593',
        'ATT710'
      ],
      // Screwdrivers and Nutdrivers - W1_22485.
      'W1_22485' => [
        'ATT496',
        'ATT802893',
        'ATT415',
        'ATT631',
        'ATT806593'
      ],
      // Shaping - W1_719537.
      'W1_719537' => [
        'ATT496',
        'ATT802893',
        'ATT934',
        'ATT201',
        'ATT547'
      ],
      // Sockets - W1_22481.
      'W1_22481' => [
        'ATT496',
        'ATT802893',
        'ATT499',
        'ATT493',
        'ATT491',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Storage - W1_736078.
      'W1_736078' => [
        'ATT802893',
      ],
      // Striking and Struck - W1_706780.
      'W1_706780' => [
        'ATT496',
        'ATT802893',
        'ATT807126',
        'ATT228',
        'ATT227',
        'ATT345'
      ],
      // Tool Sets - W1_22484.
      'W1_22484' => [
        'ATT496',
        'ATT802893',
      ],
      // Trade Tools - W1_802905.
      'W1_802905' => [
        'ATT806600',
        'ATT802893',
      ],
      // Wrenches - W1_22483.
      'W1_22483' => [
        'ATT496',
        'ATT802893',
        'ATT499',
        'ATT491',
        'ATT585',
        'ATT714694',
        'ATT205',
        'ATT739685',
        'ATT739684'
      ],
    ];

    if (!empty($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

}
