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

    $postal_range_array = explode('-', $csv_row['postalcoderange']);
    $start_postal_code = $postal_range_array[0];
    $end_postal_code = $postal_range_array[1] ?? $postal_range_array[0];

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
      $term[0]->field_ending_postal_code = $end_postal_code;
      $term[0]->field_starting_postal_code = $start_postal_code;
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
        'field_ending_postal_code' => [
          'value' => $end,
        ],
        'field_starting_postal_code' => [
          'value' => $start,
        ],
        'field_rate' => [
          'value' => $csv_row['rate'],
        ],
      ])->save();
    }

  }

}
