<?php

namespace Drupal\sata_us_migrations\Plugin\migrate\process;

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
   *
   * @param string $category_remote_id
   *   The category ID.
   *
   * @return array|mixed
   *   The resulting array (empty or not).
   */
  protected function mapCategoryToCustomFacets(string $category_remote_id) {
    $mapping = self::getLevel2AttributeMapping();

    if (!empty($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
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
  protected function mapCategoryToFacetsList(string $category_remote_id) {
    $mapping = GetAllCategoryFacets::getLevel1AttributeMapping();

    if (!empty($mapping[$category_remote_id])) {
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
  public static function getLevel2AttributeMapping(): array {
    return [
      'W2_852367' => [
        'ATT497',
        'ATT495',
        'ATT494',
      ],
      'W2_852368' => ['ATT948'],
      'W2_852442' => ['ATT948'],
      'W2_852443' => ['ATT948'],
      'W2_852444' => ['ATT948'],
      'W2_852445' => [
        'ATT948',
        'ATT781',
        'ATT254',
      ],
      'W2_852446' => ['ATT948'],
      'W2_852447' => ['ATT948'],
      'W2_852448' => ['ATT948'],
      'W2_852449' => ['ATT948'],
      'W2_852450' => ['ATT948'],
      'W2_852451' => [],
      'W2_852452' => [],
      'W2_852453' => [
        'ATT176',
        'ATT781',
        'ATT450',
      ],
      'W2_852454' => ['ATT948'],
      'W2_852456' => [],
      'W2_852465' => [
        'ATT948',
        'ATT584880',
        'ATT584885',
      ],
      'W2_852466' => [
        'ATT948',
        'ATT326',
        'ATT584880',
        'ATT584797',
      ],
      'W2_848680' => ['ATT948'],
      'W2_848683' => [
        'ATT948',
        'ATT769436',
        'ATT176',
      ],
      'W2_848684' => [],
      'W2_848685' => [
        'ATT176',
        'ATT781',
      ],
      'W2_848686' => [],
      'W2_848687' => ['ATT128'],
      'W2_852457' => [
        'ATT659',
        'ATT660',
        'ATT493',
        'ATT491',
      ],
      'W2_852458' => ['ATT493'],
      'W2_852395' => [
        'ATT491',
        'ATT804086',
        'ATT493',
      ],
      'W2_852396' => ['ATT804086'],
      'W2_852397' => ['ATT804086'],
      'W2_852399' => ['ATT948'],
      'W2_852400' => ['ATT948'],
      'W2_852401' => ['ATT948', 'ATT414'],
      'W2_867885' => ['ATT414'],
      'W2_852402' => [],
      'W2_852403' => ['ATT948'],
      'W2_852404' => ['ATT948'],
      'W2_852374' => ['ATT948'],
      'W2_852376' => ['ATT948'],
      'W2_852377' => [
        'ATT128',
        'ATT133',
        'ATT130',
      ],
      'W2_852378' => [],
      'W2_852379' => [
        'ATT345',
        'ATT948',
        'ATT425',
      ],
      'W2_852380' => ['ATT867467'],
      'W2_852381' => [],
      'W2_852382' => ['ATT425', 'ATT670298'],
      'W2_852383' => ['ATT425', 'ATT867471'],
      'W2_852405' => ['ATT948', 'ATT259'],
      'W2_852408' => ['ATT948'],
      'W2_852409' => ['ATT948'],
      'W2_852410' => ['ATT948'],
      'W2_852411' => ['ATT948'],
      'W2_852412' => ['ATT948', 'ATT259'],
      'W2_852413' => ['ATT948', 'ATT259'],
      'W2_852419' => ['ATT948'],
      'W2_852425' => ['ATT947'],
      'W2_852393' => [
        'ATT728214',
        'ATT867472',
        'ATT22507',
        'ATT662382',
      ],
      'W2_852394' => [
        'ATT728214',
        'ATT867472',
        'ATT837657',
        'ATT662382',
      ],
      'W2_852460' => ['ATT585'],
      'W2_852461' => ['ATT585'],
      'W2_852459' => ['ATT585'],
      'W2_852463' => ['ATT585'],
      'W2_852464' => ['ATT484', 'ATT585'],
      'W2_852388' => [
        'ATT631',
        'ATT415',
        'ATT497',
      ],
      'W2_852389' => [
        'ATT415',
        'ATT497',
      ],
      'W2_852390' => ['ATT631', 'ATT497'],
      'W2_852391' => ['ATT948', 'ATT947'],
      'W2_852369' => ['ATT584933', 'ATT867473'],
      'W2_852370' => ['ATT584933'],
      'W2_852371' => ['ATT673955', 'ATT867475'],
      'W2_852372' => [],
      'W2_852384' => [
        'ATT345',
        'ATT236',
        'ATT728177',
      ],
      'W2_852385' => [
        'ATT563',
        'ATT236',
        'ATT728177',
      ],
      'W2_852386' => [],
      'W2_852387' => [],
      'W2_848690' => ['ATT497'],
      'W2_848691' => ['ATT484'],
      'W2_848692' => ['ATT493'],
      'W2_848963' => ['ATT496'],
      'W2_848574' => [],
      'W2_848575' => ['ATT948'],
      'W2_848576' => [],
      'W2_848577' => ['ATT345', 'ATT948'],
      'W2_848578' => ['ATT948'],
      'W2_848580' => ['ATT948'],
      'W2_848581' => [],
      'W2_848582' => [],
      'W2_848572' => [
        'ATT678639',
        'ATT714694',
        'ATT585',
        'ATT484',
      ],
      'W2_848573' => [
        'ATT678639',
        'ATT585',
        'ATT484',
      ],
      'W2_846468' => [
        'ATT491',
        'ATT496',
        'ATT714694',
        'ATT493',
        'ATT749756',
      ],
      'W2_848165' => [
        'ATT491',
        'ATT496',
        'ATT867476',
        'ATT493',
      ],
      'W2_848166' => ['ATT948'],
      'W2_848168' => [
        'ATT948',
        'ATT781',
        'ATT254'
      ],
      'W2_848169' => [
        'ATT948',
        'ATT781',
        'ATT254',
      ],
    ];
  }

}
