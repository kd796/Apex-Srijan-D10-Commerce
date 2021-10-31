<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "create_product_specifications"
 * )
 */
class CreateProductSpecifications extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $values_array = [];
    $vid = 'product_specifications';
    $parent_id = NULL;
    $parent_term_id = NULL;

    $product_specifications = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'product_specifications'
    ]);

    if (!empty($value)) {
      foreach ($value->children() as $child) {
        $parent_label = NULL;
        $parent_term_id = NULL;
        $parent_id = (string) $child->attributes()->AttributeID;
        $validAttribute = $this->validateAttributeName($parent_id);

        if ($validAttribute) {
          foreach ($product_specifications as $product_specification) {
            if (str_contains($product_specification->label(), $parent_id)) {
              $parent_label = str_replace($parent_id . ' | ', '', $product_specification->label());
              $parent_term_id = $product_specification->id();
              break;
            }
          }

          if (!empty($parent_term_id) && !empty($parent_label)) {
            $term = NULL;

            if ($child->getName() === 'MultiValue') {
              if (count($child->children()) > 1) {
                foreach ($child->children() as $item) {
                  $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $item);
                }
              }
              else {
                $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child->Value);
              }
            }
            else {
              $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child);
            }

            if (is_object($term)) {
              $values_array[] = [
                'vid' => $vid,
                'target_id' => $term->id()
              ];
            }
          }
        }
      }

      $values_array = json_encode($values_array);
    }

    return json_decode($values_array, TRUE);
  }

  /**
   * Creates a term with a parent. Then returns the loaded or created term.
   *
   * @param string $parent_label
   *   The name/label of the parent term.
   * @param int $parent_term_id
   *   The term ID of the parent term.
   * @param string $item_label
   *   The name/label of the item you are loading/creating.
   * @param string $vid
   *   The vocabulary ID you are adding this term to.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term object or NULL.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function loadOrCreateChildTerm($parent_label, $parent_term_id, $item_label, $vid = 'product_specifications') {
    $term_name = $parent_label . ' : ' . (string) $item_label;

    if ($tid = $this->getTidByName($term_name, $vid)) {
      $term = Term::load($tid);
    }
    else {
      $term = Term::create([
        'name' => $term_name,
        'vid' => $vid,
      ]);
    }

    $term->set('parent', $parent_term_id);
    $term->save();

    if ($tid = $this->getTidByName($term_name, $vid)) {
      $term = Term::load($tid);
    }

    return $term;
  }

  /**
   * Load term by name.
   */
  protected function getTidByName($name = NULL, $vocabulary = NULL): int {
    $properties = [];

    if (!empty($name)) {
      $properties['name'] = $name;
    }

    if (!empty($vocabulary)) {
      $properties['vid'] = $vocabulary;
    }

    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    return !empty($term) ? $term->id() : 0;
  }

  /**
   * Check for valid term.
   */
  protected function validateAttributeName($attribute = NULL) {
    $attributes_to_include = [
      'ATT115',
      'ATT125',
      'ATT128',
      'ATT132',
      'ATT159',
      'ATT170',
      'ATT17712',
      'ATT178',
      'ATT179',
      'ATT190',
      'ATT194',
      'ATT196',
      'ATT201',
      'ATT205',
      'ATT22085',
      'ATT22086',
      'ATT22087',
      'ATT22088',
      'ATT22089',
      'ATT224',
      'ATT22443',
      'ATT225',
      'ATT226',
      'ATT227',
      'ATT228',
      'ATT232',
      'ATT235',
      'ATT236',
      'ATT238',
      'ATT239',
      'ATT251',
      'ATT254',
      'ATT256',
      'ATT259',
      'ATT301',
      'ATT326',
      'ATT336',
      'ATT345',
      'ATT349',
      'ATT351',
      'ATT374',
      'ATT387',
      'ATT410',
      'ATT450',
      'ATT451',
      'ATT466',
      'ATT468',
      'ATT471',
      'ATT473',
      'ATT476',
      'ATT477',
      'ATT478',
      'ATT479',
      'ATT480',
      'ATT481',
      'ATT484',
      'ATT491',
      'ATT492',
      'ATT493',
      'ATT494',
      'ATT495',
      'ATT496',
      'ATT497',
      'ATT499',
      'ATT500',
      'ATT501',
      'ATT503',
      'ATT504',
      'ATT505',
      'ATT506',
      'ATT507',
      'ATT509',
      'ATT510',
      'ATT518',
      'ATT523',
      'ATT525',
      'ATT532',
      'ATT534',
      'ATT535',
      'ATT536',
      'ATT537',
      'ATT538',
      'ATT539',
      'ATT541',
      'ATT547',
      'ATT548',
      'ATT549',
      'ATT550',
      'ATT551',
      'ATT552',
      'ATT563',
      'ATT564',
      'ATT565',
      'ATT575',
      'ATT581',
      'ATT582',
      'ATT583',
      'ATT584',
      'ATT584730',
      'ATT584731',
      'ATT584734',
      'ATT584737',
      'ATT584868',
      'ATT584927',
      'ATT585',
      'ATT586',
      'ATT587',
      'ATT588',
      'ATT589',
      'ATT590',
      'ATT591',
      'ATT592',
      'ATT593',
      'ATT606',
      'ATT607',
      'ATT608',
      'ATT609',
      'ATT610',
      'ATT612',
      'ATT613',
      'ATT614',
      'ATT631',
      'ATT633',
      'ATT634',
      'ATT635',
      'ATT659132',
      'ATT659133',
      'ATT660',
      'ATT660051',
      'ATT660052',
      'ATT664101',
      'ATT678641',
      'ATT684692',
      'ATT689',
      'ATT693',
      'ATT708',
      'ATT709',
      'ATT710',
      'ATT714694',
      'ATT714714',
      'ATT714716',
      'ATT714720',
      'ATT714721',
      'ATT714725',
      'ATT714731',
      'ATT728',
      'ATT729',
      'ATT749756',
      'ATT755881',
      'ATT767142',
      'ATT777456',
      'ATT783458',
      'ATT783482',
      'ATT783483',
      'ATT783491',
      'ATT783492',
      'ATT783498',
      'ATT783499',
      'ATT789979',
      'ATT802893',
      'ATT804086',
      'ATT806600',
      'ATT807126',
      'ATT807127',
      'ATT807194',
      'ATT818',
      'ATT820',
      'ATT833',
      'ATT835',
      'ATT83507',
      'ATT83508',
      'ATT836',
      'ATT840',
      'ATT841',
      'ATT843',
      'ATT844',
      'ATT894',
      'ATT907',
      'ATT912',
      'ATT913',
      'ATT914',
      'ATT915',
      'ATT922',
      'ATT923',
      'ATT929',
      'ATT936',
      'ATT943',
      'ATT948',
      'ATT950',
      'Footnotes',
      'ForeignTrade',
      'JawThicknessLower',
      'JawThicknessUpper',
      'JawWidthLower',
      'JawWidthUpper',
      'ATT415',
      'ATT880',
      // 'Set',.
      // 'ATT744972',.
      // 'ATT744973',.
      // 'ATT806802',.
    ];

    if (in_array($attribute, $attributes_to_include)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
