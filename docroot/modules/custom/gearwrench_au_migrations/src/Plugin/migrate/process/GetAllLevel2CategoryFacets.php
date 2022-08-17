<?php

namespace Drupal\gearwrench_au_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "get_all_level_2_category_facets"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_all_level_2_category_facets
 *   source: text
 * @endcode
 */
class GetAllLevel2CategoryFacets extends ProcessPluginBase {

  /**
   * The mapping array.
   *
   * @var array|\string[][]
   */
  public static array $mapping = [
    'W2_743832' => [
      'ATT496',
      'ATT802893',
      'ATT484',
      'ATT499',
      'ATT804086',
      'ATT491',
      'ATT744972',
      'ATT744973',
      'ATT806802'
    ],
    'W2_743838' => [
      'ATT496',
      'ATT802893',
      'ATT484',
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
    'W2_743906;' => ['ATT496', 'ATT802893'],
    'W2_743907' => ['ATT496', 'ATT802893'],
    'W2_743908' => ['ATT496', 'ATT802893'],
    'W2_743954' => [
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
      'ATT744973'
    ],
    'W2_743955' => [
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
      'ATT744973'
    ],
    'W2_774131' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743820' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743821' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743822' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743823' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743824' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743826' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743825' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_744933' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973'
    ],
    'W2_743912' => [
      'ATT496',
      'ATT802893',
      'ATT807126',
      'ATT228',
      'ATT227',
      'ATT345'
    ],
    'W2_743913' => [
      'ATT496',
      'ATT802893',
      'ATT807127',
      'ATT345'
    ],
    'W2_743853' => [
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

    $value = (string) $value;
    $facets = $this->mapCategoryToCustomFacets($value);

    if (empty($facets)) {
      // Get the parent term ID of the current term.
      $product_categories = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
        'vid' => 'product_classifications',
        'field_classification_id' => $value,
      ]);

      /** @var \Drupal\taxonomy\Entity\Term $current_term */
      $current_term = array_pop($product_categories);

      if (!empty($current_term) && is_object($current_term)) {
        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($current_term->id());

        /** @var \Drupal\taxonomy\Entity\Term $parent */
        $parent = reset($parent);

        if (is_object($parent)) {
          $parent_arr = $parent->toArray();

          if (!empty($parent_arr['field_classification_id'][0]['value'])) {
            $parent_source_id = $parent_arr['field_classification_id'][0]['value'];

            // Load the facets for the parent instead.
            $facets = $this->mapCategoryToFacetsList($parent_source_id);
          }
        }
      }
    }

    $all_terms_array = [];

    if (!empty($facets) && !empty($product_specifications)) {
      foreach ($product_specifications as $spec) {
        if (is_object($spec) && !empty($spec->label())) {
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

    $all_terms_array = json_encode($all_terms_array);

    return json_decode($all_terms_array, TRUE);
  }

  /**
   * Maps only the 2nd level categories that differ from their parents.
   */
  protected function mapCategoryToCustomFacets($category_remote_id) {
    $mapping = self::$mapping;

    if (!empty($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

  /**
   * Check for list of facets.
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = GetAllCategoryFacets::$mapping;

    if (!empty($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

}
