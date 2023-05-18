<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class ImpactWrenchesConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Impact Wrenches', 'Schlagschrauber']);
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
    $definition->addNumericColumn('Blows Per Minute')
      ->forKey('values.blows_per_minute');
    $definition->addNumericColumn('Free Speed')
      ->forKey('values.free_speed_rpm')
      ->withUnits('1/min');
    $definition->addNumericColumn('Length')
      ->forImperialKey('values.length_in', 'in')
      ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Air Inlet Size')
      ->forKey('values.air_inlet_size_inh')
      ->withUnits('in');
    $definition->addNumericColumn('Min Hose ID')
      ->forImperialKey('values.min_hose_id_in', 'in')
      ->forMetricKey('values.min_hose_id_mm', 'mm');
    $definition->addNumericColumn('Air Consumption')
      ->forImperialKey('values.air_consumption_at_free_speed_scfm_scfm', 'SCFM')
      ->forMetricKey('values.air_consumption_at_free_speed_l_s', 'l/s');
    $definition->addNumericColumn('Breakaway Torque')
      ->forImperialKey('values.breakaway_torque_ft_lbs', 'ft/lbs')
      ->forMetricKey('values.breakaway_torque_nm', 'Nm');
    $definition->addColumn('Drive Size')
      ->forKey('values.drive_size');
    $definition->addColumn('Drive Type')
      ->forKey('values.drive_type');
  }

}
