<?php

namespace Drupal\sata_brazil_migrations\Plugin\migrate\process;

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
      // Auto Specialty - W1_743930.
      'W1_743930' => [
        'ATT496',
        'ATT802893',
      ],
      // Cutting Tools - W1_743899.
      'W1_743899' => [
        'ATT496',
        'ATT802893',
      ],
      // Impact Products - W1_743831.
      'W1_743831' => [
        'ATT496',
        'ATT802893',
        'ATT484',
        'ATT499',
        'ATT804086',
        'ATT491',
        'ATT744972',
        'ATT744973'
      ],
      // Lighting - W1_743769.
      'W1_743769' => [
        'ATT802893',
        'ATT714716',
        'ATT714694',
        'ATT592'
      ],
      // Master Sets - W1_743784.
      'W1_743784' => [
        'ATT496',
        'ATT802893',
      ],
      // Measuring & Inspection - W1_743905.
      'W1_743905' => [
        'ATT496',
      ],
      // Pliers - W1_743878.
      'W1_743878' => [
        'ATT802893',
        'ATT496',
        'ATT259',
        'ATT226',
        'ATT880',
        'ATT451',
        'ATT115',
        'ATT714720'
      ],
      // Pry Bars - W1_743917.
      'W1_743917' => [
        'ATT496',
        'ATT802893',
        'ATT584',
        'ATT582',
        'ATT583'
      ],
      // Pullers - W1_743927.
      'W1_743927' => [
        'ATT496',
      ],
      // Ratchets and Socket Sets - W1_743953.
      'W1_743953' => [
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
        'ATT710',
        'ATT499',
        'ATT493',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Ratchets and Drive Tools - W1_743803.
      'W1_743803' => [
        'ATT496',
        'ATT802893',
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
      // Screwdrivers and Nutdrivers - W1_743867.
      'W1_743867' => [
        'ATT496',
        'ATT802893',
        'ATT415',
        'ATT631'
      ],
      // Shop Assist Equipment - W1_743771.
      'W1_743771' => [
        'ATT802893'
      ],
      // Sockets and Sets - W1_743819.
      'W1_743819' => [
        'ATT496',
        'ATT802893',
        'ATT499',
        'ATT493',
        'ATT491',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Striking and Struck - W1_743911.
      'W1_743911' => [
        'ATT496',
        'ATT802893',
        'ATT807126',
        'ATT807127',
        'ATT228',
        'ATT227',
        'ATT345'
      ],
      // Tool Sets - W1_743772.
      'W1_743772' => [
        'ATT496',
        'ATT802893',
      ],
      // Tool Storage - W1_743786.
      'W1_743786' => [
        'ATT802893',
        'ATT753947'
      ],
      // Torque Products - W1_743860.
      'W1_743860' => [
        'ATT806600',
        'ATT802893',
        'ATT484',
        'ATT585',
        'ATT714694',
        'ATT753929'
      ],
      // Wrenches - W1_743846.
      'W1_743846' => [
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
