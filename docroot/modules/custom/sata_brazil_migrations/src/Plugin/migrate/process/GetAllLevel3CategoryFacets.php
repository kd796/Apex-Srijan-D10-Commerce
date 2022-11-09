<?php

namespace Drupal\sata_brazil_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get the facets for the 3rd tier categories.
 *
 * Since there are no custom facets for 3rd tier, we fallback to the 1st.
 *
 * @MigrateProcessPlugin(
 *   id = "get_all_level_3_category_facets"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_all_level_3_category_facets
 *   source: text
 * @endcode
 */
class GetAllLevel3CategoryFacets extends ProcessPluginBase {

  /**
   * The mapping array.
   *
   * @var array|\string[][]
   */
  public static array $mapping = [
    'W3_777844' => ['ATT584933'],
    'W3_777845' => ['ATT584933'],
    'W3_870823' => ['ATT584933'],
    'W3_777734' => ['ATT497', 'ATT948', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777737' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777738' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777739' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777740' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777741' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777742' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777743' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777744' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777745' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W4_777746' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W3_777735' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W3_777817' => ['ATT497', 'ATT948'],
    'W3_777818' => ['ATT497', 'ATT948'],
    'W3_777819' => ['ATT497', 'ATT493', 'ATT948', 'ATT420'],
    'W3_777820' => ['ATT497', 'ATT493', 'ATT948', 'ATT420'],
    'W3_777822' => ['ATT497', 'ATT493', 'ATT948', 'ATT420'],
    'W3_777823' => ['ATT497', 'ATT948', 'ATT420'],
    'W3_777824' => ['ATT497', 'ATT948', 'ATT420'],
    'W3_777825' => ['ATT497', 'ATT420'],
    'W3_777841' => ['ATT497', 'ATT948', 'ATT493', 'ATT420', 'ATT835'],
    'W3_869841' => ['ATT497', 'ATT493', 'ATT420', 'ATT835'],
    'W3_777736' => ['ATT948'],
    'W3_777759' => ['ATT948'],
    'W3_788105' => ['ATT948', 'ATT755881'],
    'W3_777738' => ['ATT948', 'ATT425'],
    'W3_777753' => ['ATT948', 'ATT425'],
    'W3_777737' => ['ATT948', 'ATT425'],
    'W3_777752' => ['ATT948'],
    'W3_777758' => ['ATT948'],
    'W3_777761' => ['ATT948'],
    'W3_777763' => ['ATT948', 'ATT867587', 'ATT425'],
    'W3_777764' => ['ATT948', 'ATT755881'],
    'W3_777765' => ['ATT948'],
    'W3_777766' => ['ATT948'],
    'W3_799005' => ['ATT948', 'ATT425'],
    'W3_799006' => ['ATT948', 'ATT425'],
    'W3_870888' => ['ATT948', 'ATT425'],
    'W3_777739' => ['ATT948', 'ATT425'],
    'W3_777815' => ['ATT948'],
    'W3_777835' => ['ATT948'],
    'W3_777740' => ['ATT687593'],
    'W3_777745' => ['ATT948'],
    'W3_777746' => ['ATT948', 'ATT345'],
    'W3_777741' => ['ATT948'],
    'W3_777742' => ['ATT948'],
    'W3_777748' => ['ATT948'],
    'W3_777750' => ['ATT948', 'ATT425', 'ATT176', 'ATT185'],
    'W3_777743' => ['ATT948', 'ATT493', 'ATT835'],
    'W3_777744' => ['ATT948', 'ATT493', 'ATT835'],
    'W3_777786' => ['ATT948', 'ATT493', 'ATT835'],
    'W3_777767' => ['ATT948', 'ATT425'],
    'W3_777776' => ['ATT948'],
    'W3_777777' => ['ATT948'],
    'W3_777790' => ['ATT948'],
    'W3_777792' => ['ATT948'],
    'W3_777794' => ['ATT948'],
    'W3_777804' => ['ATT948', 'ATT835', 'ATT493'],
    'W3_777812' => ['ATT948', 'ATT835', 'ATT493'],
    'W3_777828' => ['ATT948'],
    'W3_777751' => ['ATT948', 'ATT425'],
    'W3_777754' => ['ATT948'],
    'W3_777755' => ['ATT948'],
    'W3_777756' => ['ATT948'],
    'W3_777757' => ['ATT948', 'ATT263'],
    'W3_777760' => ['ATT948', 'ATT493'],
    'W3_777769' => ['ATT948', 'ATT493'],
    'W3_777771' => ['ATT948', 'ATT493'],
    'W3_777772' => ['ATT948', 'ATT493'],
    'W3_777773' => ['ATT948', 'ATT493'],
    'W3_777774' => ['ATT948', 'ATT493'],
    'W3_777775' => ['ATT948', 'ATT493'],
    'W3_799009' => ['ATT948', 'ATT493'],
    'W3_799010' => ['ATT948', 'ATT493'],
    'W3_777778' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777781' => ['ATT948', 'ATT493'],
    'W3_777788' => ['ATT948'],
    'W3_777805' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777806' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777807' => ['ATT948'],
    'W3_777808' => ['ATT948', 'ATT493'],
    'W3_777809' => ['ATT948', 'ATT493'],
    'W3_777810' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777811' => ['ATT948', 'ATT493'],
    'W3_777813' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777814' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777816' => ['ATT948', 'ATT584466', 'ATT744972'],
    'W3_777779' => ['ATT497', 'ATT948', 'ATT493'],
    'W3_777780' => ['ATT497', 'ATT948', 'ATT493'],
    'W3_777782' => ['ATT497', 'ATT948', 'ATT493'],
    'W3_777783' => ['ATT497', 'ATT948', 'ATT493'],
    'W3_777784' => ['ATT497', 'ATT948', 'ATT493'],
    'W3_777733' => ['ATT497', 'ATT948', 'ATT493'],
    'W3_777785' => ['ATT948', 'ATT493'],
    'W3_777789' => ['ATT948', 'ATT493'],
    'W3_777791' => ['ATT948', 'ATT493'],
    'W3_777793' => ['ATT948', 'ATT493'],
    'W3_777795' => ['ATT948', 'ATT493'],
    'W3_777796' => ['ATT948', 'ATT493'],
    'W3_777797' => ['ATT948', 'ATT493'],
    'W3_777798' => ['ATT948', 'ATT493'],
    'W3_777830' => ['ATT948', 'ATT493'],
    'W3_777831' => ['ATT948', 'ATT493'],
    'W3_777832' => ['ATT948', 'ATT493'],
    'W3_777840' => ['ATT493'],
    'W3_777842' => ['ATT948', 'ATT493'],
    'W3_807286' => ['ATT948', 'ATT493'],
    'W3_777799' => ['ATT943', 'ATT584466', 'ATT744972'],
    'W3_777800' => ['ATT943', 'ATT584466', 'ATT744972'],
    'W3_777801' => ['ATT943', 'ATT584466', 'ATT744972'],
    'W3_777802' => ['ATT948', 'ATT493'],
    'W3_777803' => ['ATT943', 'ATT584466', 'ATT744972'],
    'W3_777829' => ['ATT943', 'ATT584466', 'ATT744972'],
    'W3_777826' => ['ATT948'],
    'W3_777827' => ['ATT948', 'ATT425'],
    'W3_777837' => ['ATT948'],
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

    $all_terms_array = [];
    $value = (string) $value;
    $facets = $this->mapCategoryToCustomFacets($value);

    if (empty($facets)) {
      $level_two_parent = $this->getParentTermFromClassificationId($value);

      if (!empty($level_two_parent)) {
        $level_two_parent_source_id = $this->getClassificationId($level_two_parent);

        if (!empty($level_two_parent_source_id)) {
          $facets = $this->mapCategoryToL2FacetsList($level_two_parent_source_id);

          if (empty($facets)) {
            $top_level_parent = $this->getParentTermFromClassificationId($level_two_parent_source_id);

            if (!empty($top_level_parent)) {
              $top_level_parent_source_id = $this->getClassificationId($top_level_parent);

              if (!empty($top_level_parent_source_id)) {
                // Load the facets for the parent instead.
                $facets = $this->mapCategoryToFacetsList($top_level_parent_source_id);
              }
            }
          }
        }
      }
    }

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
   * Gets the parent term from a classification ID.
   */
  protected function getParentTermFromClassificationId($classificationID) {
    // Get the parent term ID of the current term.
    $product_categories = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'product_classifications',
      'field_classification_id' => $classificationID,
    ]);

    if (!empty($product_categories)) {
      /** @var \Drupal\taxonomy\Entity\Term $current_term */
      $current_term = array_pop($product_categories);

      if (!is_object($current_term)) {
        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($current_term->id());

        /** @var \Drupal\taxonomy\Entity\Term $parent */
        return reset($parent);
      }
    }

    return NULL;
  }

  /**
   * Get the classification ID from the parent of the passed in term.
   */
  protected function getClassificationId($term) {
    if (is_object($term)) {
      $term_arr = $term->toArray();

      if (!empty($term_arr['field_classification_id'][0]['value'])) {
        return $term_arr['field_classification_id'][0]['value'];
      }
    }

    return NULL;
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
  protected function mapCategoryToL2FacetsList($category_remote_id) {
    $mapping = GetAllLevel2CategoryFacets::$mapping;

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
