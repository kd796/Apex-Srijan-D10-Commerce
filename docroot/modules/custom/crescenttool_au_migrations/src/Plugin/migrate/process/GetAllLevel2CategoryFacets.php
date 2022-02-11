<?php

namespace Drupal\crescenttool_au_migrations\Plugin\migrate\process;

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
      'W2_777129' => ['ATT802893', 'ATT778642' , 'ATT763883'],
      'W2_719536' => ['ATT802893'],
      'W2_744962' => ['ATT802893'],
      'W2_719533' => ['ATT802893'],
      'W2_803436' => ['ATT802893'],
      'W2_806576' => [
        'ATT496', 'ATT802893', 'ATT499', 'ATT493', 'ATT491', 'ATT744972',
        'ATT744973'
      ],
      'W2_806577' => [
        'ATT496', 'ATT802893', 'ATT499', 'ATT493', 'ATT491', 'ATT744972',
        'ATT744973'
      ],
      'W2_806578' => [
        'ATT496', 'ATT802893', 'ATT499', 'ATT493', 'ATT491', 'ATT744972',
        'ATT744973'
      ],
      'W2_806579' => [
        'ATT496', 'ATT802893', 'ATT499', 'ATT493', 'ATT491', 'ATT744972',
        'ATT744973'
      ],
      'W2_806580' => [
        'ATT496', 'ATT802893', 'ATT499', 'ATT493', 'ATT491', 'ATT744972',
        'ATT744973'
      ],
      'W2_22492' => [
        'ATT496', 'ATT802893', 'ATT499', 'ATT493', 'ATT491', 'ATT744972',
        'ATT744973'
      ],
      'W2_792547' => ['ATT802893', 'ATT345', 'ATT205', 'ATT159'],
      'W2_22489' => ['ATT496', 'ATT802893', 'ATT491', 'ATT205'],
      'W2_761733' => ['ATT496', 'ATT802893', 'ATT491', 'ATT205', 'ATT226'],
      'W2_22491' => ['ATT496', 'ATT802893', 'ATT714694'],
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
      // Bolt Cutters - W1_729125.
      'W1_729125' => [
        'ATT496',
        'ATT802893',
        'ATT278',
      ],
      // Cable Ties - W1_729889.
      'W1_729889' => [
        'ATT496',
        'ATT802893',
      ],
      // Clamps - W1_729121.
      'W1_729121' => [
        'ATT496',
        'ATT802893',
      ],
      // Cutting - W1_729126.
      'W1_729126' => [
        'ATT496',
        'ATT802893',
        'ATT340',
        'ATT769436',
        'ATT278',
        'ATT686141',
      ],
      // Demolition Tools - W1_729120.
      'W1_729120' => [
        'ATT496',
        'ATT802893',
        'ATT582',
        'ATT583',
      ],
      // Hammers - W1_729124.
      'W1_729124' => [
        'ATT496',
        'ATT802893',
        'ATT228',
        'ATT227',
        'ATT345',
      ],
      // Hand Files - W1_729128.
      'W1_729128' => [
        'ATT496',
        'ATT802893',
        'ATT493',
        'ATT201',
        'ATT547',
      ],
      // Hex Keys - W1_729123.
      'W1_729123' => [
        'ATT496',
        'ATT802893',
        'ATT493',
        'ATT660',
        'ATT592',
        'ATT659'
      ],
      // Laser Product - W1_729890.
      'W1_729890' => [
        'ATT496',
        'ATT802893',
        'ATT584827',
        'ATT592',
      ],
      // Leather Aprons & Belts - W1_734127.
      'W1_734127' => [
        'ATT496',
        'ATT802893',
      ],
      // Measuring and Marking - W1_729127.
      'W1_729127' => [
        'ATT802893',
        'ATT807193',
        'ATT127',
        'ATT130',
        'ATT592',
        'ATT593',
      ],
      // Mechanics Tools - W1_729117.
      'W1_729117' => [
        'ATT496',
        'ATT802893',
      ],
      // Miscellaneous Tools - W1_729885.
      'W1_729885' => [
        'ATT496',
        'ATT802893',
      ],
      // Pliers - W1_729119.
      'W1_729119' => [
        'ATT802893',
        'ATT496',
        'ATT259',
        'ATT226',
        'ATT880',
        'ATT451',
        'ATT115',
        'ATT714720'
      ],
      // Ratchets and Drive Tools - W1_729115.
      'W1_729115' => [
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
      // Screwdrivers and Nutdrivers - W1_729118.
      'W1_729118' => [
        'ATT496',
        'ATT802893',
        'ATT415',
        'ATT631',
        'ATT806593'
      ],
      // Sockets and Sets - W1_729114.
      'W1_729114' => [
        'ATT496',
        'ATT802893',
        'ATT484',
        'ATT499',
        'ATT493',
        'ATT491',
        'ATT744972',
        'ATT744973',
        'ATT806802'
      ],
      // Storage and Tool Holders - W1_729887.
      'W1_729887' => [
        'ATT802893',
        'ATT345',
        'ATT205',
        'ATT159',
      ],
      // Strippers and Crimpers - W1_729884.
      'W1_729884' => [
        'ATT496',
        'ATT802893',
      ],
      // Torque Wrenches - W1_729122.
      'W1_729122' => [
        'ATT806600',
        'ATT802893',
        'ATT484',
        'ATT585',
        'ATT714694',
        'ATT753929',
      ],
      // Wrenches - W1_729116.
      'W1_729116' => [
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

}
