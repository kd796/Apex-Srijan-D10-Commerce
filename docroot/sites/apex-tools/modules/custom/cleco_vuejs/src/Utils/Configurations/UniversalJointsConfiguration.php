<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category UniversalJointsConfiguration.
 */
class UniversalJointsConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = ['Universal Joints', 'Universalgelenke'];
    if (!array_intersect($data['product_category'], $category)) {
      if (is_array($data['product_category'])) {
        $conditionApplied = FALSE;
        if (in_array($data['product_category'][0],
          $this->getChildren($category, $data['product_category']))) {
          $conditionApplied = TRUE;
        }
        return $conditionApplied;
      }
      else {
        return array_intersect($data['product_category'], $this->getChildren($category, $data['product_category']));
      }
    }

    return $category;
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    // att675357.
    $definition->addColumn('Hub Type')
      ->forKey('values.hub_type');
    // att575.
    $definition->addColumn('Used On')
      ->forKey('values.used_on');
    // att27860.
    $definition->addColumn('Style')
      ->forKey('values.style');
    // att675562.
    $definition->addColumn('Cover')
      ->forKey('values.cover');
    // att675403.
    $definition->addColumn('Front Bore ID')
      ->forKey('values.front_bore_id');
    // att675404.
    $definition->addColumn('Rear Bore ID')
      ->forKey('values.rear_bore_id');
    $definition->addNumericColumn('Outside Diameter (A)')
    // att675266.
      ->forImperialKey('values.outside_diameter_a_in', 'in')
    // att675243.
      ->forMetricKey('values.outside_diameter_a_mm', 'mm');
    $definition->addColumn('Exposed Hub Length')
    // att675368.
      ->forImperialKey('values.exposed_hub_length_in', 'in')
    // att675364.
      ->forMetricKey('values.exposed_hub_length_mm', 'mm');
    $definition->addColumn('Max Cover Diameter')
    // att675369.
      ->forImperialKey('values.max_cover_diameter_in', 'in')
    // att675365.
      ->forMetricKey('values.max_cover_diameter_mm', 'mm');
    $definition->addNumericColumn('Overall Length (B)')
    // att675267.
      ->forImperialKey('values.overall_length_b_in', 'in')
    // att675244.
      ->forMetricKey('values.overall_length_b_mm', 'mm');
    $definition->addNumericColumn('Bore Depth (C)')
    // att675269.
      ->forImperialKey('values.bore_depth_c_in', 'in')
    // att675246.
      ->forMetricKey('values.bore_depth_c_mm', 'mm');
    // Missing from Malissa's excel and xml.
    // $definition->addNumericColumn('Min Length')
    // ->forImperialKey('values.min_length_in', 'in') // att675268
    // ->forMetricKey('values.min_length_mm', 'mm'); // att675245.
    $definition->addNumericColumn('Bore Diameter (G)')
    // att675270.
      ->forImperialKey('values.bore_diameter_g_in', 'in')
    // att675247.
      ->forMetricKey('values.bore_diameter_g_mm', 'mm');
    $definition->addNumericColumn('Keyaway Width')
    // att675366.
      ->forImperialKey('values.keyaway_width_in', 'in')
    // att675361.
      ->forMetricKey('values.keyaway_width_mm', 'mm');
    $definition->addColumn('Keyaway Depth')
    // att675367.
      ->forImperialKey('values.keyaway_depth_in', 'in')
    // att675362.
      ->forMetricKey('values.keyaway_depth_mm', 'mm');
    // Below both att changed.
    $definition->addNumericColumn('Hole Location')
    // att728095.
      ->forImperialKey('values.hole_location_in', 'in')
    // att728098.
      ->forMetricKey('values.hole_location_mm', 'mm');
    // Missing from Malissa's excel sheet.
    $definition->addNumericColumn('Weight')
    // att661955.
      ->forImperialKey('values.weight_lbs', 'lbs');
    $definition->addNumericColumn('Torsional Play Test Torque')
    // att675273.
      ->forImperialKey('values.torsional_play_test_torque_lbs', 'lbs')
    // att675264.
      ->forMetricKey('values.torsional_play_test_torque_nm', 'Nm');
    // att675251.
    $definition->addNumericColumn('Maximum Angle')
      ->forKey('values.maximum_angle');
    $definition->addNumericColumn('Min. Ultimate Static Torsional Strength')
    // att675274.
      ->forImperialKey('values.min_ultimate_static_torsional_strength_lbs', 'lbs')
    // att675261.
      ->forMetricKey('values.min_ultimate_static_torsional_strength_nm', 'Nm');
    $definition->addNumericColumn('Min. Ultimate Static Torsional Strength - Apex Avg')
    // att675275.
      ->forImperialKey('values.min_ultimate_static_torsional_strength_apex_avg_lbs', 'lbs')
    // att675262.
      ->forMetricKey('values.min_ultimate_static_torsional_strength_apex_avg_nm', 'Nm');
    $definition->addColumn('Ultimate Axial Strength')
    // att675371.
      ->forImperialKey('values.ultimate_axial_strength_lbs', 'lbs')
    // att675370.
      ->forMetricKey('values.ultimate_axial_strength_nm', 'Nm');
    $definition->addColumn('Max Momentary Stall Torque')
    // att675375.
      ->forImperialKey('values.max_momentary_stall_torque_lbs', 'lbs')
    // att675372.
      ->forMetricKey('values.max_momentary_stall_torque_nm', 'Nm');
    $definition->addColumn('Max Peak Torque')
    // att675376.
      ->forImperialKey('values.max_peak_torque_lbs', 'lbs')
    // att675374.
      ->forMetricKey('values.max_peak_torque_nm', 'Nm');
    $definition->addNumericColumn('Axial Tension / Compression')
    // att675277.
      ->forImperialKey('values.axial_tension_compression_lbs', 'lbs')
    // att675265.
      ->forMetricKey('values.axial_tension_compression_nm', 'Nm');
    // att675252.
    $definition->addNumericColumn('Endurance Torque Test Angle')
      ->forKey('values.endurance_torque_test_angle');
    $definition->addNumericColumn('Endurance Torque Test')
    // att675276.
      ->forImperialKey('values.endurance_torque_test_lbs', 'lbs')
    // att675263.
      ->forMetricKey('values.endurance_torque_test_lbs_nm', 'Nm');
  }

}
