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
   */
  protected function mapCategoryToCustomFacets($category_remote_id) {
    $mapping = [
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

    if (!empty($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
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
