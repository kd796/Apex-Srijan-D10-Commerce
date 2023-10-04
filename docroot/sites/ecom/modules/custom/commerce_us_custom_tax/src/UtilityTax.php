<?php

namespace Drupal\commerce_us_custom_tax;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides utility functions for tax calculations.
 */
class UtilityTax {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Import tax data to taxonomy.
   */
  public function importTax($csv_row) {
    // Handling * condition for city.
    if ($csv_row['city'] == '*') {
      $exploded_name = explode('-', $csv_row['code']);
      $csv_row['city'] = $exploded_name[3];
    }
    // Handling * condition for county.
    if ($csv_row['county'] == '*') {
      $exploded_name = explode('-', $csv_row['code']);
      $csv_row['county'] = $exploded_name[2];
    }
    $postal_range_array = explode('-', $csv_row['postalcoderange']);
    // Need to seperate ex 90000-90002.
    $start_postal_code = $postal_range_array[0];
    $start_postal_code = (int) $start_postal_code;
    $end_postal_code = $postal_range_array[1] ?? $postal_range_array[0];
    $end_postal_code = (int) $end_postal_code;
    $existing_term = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $csv_row['code'],
        'vid' => 'us_tax_data',
      ]);

    if (!empty($existing_term)) {
      $term = array_values($existing_term);
      $term[0]->field_us_city = $csv_row['city'];
      $term[0]->field_us_state = $csv_row['state'];
      $term[0]->field_us_county = $csv_row['county'];
      $term[0]->field_ending_zip_code = $end_postal_code;
      $term[0]->field_starting_zip_code = $start_postal_code;
      $term[0]->field_rate = $csv_row['rate'];
      $term[0]->save();

    }
    else {
      $new_term = Term::create([
        'name' => $csv_row['code'],
        'vid' => 'us_tax_data',
        'field_us_state' => [
          'value' => $csv_row['state'],
        ],
        'field_us_city' => [
          'value' => $csv_row['city'],
        ],
        'field_us_county' => [
          'value' => $csv_row['county'],
        ],
        'field_ending_zip_code' => [
          'value' => $end_postal_code,
        ],
        'field_starting_zip_code' => [
          'value' => $start_postal_code,
        ],
        'field_rate' => [
          'value' => $csv_row['rate'],
        ],
      ])->save();
    }
  }

  /**
   * Getting term name based on tid.
   */
  public function getTermName($tid) {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    $term_name = $term->get('name')->value;
    return $term_name;
  }

  /**
   * Getting matched tax rate.
   */
  public function getMatching($state, $postal_code, $city, $county) {
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $term_ids = $query->condition('vid', 'us_tax_data')
      ->accessCheck(FALSE)
      ->condition('field_us_state', $state)
      ->condition('field_us_city', $city)
      ->condition('field_starting_zip_code', $postal_code, '<=')
      ->condition('field_ending_zip_code', $postal_code, '>=');

    if (!empty($county)) {
      $term_ids->condition('field_us_county', $county);
    }

    $term_ids = $term_ids->execute();

    // Changing index of array to start from 0.
    $term_ids = array_values($term_ids);

    if (empty($term_ids)) {
      // Sending mail if tax is 0.
      // For debuging.
      $params['message'] = "Tax is calculated as zero for the <p>State: {$state}</p> <p>Postal Code: {$postal_code}</p> <p>City: {$city}</p> <p>County: {$county}</p>";
      \Drupal::logger('commerce_us_custom_tax')->notice($params['message']);
      return 0;
    }
    else {
      return($this->getRate($term_ids[0]));
    }
  }

  /**
   * Returning tax rate.
   */
  public function getRate($tid) {

    $rate = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid)->get('field_rate')->value;
    return $rate;
  }

}
