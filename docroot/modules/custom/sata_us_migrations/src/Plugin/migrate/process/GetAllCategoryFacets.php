<?php

namespace Drupal\sata_us_migrations\Plugin\migrate\process;

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
   *
   * @param string $category_remote_id
   *   The category ID.
   *
   * @return array|string[]
   *   The resulting array (empty or not).
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = self::getLevel1AttributeMapping();

    if (isset($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

  /**
   * Returns the array of attribute mapping for level 2 categories.
   *
   * @return array
   *   The mapping array.
   */
  public static function getLevel1AttributeMapping(): array {
    return [
      // Auto Specialty - W1_846447.
      'W1_846447' => [
        'ATT497',
        'ATT495',
        'ATT494',
        'ATT948',
        'ATT781',
        'ATT254',
        'ATT176',
        'ATT450',
      ],
      // Clamping - W1_846444.
      'W1_846444' => [
        'ATT948',
        'ATT584880',
        'ATT584885',
        'ATT326',
        'ATT584797',
      ],
      // Cutting and Filing - W1_846459.
      'W1_846459' => [
        'ATT948',
        'ATT769436',
        'ATT176',
        'ATT781',
        'ATT128',
      ],
      // Hex Keys - W1_846450.
      'W1_846450' => [
        'ATT659',
        'ATT660',
        'ATT493',
        'ATT491',
      ],
      // Impact Products - W1_846451.
      'W1_846451' => [
        'ATT491',
        'ATT804086',
        'ATT493',
      ],
      // Insulated Tools - W1_846452.
      'W1_846452' => [
        'ATT414',
        'ATT948',
      ],
      // Measuring and Layout - W1_846458.
      'W1_846458' => [
        'ATT948',
        'ATT128',
        'ATT133',
        'ATT130',
      ],
      // Personal Protective Equipment - W1_846456.
      'W1_846456' => [
        'ATT345',
        'ATT948',
        'ATT425',
        'ATT867467',
        'ATT670298',
        'ATT867471',
      ],
      // Pliers - W1_846454.
      'W1_846454' => [
        'ATT259',
        'ATT948',
        'ATT497',
      ],
      // Power Tools - W1_846455.
      'W1_846455' => [
        'ATT22507',
        'ATT662382',
        'ATT728214',
        'ATT867472',
        'ATT837657',
        'ATT662382',
      ],
      // Ratchets & Drive Tools - W1_846457.
      'W1_846457' => [
        'ATT585',
        'ATT484',
      ],
      // Screwdrivers & Bitdrivers - W1_846460.
      'W1_846460' => [
        'ATT415',
        'ATT631',
        'ATT948',
        'ATT497',
      ],
      // Shop Equipment - W1_846461.
      'W1_846461' => [
        'ATT867473',
        'ATT584933',
        'ATT673955',
        'ATT867475',
      ],
      // Striking and Struck - W1_846463.
      'W1_846463' => [
        'ATT345',
        'ATT236',
        'ATT728177',
        'ATT563',
      ],
      // Tool and Socket Sets - W1_846464.
      'W1_846464' => [
        'ATT497',
        'ATT484',
        'ATT493',
        'ATT496',
      ],
      // Tool Storage - W1_846465.
      'W1_846465' => [
        'ATT345',
        'ATT948',
      ],
      // Torque Products - W1_846466.
      'W1_846466' => [
        'ATT678639',
        'ATT714694',
        'ATT585',
        'ATT484',
      ],
      // Wrenches - W1_846467.
      'W1_846467' => [
        'ATT491',
        'ATT496',
        'ATT714694',
        'ATT493',
        'ATT749756',
        'ATT867476',
        'ATT948',
        'ATT781',
        'ATT254',
      ],
    ];
  }

}
