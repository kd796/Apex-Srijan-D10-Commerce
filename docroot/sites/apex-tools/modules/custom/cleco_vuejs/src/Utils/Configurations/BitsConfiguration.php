<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class BitsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Bits']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Point Size') //att339
               ->forKey('values.point_size');
    $definition->addColumn('Recess') //att666320
               ->forKey('values.recess');
	$definition->addColumn('Nominal Size') //att17319
               ->forKey('values.nominal_size');
	$definition->addColumn('Hex Size') // att666136
               ->forImperialKey('values.hex_size_in', 'in')
               ->forMetricKey('values.hex_size_mm', 'mm');
	$definition->addColumn('Male Square Drive') // att728148
               ->forImperialKey('values.male_square_drive_in', 'in')
               ->forMetricKey('values.male_square_drive_mm', 'mm');
	$definition->addColumn('Drive Size') //att835
               ->forKey('values.drive_size');
	$definition->addColumn('MorTorq Size') // att666310
               ->forKey('values.mortorq_size');
	$definition->addColumn('Screw Size') // att533
               ->forKey('values.screw_size');
	$definition->addColumn('Measurement Across Lobes') // att666309
               ->forImperialKey('values.measurement_across_lobes_in', 'in')
               ->forMetricKey('values.measurement_across_lobes_mm', 'mm');
    $definition->addColumn('Overall Length') // att326
               ->forImperialKey('values.overall_length_in', 'in')
               ->forMetricKey('values.overall_length_mm', 'mm');
	$definition->addColumn('Socket Nose Diameter') // att666293
               ->forImperialKey('values.socket_nose_diameter_in', 'in');
               ->forMetricKey('values.socket_nose_diameter_mm', 'mm');
	$definition->addColumn('Blade Thickness') // att131
               ->forImperialKey('values.blade_thickness_in', 'in');
               ->forMetricKey('values.blade_thickness_mm', 'mm');
	$definition->addColumn('Blade Width') // att666192
               ->forImperialKey('values.blade_width_in', 'in')
               ->forMetricKey('values.blade_width_mm', 'mm');
	$definition->addColumn('Turned Length') // att666187
               ->forImperialKey('values.turned_length_in', 'in')
               ->forMetricKey('values.turned_length_mm', 'mm');
	$definition->addColumn('Turned OD (B)') // att666190
               ->forImperialKey('values.turned_od_b_in', 'in')
               ->forMetricKey('values.turned_od_b_mm', 'mm');
	$definition->addColumn('Sleeve Diameter') // att667125
               ->forImperialKey('values.sleeve_diameter_in', 'in')
               ->forMetricKey('values.sleeve_diameter_mm', 'mm');
	$definition->addColumn('Length of Insert') // att666177
               ->forImperialKey('values.length_of_insert_in', 'in')
               ->forMetricKey('values.length_of_insert_mm', 'mm');
	$definition->addColumn('Bit Length (A)') // att728147
               ->forImperialKey('values.bit_length_a_in', 'in')
               ->forMetricKey('values.bit_length_a_mm', 'mm');
    $definition->addColumn('Description') // att728149
               ->forKey('values.description');
  }
}
