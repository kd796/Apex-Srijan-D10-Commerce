<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class ExtensionsAndAdaptersConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Extensions & Adapters', 'Verlängerungen und Adapter']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
	$definition->addColumn('Drive Size') //att666986
               ->forKey('values.drive_size');
	$definition->addColumn('Diameter Nose End (B)') // att667014
               ->forImperialKey('values.diameter_nose_end_b_in', 'in')
               ->forMetricKey('values.diameter_nose_end_b_mm', 'mm');
	$definition->addColumn('Diameter Drive End (C)') // att667017
               ->forImperialKey('values.diameter_drive_end_c_in', 'in')
               ->forMetricKey('values.diameter_drive_end_c_mm', 'mm');
	$definition->addColumn('Male Square Drive') // att728148
               ->forImperialKey('values.male_square_drive_in', 'in')
               ->forMetricKey('values.male_square_drive_mm', 'mm');
	$definition->addColumn('Female Square') // att728152
               ->forImperialKey('values.females_quare_in', 'in')
               ->forMetricKey('values.female_square_mm', 'mm');
	$definition->addColumn('Type of Lock') //att666325
               ->forKey('values.type_of_lock');
    $definition->addColumn('Overall Length') // att326
               ->forImperialKey('values.overall_length_in', 'in')
               ->forMetricKey('values.overall_length_mm', 'mm');
	$definition->addColumn('Largest Diameter') // att667007
               ->forImperialKey('values.largest diameter_in', 'in')
               ->forMetricKey('values.largest diameter_mm', 'mm');
  }
}
