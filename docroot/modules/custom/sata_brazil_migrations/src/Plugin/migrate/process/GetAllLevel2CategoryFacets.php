<?php

namespace Drupal\sata_brazil_migrations\Plugin\migrate\process;

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
    'W2_777680' => ['ATT584933', 'ATT779365'],
    'W2_777684' => ['ATT584933'],
    'W2_777685' => ['ATT584933'],
    'W2_777663' => [
      'ATT420',
      'ATT493',
      'ATT497',
      'ATT835',
      'ATT948',
      'ATT779365',
    ],
    'W2_777664' => ['ATT948', 'ATT779365'],
    'W2_777665' => [
      'ATT779365',
      'ATT425',
      'ATT755881',
      'ATT948',
    ],
    'W2_777666' => [
      'ATT779365',
      'ATT948',
      'ATT425',
    ],
    'W2_777667' => [
      'ATT779365',
      'ATT687593',
      'ATT948',
      'ATT345',
    ],
    'W2_777668' => [
      'ATT687593',
      'ATT948',
      'ATT425',
      'ATT176',
      'ATT185',
    ],
    'W2_777669' => [
      'ATT779365',
      'ATT948',
      'ATT493',
      'ATT835'
    ],
    'W2_777670' => [
      'ATT779365',
      'ATT425',
      'ATT493',
      'ATT835',
      'ATT948',
    ],
    'W2_777671' => [
      'ATT779365',
      'ATT948',
      'ATT425'
    ],
    'W2_777672' => ['ATT425'],
    'W2_777673' => [
      'ATT779365',
      'ATT948',
      'ATT263'
    ],
    'W2_777674' => [
      'ATT779365',
      'ATT493',
      'ATT948'
    ],
    'W2_777675' => [
      'ATT779365',
      'ATT493',
      'ATT584466',
      'ATT744972',
      'ATT948',
    ],
    'W2_777676' => [
      'ATT779365',
      'ATT493',
      'ATT497',
      'ATT948'
    ],
    'W2_777677' => [
      'ATT779365',
      'ATT493',
      'ATT948'
    ],
    'W2_777678' => [
      'ATT779365',
      'ATT493',
      'ATT584466',
      'ATT744972',
      'ATT948',
    ],
    'W2_777679' => [
      'ATT779365',
      'ATT948',
      'ATT425'
    ],
    'W2_817553' => ['ATT948', 'ATT425'],
    'W2_853012' => ['ATT948', 'ATT425'],
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
