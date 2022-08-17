<?php

namespace Drupal\crescenttool_migrations\Plugin\migrate\process;

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
