<?php

namespace Drupal\gearwrench_au_migrations\Plugin\migrate\process;

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
   * Check for list of facets.
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = [
      // Auto Specialty - W1_15788.
      'W1_15788' => [
        'ATT496',
        'ATT802893',
      ],
      // Cutting Tools - W1_15789.
      'W1_15789' => [
        'ATT496',
        'ATT802893',
      ],
      // Extraction Tools - W1_785249.
      'W1_785249' => [
        'ATT496',
        'ATT493',
        'ATT484',
        'ATT491'
      ],
      // Hex Keys - W1_802014.
      'W1_802014' => [
        'ATT496',
        'ATT802893',
        'ATT493',
        'ATT660',
        'ATT592',
        'ATT659'
      ],
      // Impact Products - W1_15792.
      'W1_15792' => [
        'ATT496',
        'ATT802893',
        'ATT499',
        'ATT804086',
        'ATT491',
        'ATT744972',
        'ATT744973'
      ],
      // Lighting - W1_727497.
      'W1_727497' => [
        'ATT802893',
        'ATT714716',
        'ATT714694',
        'ATT592'
      ],
      // Pass Thru™ Tools - W1_806799.
      'W1_806799' => [
        'ATT496',
        'ATT802893',
        'ATT804086',
        'ATT484',
        'ATT499',
        'ATT493',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Pliers - W1_15797.
      'W1_15797' => [
        'ATT802893',
        'ATT496',
        'ATT259',
        'ATT226',
        'ATT880',
        'ATT451',
        'ATT115',
        'ATT714720'
      ],
      // Pry Bars - W1_15798.
      'W1_15798' => [
        'ATT496',
        'ATT802893',
        'ATT584',
        'ATT582',
        'ATT583'
      ],
      // Ratchets and Drive Tools - W1_15793.
      'W1_15793' => [
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
      // Screwdrivers and Nutdrivers - W1_15795.
      'W1_15795' => [
        'ATT496',
        'ATT802893',
        'ATT415',
        'ATT631'
      ],
      // Shop Assist Equipment - W1_728251.
      'W1_728251' => [
        'ATT802893'
      ],
      // Sockets - W1_16113.
      'W1_16113' => [
        'ATT496',
        'ATT802893',
        'ATT499',
        'ATT493',
        'ATT491',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Striking and Struck - W1_15799.
      'W1_15799' => [
        'ATT496',
        'ATT802893',
        'ATT807126',
        'ATT807127',
        'ATT228',
        'ATT227',
        'ATT345'
      ],
      // Tethered Products - W1_781017.
      'W1_781017' => [
        'ATT496',
        'ATT802893',
        'ATT804086',
        'ATT783458'
      ],
      // Tool Sets - W1_736539.
      'W1_736539' => [
        'ATT496',
        'ATT802893',
      ],
      // Tool Storage - W1_15791.
      'W1_15791' => [
        'ATT802893',
        'ATT753947'
      ],
      // Torque Products - W1_15794.
      'W1_15794' => [
        'ATT806600',
        'ATT802893',
        'ATT484',
        'ATT585',
        'ATT714694',
        'ATT753929'
      ],
      // Wrenches - W1_15796.
      'W1_15796' => [
        'ATT496',
        'ATT802893',
        'ATT499',
        'ATT491',
        'ATT585',
        'ATT749756',
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
