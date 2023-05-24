<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class UniversalJointsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Universal Joints', 'Universalgelenke']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Hub Type') // att675357
               ->forKey('values.hub_type');
	$definition->addColumn('Used On') // att575
	           ->forKey('values.used_on');
	$definition->addColumn('Style') // att27860
	           ->forKey('values.style');
	$definition->addColumn('Cover') // att675562
	           ->forKey('values.cover');
	$definition->addColumn('Front Bore ID') // att675403
	           ->forKey('values.front_bore_id');
	$definition->addColumn('Rear Bore ID') // att675404
	           ->forKey('values.rear_bore_id');
    $definition->addNumericColumn('Outside Diameter (A)')
               ->forImperialKey('values.outside_diameter_a_in', 'in') // att675266
               ->forMetricKey('values.outside_diameter_a_mm', 'mm'); // att675243
	$definition->addColumn('Exposed Hub Length')
               ->forImperialKey('values.exposed_hub_length_in', 'in') // att675368
               ->forMetricKey('values.exposed_hub_length_mm', 'mm'); // att675364
	$definition->addColumn('Max Cover Diameter')
               ->forImperialKey('values.max_cover_diameter_in', 'in') // att675369
               ->forMetricKey('values.max_cover_diameter_mm', 'mm'); // att675365
	$definition->addNumericColumn('Overall Length (B)')
               ->forImperialKey('values.overall_length_b_in', 'in') // att675267
               ->forMetricKey('values.overall_length_b_mm', 'mm'); // att675244
	$definition->addNumericColumn('Bore Depth (C)')
               ->forImperialKey('values.bore_depth_c_in', 'in') // att675269
               ->forMetricKey('values.bore_depth_c_mm', 'mm'); // att675246
	$definition->addNumericColumn('Min Length')
               ->forImperialKey('values.min_length_in', 'in') // att675268
               ->forMetricKey('values.min_length_mm', 'mm'); // att675245
	$definition->addNumericColumn('Bore Diameter (G)')
               ->forImperialKey('values.bore_diameter_g_in', 'in') // att675270
               ->forMetricKey('values.bore_diameter_g_mm', 'mm'); // att675247
	$definition->addNumericColumn('Keyaway Width')
               ->forImperialKey('values.keyaway_width_in', 'in') // att675366
               ->forMetricKey('values.keyaway_width_mm', 'mm'); // att675361
	$definition->addColumn('Keyaway Depth')
               ->forImperialKey('values.keyaway_depth_in', 'in') // att675367
               ->forMetricKey('values.keyaway_depth_mm', 'mm'); // att675362
	$definition->addNumericColumn('Hole Location')
               ->forImperialKey('values.hole_location_in', 'in') // att675271
               ->forMetricKey('values.hole_location_mm', 'mm'); // att675248
	$definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs') // att661955
               ->forMetricKey('values.weight_kg', 'kg'); // att661950
	$definition->addNumericColumn('Torsional Play Test Torque')
               ->forImperialKey('values.torsional_play_test_torque_lbs', 'lbs') // att675273
               ->forMetricKey('values.torsional_play_test_torque_nm', 'Nm'); // att675264
	$definition->addNumericColumn('Maximum Angle') // att675251
	           ->forKey('values.maximum_angle');
	$definition->addNumericColumn('Min. Ultimate Static Torsional Strength')
               ->forImperialKey('values.min_ultimate_static_torsional_strength_lbs', 'lbs') // att675274
               ->forMetricKey('values.min_ultimate_static_torsional_strength_nm', 'Nm'); // att675261
	$definition->addNumericColumn('Min. Ultimate Static Torsional Strength - Apex Avg')
               ->forImperialKey('values.min_ultimate_static_torsional_strength_apex_avg_lbs', 'lbs') // att675275
               ->forMetricKey('values.min_ultimate_static_torsional_strength_apex_avg_nm', 'Nm'); // att675262
	$definition->addColumn('Ultimate Axial Strength')
               ->forImperialKey('values.ultimate_axial_strength_lbs', 'lbs') // att675371
               ->forMetricKey('values.ultimate_axial_strength_nm', 'Nm'); // att675370
	$definition->addColumn('Max Momentary Stall Torque')
               ->forImperialKey('values.max_momentary_stall_torque_lbs', 'lbs') // att675375
               ->forMetricKey('values.max_momentary_stall_torque_nm', 'Nm'); // att675372
	$definition->addColumn('Max Peak Torque')
               ->forImperialKey('values.max_peak_torque_lbs', 'lbs') // att675376
               ->forMetricKey('values.max_peak_torque_nm', 'Nm'); // att675374
	$definition->addNumericColumn('Axial Tension / Compression')
               ->forImperialKey('values.axial_tension_compression_lbs', 'lbs') // att675277
               ->forMetricKey('values.axial_tension_compression_nm', 'Nm'); // att675265
	$definition->addNumericColumn('Endurance Torque Test Angle') // att675251
	           ->forKey('values.endurance_torque_test_angle');
	$definition->addNumericColumn('Endurance Torque Test')
               ->forImperialKey('values.endurance_torque_test_lbs', 'lbs') // att675276
               ->forMetricKey('values.endurance_torque_test_lbs_nm', 'Nm'); // att675263
  }
}
