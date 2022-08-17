<?php

namespace Drupal\sata_emea_migrations\Plugin\migrate\process;

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
    'W1_760608' => [
      'ATT948',
      'ATT345',
      'ATT834',
      'ATT789490',
      'ATT497',
      'ATT835',
      'ATT493',
      'ATT420',
      'ATT428',
    ],
    'W1_867137' => [
      'ATT497',
      'ATT835',
      'ATT493',
      'ATT940',
    ],
    'W1_867138' => [
      'ATT585',
      'ATT838034',
      'ATT589',
      'ATT631',
      'ATT867587',
      'ATT867592',
      'ATT948',
      'ATT835',
    ],
    'W1_867150' => [
      'ATT835',
      'ATT493',
      'ATT584466',
      'ATT661605',
      'ATT867592',
      'ATT498',
      'ATT499',
      'ATT491',
    ],
    'W1_867164' => ['ATT493', 'ATT835'],
    'W1_867165' => [
      'ATT867591',
      'ATT584466',
      'ATT661605',
      'ATT835',
    ],
    'W1_760609' => [
      'ATT835',
      'ATT493',
      'ATT584466',
      'ATT661605',
      'ATT867592',
      'ATT491',
      'ATT425',
    ],
    'W1_760614' => [
      'ATT493',
      'ATT948',
      'ATT867594',
      'ATT923',
      'ATT867592',
    ],
    'W1_867199' => [
      'ATT948',
      'ATT838034',
      'ATT254',
    ],
    'W1_867206' => [
      'ATT948',
      'ATT838034',
      'ATT254',
    ],
    'W1_760615' => [
      'ATT493',
      'ATT948',
      'ATT923',
      'ATT867592',
      'ATT493',
      'ATT867595',
      'ATT683454',
      'ATT835',
    ],
    'W1_760618' => ['ATT672487'],
    'W1_760610' => [
      'ATT867587',
      'ATT176',
      'ATT227',
      'ATT425',
      'ATT497',
      'ATT584720',
      'ATT584724',
      'ATT683449',
      'ATT838034',
      'ATT867573',
      'ATT867574',
      'ATT867575',
      'ATT948',
    ],
    'W1_843924' => [
      'ATT128',
      'ATT176',
      'ATT185',
      'ATT451',
      'ATT531',
      'ATT544',
      'ATT769436',
      'ATT789979',
      'ATT838020',
      'ATT838034',
      'ATT867574',
      'ATT867582',
      'ATT867585',
      'ATT867587',
      'ATT867592',
      'ATT948',
    ],
    'W1_760612' => [
      'ATT226',
      'ATT227',
      'ATT27860',
      'ATT415',
      'ATT497',
      'ATT499',
      'ATT584477',
      'ATT622',
      'ATT803',
      'ATT867596',
      'ATT948',
    ],
    'W1_760616' => [
      'ATT205',
      'ATT227',
      'ATT27860',
      'ATT415',
      'ATT493',
      'ATT867577',
      'ATT867578',
      'ATT948',
    ],
    'W1_824996' => [
      'ATT130',
      'ATT584826',
      'ATT725',
      'ATT726',
      'ATT838034',
      'ATT948',
    ],
    'W1_760613' => [
      'ATT235',
      'ATT27860',
      'ATT345',
      'ATT535',
      'ATT563',
      'ATT584477',
      'ATT584804',
    ],
    'W1_867362' => [
      'ATT22507',
      'ATT661950',
      'ATT670486',
      'ATT672487',
      'ATT673955',
      'ATT837657',
      'ATT867472',
    ],
    'W1_867365' => [
      'ATT714709',
      'ATT714722',
      'ATT714731',
      'ATT714732',
      'ATT867580',
    ],
    'W1_867366' => [
      'ATT176',
      'ATT254',
      'ATT415',
      'ATT493',
      'ATT497',
      'ATT584466',
      'ATT584477',
      'ATT661605',
      'ATT803',
      'ATT835',
      'ATT838034',
      'ATT948',
    ],
    'W1_760617' => [
      'ATT145',
      'ATT425',
      'ATT497',
      'ATT584477',
      'ATT584772',
      'ATT584933',
      'ATT673955',
      'ATT867475',
      'ATT931',
      'ATT948',
    ],
    'W1_760619' => [
      'ATT130',
      'ATT235',
      'ATT345',
      'ATT425',
      'ATT428',
      'ATT491',
      'ATT493',
      'ATT584466',
      'ATT584477',
      'ATT584804',
      'ATT584826',
      'ATT585',
      'ATT589',
      'ATT631',
      'ATT661605',
      'ATT672487',
      'ATT725',
      'ATT726',
      'ATT835',
      'ATT838034',
      'ATT867581',
      'ATT867587',
      'ATT867592',
      'ATT923',
      'ATT948',
    ],
    'W1_867565' => [
      'ATT583',
      'ATT584477',
      'ATT584890',
      'ATT714694',
      'ATT948',
    ]
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
