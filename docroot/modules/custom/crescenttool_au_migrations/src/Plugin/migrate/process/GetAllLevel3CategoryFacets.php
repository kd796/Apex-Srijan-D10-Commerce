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
      'W3_743707' => ['ATT802893', 'ATT778642', 'ATT763883'],
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
      // Bolt Cutters - W1_729125.
      'W1_729125' => [
        'ATT496',
        'ATT802893',
        'ATT278',
      ],
      // Cable Ties - W1_729889.
      'W1_729889' => [
        'ATT496',
        'ATT802893',
      ],
      // Clamps - W1_729121.
      'W1_729121' => [
        'ATT496',
        'ATT802893',
      ],
      // Cutting - W1_729126.
      'W1_729126' => [
        'ATT496',
        'ATT802893',
        'ATT340',
        'ATT769436',
        'ATT278',
        'ATT686141',
      ],
      // Demolition Tools - W1_729120.
      'W1_729120' => [
        'ATT496',
        'ATT802893',
        'ATT582',
        'ATT583',
      ],
      // Hammers - W1_729124.
      'W1_729124' => [
        'ATT496',
        'ATT802893',
        'ATT228',
        'ATT227',
        'ATT345',
      ],
      // Hand Files - W1_729128.
      'W1_729128' => [
        'ATT496',
        'ATT802893',
        'ATT493',
        'ATT201',
        'ATT547',
      ],
      // Hex Keys - W1_729123.
      'W1_729123' => [
        'ATT496',
        'ATT802893',
        'ATT493',
        'ATT660',
        'ATT592',
        'ATT659'
      ],
      // Laser Product - W1_729890.
      'W1_729890' => [
        'ATT496',
        'ATT802893',
        'ATT584827',
        'ATT592',
      ],
      // Leather Aprons & Belts - W1_734127.
      'W1_734127' => [
        'ATT496',
        'ATT802893',
      ],
      // Measuring and Marking - W1_729127.
      'W1_729127' => [
        'ATT802893',
        'ATT807193',
        'ATT127',
        'ATT130',
        'ATT592',
        'ATT593',
      ],
      // Mechanics Tools - W1_729117.
      'W1_729117' => [
        'ATT496',
        'ATT802893',
      ],
      // Miscellaneous Tools - W1_729885.
      'W1_729885' => [
        'ATT496',
        'ATT802893',
      ],
      // Pliers - W1_729119.
      'W1_729119' => [
        'ATT802893',
        'ATT496',
        'ATT259',
        'ATT226',
        'ATT880',
        'ATT451',
        'ATT115',
        'ATT714720'
      ],
      // Ratchets and Drive Tools - W1_729115.
      'W1_729115' => [
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
      // Screwdrivers and Nutdrivers - W1_729118.
      'W1_729118' => [
        'ATT496',
        'ATT802893',
        'ATT415',
        'ATT631',
        'ATT806593'
      ],
      // Sockets and Sets - W1_729114.
      'W1_729114' => [
        'ATT496',
        'ATT802893',
        'ATT484',
        'ATT499',
        'ATT493',
        'ATT491',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Storage and Tool Holders - W1_729887.
      'W1_729887' => [
        'ATT802893',
        'ATT345',
        'ATT205',
        'ATT159',
      ],
      // Strippers and Crimpers - W1_729884.
      'W1_729884' => [
        'ATT496',
        'ATT802893',
      ],
      // Torque Wrenches - W1_729122.
      'W1_729122' => [
        'ATT806600',
        'ATT802893',
        'ATT484',
        'ATT585',
        'ATT714694',
        'ATT753929',
      ],
      // Wrenches - W1_729116.
      'W1_729116' => [
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
