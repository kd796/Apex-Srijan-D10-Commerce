<?php

namespace Drupal\sata_emea_migrations\Plugin\migrate\process;

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
    'W2_843922' => ['ATT948'],
    'W2_867627' => ['ATT948', 'ATT345'],
    'W2_843923' => ['ATT948'],
    'W2_867128' => ['ATT948'],
    'W2_867129' => ['ATT834'],
    'W2_867130' => ['ATT948', 'ATT789490'],
    'W2_867131' => ['ATT948'],
    'W2_867132' => ['ATT948', 'ATT789490'],
    'W2_867133' => ['ATT948', 'ATT789490', 'ATT497'],
    'W2_867134' => [
      'ATT835',
      'ATT493',
      'ATT420',
      'ATT428',
      'ATT497',
    ],
    'W2_867139' => [
      'ATT585',
      'ATT589',
      'ATT631',
      'ATT838034',
      'ATT867587',
      'ATT867592',
      'ATT948',
    ],
    'W2_867140' => ['ATT835'],
    'W2_867141' => ['ATT835'],
    'W2_867142' => ['ATT835'],
    'W2_867143' => ['ATT835'],
    'W2_867144' => ['ATT835'],
    'W2_867151' => [
      'ATT491',
      'ATT493',
      'ATT498',
      'ATT499',
      'ATT584466',
      'ATT661605',
      'ATT835',
      'ATT867592',
    ],
    'W2_867152' => ['ATT835'],
    'W2_867153' => 'ATT835',
    'W2_867166' => [
      'ATT867591',
      'ATT584466',
      'ATT661605',
      'ATT835',
    ],
    'W2_867494' => [
      'ATT835',
      'ATT493',
      'ATT584466',
      'ATT661605',
      'ATT867592',
    ],
    'W2_867495' => [
      'ATT835',
      'ATT491',
      'ATT493',
      'ATT584466',
      'ATT661605',
    ],
    'W2_867496' => [
      'ATT835',
      'ATT493',
      'ATT584466',
      'ATT661605',
      'ATT867592',
    ],
    'W2_867497' => [
      'ATT835',
      'ATT491',
      'ATT493',
      'ATT584466',
      'ATT661605',
    ],
    'W2_867498' => [
      'ATT835',
      'ATT425',
      'ATT867592',
    ],
    'W2_867169' => [
      'ATT493',
      'ATT948',
      'ATT867594',
      'ATT923',
      'ATT867592',
    ],
    'W2_867170' => [
      'ATT493',
      'ATT948',
      'ATT923',
      'ATT867592',
    ],
    'W2_867171' => [
      'ATT493',
      'ATT948',
      'ATT923',
      'ATT867592',
    ],
    'W2_867172' => [
      'ATT493',
      'ATT948',
      'ATT923',
      'ATT867592',
    ],
    'W2_867173' => [
      'ATT493',
      'ATT948',
      'ATT923',
      'ATT867592',
    ],
    'W2_867175' => [
      'ATT493',
      'ATT948',
      'ATT923',
      'ATT867592',
    ],
    'W2_867176' => ['ATT493', 'ATT948'],
    'W2_867177' => ['ATT493', 'ATT948'],
    'W2_867178' => ['ATT493', 'ATT948', 'ATT923'],
    'W2_867179' => ['ATT493', 'ATT948', 'ATT923'],
    'W2_867180' => ['ATT493', 'ATT948'],
    'W2_867181' => ['ATT948'],
    'W2_867182' => ['ATT493', 'ATT948'],
    'W2_867183' => ['ATT493', 'ATT948'],
    'W2_867200' => ['ATT948', 'ATT838034', 'ATT254'],
    'W2_867201' => ['ATT948', 'ATT838034', 'ATT254'],
    'W2_867202' => ['ATT948', 'ATT838034', 'ATT254'],
    'W2_867203' => ['ATT948', 'ATT838034', 'ATT254'],
    'W2_867207' => ['ATT493', 'ATT948', 'ATT923', 'ATT867592'],
    'W2_867210' => [
      'ATT493',
      'ATT948',
      'ATT867595',
      'ATT683454',
      'ATT923',
      'ATT867592',
    ],
    'W2_867211' => ['ATT493', 'ATT948', 'ATT923', 'ATT867592'],
    'W2_867212' => ['ATT493', 'ATT948', 'ATT923', 'ATT867592'],
    'W2_867213' => ['ATT493', 'ATT948', 'ATT923', 'ATT683454'],
    'W2_867214' => ['ATT493', 'ATT948', 'ATT923', 'ATT683454'],
    'W2_867216' => ['ATT493', 'ATT948', 'ATT923', 'ATT867592'],
    'W2_867220' => ['ATT493', 'ATT948'],
    'W2_867222' => ['ATT835', 'ATT923'],
    'W2_867223' => ['ATT672487'],
    'W2_867228' => ['ATT867587', 'ATT497', 'ATT867573'],
    'W2_867229' => [
      'ATT948',
      'ATT838034',
      'ATT867575',
      'ATT867587',
      'ATT867574',
      'ATT176',
    ],
    'W2_867230' => [
      'ATT948',
      'ATT838034',
      'ATT867575',
      'ATT867587',
      'ATT867574',
      'ATT176',
    ],
    'W2_867231' => [
      'ATT948',
      'ATT838034',
      'ATT867575',
      'ATT867587',
      'ATT867574',
      'ATT176',
    ],
    'W2_867232' => [
      'ATT867573',
      'ATT948',
      'ATT838034',
      'ATT176',
    ],
    'W2_867233' => [
      'ATT948',
      'ATT838034',
      'ATT176',
    ],
    'W2_867234' => [
      'ATT948',
      'ATT838034',
      'ATT176',
    ],
    'W2_867235' => [
      'ATT948',
      'ATT838034',
      'ATT867574'
    ],
    'W2_867236' => [
      'ATT948',
      'ATT838034',
    ],
    'W2_867237' => [
      'ATT948',
      'ATT838034',
      'ATT584720',
    ],
    'W2_867238' => [
      'ATT948',
      'ATT838034',
      'ATT584720',
    ],
    'W2_867239' => ['ATT425'],
    'W2_867240' => [
      'ATT948',
      'ATT838034',
      'ATT584720',
    ],
    'W2_867241' => [
      'ATT948',
      'ATT838034',
      'ATT584720',
    ],
    'W2_867242' => [
      'ATT948',
      'ATT838034',
      'ATT227',
    ],
    'W2_867243' => [
      'ATT948',
      'ATT838034',
      'ATT584724',
      'ATT683449',
    ],
    'W2_867244' => [
      'ATT948',
      'ATT838034',
    ],
    'W2_867245' => [
      'ATT948',
      'ATT838034',
    ],
    'W2_867246' => [
      'ATT867573',
      'ATT948',
      'ATT838034',
      'ATT584720',
    ],
    'W2_867268' => ['ATT867574', 'ATT867592'],
    'W2_867270' => [
      'ATT948',
      'ATT838034',
      'ATT451',
      'ATT867582'
    ],
    'W2_867271' => [
      'ATT948',
      'ATT838034',
      'ATT185'
    ],
    'W2_867272' => [
      'ATT948',
      'ATT838034',
      'ATT176'
    ],
    'W2_867273' => [
      'ATT948',
      'ATT838034',
      'ATT128',
      'ATT769436'
    ],
    'W2_867274' => [
      'ATT948',
      'ATT838034',
      'ATT769436'
    ],
    'W2_867275' => [
      'ATT948',
      'ATT838034',
      'ATT769436'
    ],
    'W2_867276' => [
      'ATT948',
      'ATT838034',
      'ATT769436'
    ],
    'W2_867277' => [
      'ATT948',
      'ATT838034',
      'ATT176'
    ],
    'W2_867278' => [
      'ATT128',
      'ATT838020',
      'ATT531',
      'ATT867587',
    ],
    'W2_867280' => ['ATT176', 'ATT354'],
    'W2_867281' => ['ATT176'],
    'W2_867282' => ['ATT176'],
    'W2_867283' => [
      'ATT838034',
      'ATT544',
      'ATT948',
      'ATT789979',
      'ATT867585',
    ],
    'W2_867296' => [
      'ATT226',
      'ATT415',
      'ATT497',
      'ATT867596',
    ],
    'W2_867297' => ['ATT497', 'ATT227'],
    'W2_867298' => ['ATT415', 'ATT948', 'ATT803'],
    'W2_867300' => ['ATT415', 'ATT948', 'ATT803'],
    'W2_867302' => ['ATT948', 'ATT803'],
    'W2_867305' => ['ATT415', 'ATT948', 'ATT803'],
    'W2_867307' => ['ATT948', 'ATT803'],
    'W2_867308' => ['ATT948', 'ATT803'],
    'W2_867309' => ['ATT415', 'ATT948', 'ATT803'],
    'W2_867310' => ['ATT948', 'ATT803'],
    'W2_867312' => ['ATT415', 'ATT497'],
    'W2_867313' => ['ATT948', 'ATT497'],
    'W2_867314' => [
      'ATT27860',
      'ATT499',
      'ATT584477',
      'ATT622',
      'ATT948',
    ],
    'W2_867315' => [
      'ATT27860',
      'ATT227',
      'ATT493',
      'ATT415',
      'ATT867577',
      'ATT867578',
      'ATT205',
    ],
    'W2_867316' => ['ATT948'],
    'W2_867317' => ['ATT948'],
    'W2_867318' => ['ATT948'],
    'W2_867319' => ['ATT948'],
    'W2_867320' => ['ATT948'],
    'W2_867321' => ['ATT948'],
    'W2_867322' => ['ATT948'],
    'W2_867323' => ['ATT948'],
    'W2_867346' => ['ATT726', 'ATT725', 'ATT130'],
    'W2_867347' => ['ATT726', 'ATT725', 'ATT130'],
    'W2_867351' => ['ATT948'],
    'W2_867352' => ['ATT948'],
    'W2_867353' => ['ATT948'],
    'W2_867354' => ['ATT948', 'ATT838034'],
    'W2_867355' => ['ATT584826'],
    'W2_867324' => ['ATT497', 'ATT948'],
    'W2_867325' => [
      'ATT27860',
      'ATT584804',
      'ATT535',
      'ATT584477',
    ],
    'W2_867326' => [
      'ATT345',
      'ATT584804',
      'ATT235',
      'ATT584477',
    ],
    'W2_867327' => [
      'ATT345',
      'ATT584804',
      'ATT235',
      'ATT584477',
    ],
    'W2_867328' => [
      'ATT584804',
      'ATT235',
      'ATT584477',
    ],
    'W2_867329' => [
      'ATT584804',
      'ATT235',
      'ATT563',
      'ATT584477',
    ],
    'W2_867330' => ['ATT664422'],
    'W2_867331' => [
      'ATT584804',
      'ATT235',
      'ATT563',
      'ATT584477',
    ],
    'W2_867332' => [
      'ATT584804',
      'ATT235',
      'ATT584477',
    ],
    'W2_867363' => [
      'ATT672487',
      'ATT867472',
      'ATT22507',
      'ATT673955',
      'ATT670486',
      'ATT661950',
    ],
    'W2_867364' => [
      'ATT672487',
      'ATT867472',
      'ATT837657',
      'ATT673955',
      'ATT670486',
      'ATT661950',
    ],
    'W2_867369' => [
      'ATT835',
      'ATT493',
      'ATT584466',
      'ATT661605',
    ],
    'W2_867372' => [
      'ATT493',
      'ATT948',
      'ATT838034',
      'ATT254',
    ],
    'W2_867373' => [
      'ATT948',
      'ATT584477',
      'ATT176',
    ],
    'W2_867374' => ['ATT584477'],
    'W2_867375' => [
      'ATT497',
      'ATT415',
      'ATT948',
      'ATT803',
    ],
    'W2_867378' => ['ATT584933'],
    'W2_867379' => ['ATT584933'],
    'W2_867380' => ['ATT497', 'ATT931'],
    'W2_867382' => ['ATT948', 'ATT664426'],
    'W2_867384' => ['ATT948', 'ATT145'],
    'W2_867388' => ['ATT948', 'ATT326', 'ATT254'],
    'W2_867396' => ['ATT948'],
    'W2_867398' => ['ATT584477'],
    'W2_867399' => [
      'ATT145',
      'ATT673955',
      'ATT867475',
      'ATT584772',
    ],
    'W2_867400' => ['ATT948'],
    'W2_867401' => ['ATT948'],
    'W2_867402' => ['ATT948', 'ATT425'],
    'W2_867523' => [
      'ATT428',
      'ATT585',
      'ATT589',
      'ATT631',
      'ATT835',
      'ATT838034',
      'ATT867587',
      'ATT867592',
      'ATT948',
    ],
    'W2_867524' => [
      'ATT425',
      'ATT491',
      'ATT493',
      'ATT584466',
      'ATT661605',
      'ATT835',
      'ATT867581',
      'ATT867592',
    ],
    'W2_867525' => [
      'ATT493',
      'ATT867592',
      'ATT923',
      'ATT948',
    ],
    'W2_867526' => [
      'ATT948',
      'ATT838034',
      'ATT254',
    ],
    'W2_867527' => ['ATT835', 'ATT672487'],
    'W2_867528' => [
      'ATT235',
      'ATT345',
      'ATT584477',
      'ATT584804',
    ],
    'W2_867529' => [
      'ATT130',
      'ATT584477',
      'ATT584826',
      'ATT725',
      'ATT726',
      'ATT948',
    ],
    'W2_867530' => [
      'ATT714709',
      'ATT714731',
      'ATT714732',
      'ATT714722',
    ],
    'W2_867404' => ['ATT583', 'ATT948'],
    'W2_867566' => ['ATT583', 'ATT948'],
    'W2_867568' => ['ATT714694', 'ATT584477', 'ATT584890'],
    'W2_867569' => ['ATT948'],
    'W2_867570' => ['ATT948'],
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
    $facets = [];

    if (!empty($mapping[$category_remote_id])) {
      $facets = $mapping[$category_remote_id];
    }
    if (!is_array($facets)) {
      $facets = [$facets];
    }

    return $facets;
  }

  /**
   * Check for list of facets.
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = GetAllCategoryFacets::$mapping;
    $facets = [];

    if (!empty($mapping[$category_remote_id])) {
      $facets = $mapping[$category_remote_id];
    }
    if (!is_array($facets)) {
      $facets = [$facets];
    }

    return $facets;
  }

}
