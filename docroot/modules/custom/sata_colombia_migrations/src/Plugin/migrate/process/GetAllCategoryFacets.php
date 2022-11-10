<?php

namespace Drupal\sata_colombia_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "get_all_category_facets"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_all_category_facets
 *   source: text
 * @endcode
 */
class GetAllCategoryFacets extends ProcessPluginBase {

  /**
   * The mapping array.
   *
   * @var array|\string[][]
   */
  public static array $mapping = [
    'W1_775296' => ['ATT493', 'ATT584466', 'ATT744972', 'ATT779365'],
    'W1_775286' => ['ATT779365', 'ATT948', 'ATT755881', 'ATT425', 'ATT867587'],
    'W1_775299' => ['ATT779365', 'ATT687593', 'ATT948', 'ATT345'],
    'W1_775297' => ['ATT779365', 'ATT493', 'ATT584466', 'ATT744972'],
    'W1_775300' => ['ATT779365', 'ATT948', 'ATT493'],
    'W1_784246' => ['ATT779365', 'ATT948', 'ATT425'],
    'W1_775303' => ['ATT779365', 'ATT948', 'ATT425', 'ATT176', 'ATT185'],
    'W1_775304' => ['ATT779365', 'ATT948', 'ATT425'],
    'W1_775290' => ['ATT779365', 'ATT948', 'ATT493'],
    'W1_775305' => ['ATT779365', 'ATT948', 'ATT425'],
    'W1_775306' => ['ATT779365', 'ATT948', 'ATT425'],
    'W1_775307' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W1_775308' => ['ATT497', 'ATT948', 'ATT420'],
    'W1_775309' => ['ATT497', 'ATT948', 'ATT493', 'ATT420'],
    'W1_775310' => ['ATT497', 'ATT948', 'ATT493', 'ATT420', 'ATT835'],
    'W1_775301' => ['ATT497', 'ATT948', 'ATT493', 'ATT420'],
    'W1_775302' => ['ATT779365', 'ATT948', 'ATT493'],
    'W1_775311' => ['ATT779365', 'ATT948', 'ATT493'],
    'W1_775298' => [
      'ATT779365',
      'ATT948',
      'ATT493',
      'ATT497',
      'ATT420',
      'ATT425'
    ],
    'W1_775312' => ['ATT779365', 'ATT948', 'ATT493', 'ATT835'],
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

    $facets = $this->mapCategoryToFacetsList($value);
    $all_terms_array = [];

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

    $all_terms_array = json_encode($all_terms_array);

    return json_decode($all_terms_array, TRUE);
  }

  /**
   * Check for list of facets.
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = self::$mapping;

    if (isset($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

}
