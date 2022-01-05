<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

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

    if (isset($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

}
