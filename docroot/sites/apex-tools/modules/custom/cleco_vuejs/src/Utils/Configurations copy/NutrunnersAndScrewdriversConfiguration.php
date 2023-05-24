<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class NutrunnersAndScrewdriversConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Nutrunners & Screwdrivers', 'Schraubwerkzeuge']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {

    $definition->addNumericColumn('Torque Range Min')
      ->forImperialKey('values.torque_range_min_ft_lbs', 'ft/lbs')
      ->forMetricKey('values.torque_range_min_nm', 'Nm');
    $definition->addNumericColumn('Torque Range Max')
      ->forImperialKey('values.torque_range_max_ft_lbs', 'ft/lbs')
      ->forMetricKey('values.torque_range_max_nm', 'Nm');
    $definition->addNumericColumn('Free Speed')
      ->forKey('values.free_speed_rpm')
      ->withUnits('1/min');
    $definition->addNumericColumn('Free Speed (26V)')
      ->forKey('values.free_speed_rpm_26v')
      ->withUnits('1/min');
    $definition->addNumericColumn('Free Speed (44V)')
      ->forKey('values.free_speed_rpm_44v')
      ->withUnits('1/min');
    $definition->addNumericColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');
    $definition->addColumn('Output Drive')
      ->forKey('values.output_drive_in_mm')
      ->withUnits('in|mm');
    $definition->addNumericColumn('Length')
      ->forImperialKey('values.length_in', 'in')
      ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Length (w/26V Battery)')
      ->forImperialKey('values.length_w_26v_battery_in', 'in')
      ->forMetricKey('values.length_w_26v_battery_mm', 'mm');
    $definition->addNumericColumn('Side to Center')
      ->forImperialKey('values.side_to_center_in', 'in')
      ->forMetricKey('values.side_to_center_mm', 'mm');
    $definition->addNumericColumn('Angle Head Height')
      ->forImperialKey('values.angle_head_height_in', 'in')
      ->forMetricKey('values.angle_head_height_mm', 'mm');
    $definition->addNumericColumn('Air Consumption')
      ->forImperialKey('values.air_consumption_at_free_speed_scfm_scfm', 'SCFM')
      ->forMetricKey('values.air_consumption_at_free_speed_l_s', 'l/s');
    $definition->addNumericColumn('Air Inlet Size')
      ->forKey('values.air_inlet_size_inh')
      ->withUnits('in');
    $definition->addNumericColumn('Head Blade Ht “B”')
      ->forKey('values.head_blade_ht_b_in')
      ->withUnits('in');
    $definition->addNumericColumn('Head End/Cntr “D”')
      ->forKey('values.head_end_cntr_d_in')
      ->withUnits('in');
    $definition->addNumericColumn('Head Opening “C”')
      ->forKey('values.head_opening_c_in')
      ->withUnits('in');
    $definition->addNumericColumn('Head Opening Depth D Dim')
      ->forKey('values.head_opening_depth_d_dim_mm')
      ->withUnits('mm');
    $definition->addNumericColumn('Head Opening Width C Dim')
      ->forKey('values.head_opening_width_c_dim_mm')
      ->withUnits('mm');
    $definition->addNumericColumn('Head Thickness B Dim')
      ->forKey('values.head_thickness_b_dim_mm')
      ->withUnits('mm');
    $definition->addNumericColumn('Head Width “A” Dim')
      ->forImperialKey('values.head_width_a_dim_in', 'in')
      ->forMetricKey('values.head_width_a_dim_mm', 'mm');
    $definition->addNumericColumn('Livewire Speed (Vmax)')
      ->forKey('values.livewire_speed_rpm_vmax')
      ->withUnits('1/min');
    $definition->addNumericColumn('Min Hose ID')
      ->forImperialKey('values.min_hose_id_in', 'in')
      ->forMetricKey('values.min_hose_id_mm', 'mm');
    $definition->addNumericColumn('Socket Size')
      ->forImperialKey('values.socket_size_in', 'in')
      ->forMetricKey('values.socket_size_mm', 'mm');
  }

}
