<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class GrindersConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Grinders', 'Schleifmaschinen']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Collet Size')
               ->forKey('values.collet_size');
    $definition->addColumn('Horsepower Range')
               ->forKey('values.horsepower_range');
    $definition->addColumn('Free Speed')
               ->forKey('values.free_speed_rpm')
               ->withUnits('1/min');
    $definition->addColumn('RPM Range')
               ->forKey('values.rmpm_range');
    $definition->addColumn('Overhose')
               ->forKey('values.overhose');
    $definition->addNumericColumn('Wheel Size')
               ->forImperialKey('values.wheel_size_inh', 'in')
               ->forMetricKey('values.wheel_size_mmt', 'mm');
    $definition->addColumn('Abrasive Capacity')
               ->forKey('values.abrasive_capacity_grinder_1');
    $definition->addColumn('Spindle Size')
               ->forKey('spindle_size');
    $definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs')
               ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Length')
               ->forImperialKey('values.length_in', 'in')
               ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Head Height')
               ->forImperialKey('values.head_height_in', 'in')
               ->forMetricKey('values.head_height_mm', 'mm');
    $definition->addNumericColumn('Air Inlet Size')
               ->forKey('values.air_inlet_size_inh')
               ->withUnits('in');
    $definition->addColumn('Air Consumption at Free Speed')
               ->forImperialKey('values.air_consumption_at_free_speed_scfm_scfm', 'SCFM')
               ->forMetricKey('values.air_consumption_at_free_speed_scfm__s', 'l/s');
    $definition->addColumn('Vibration')
               ->forImperialKey('values.vibration_m_s', 'm/s')
               ->forMetricKey('values.vibration_k', 'k');
    $definition->addColumn('Exhaust')
               ->forKey('values.exhaust');
    $definition->addColumn('Air Consumption')
               ->forKey('values.at_load_scfm_l_s')
               ->withUnits('l/s');
    $definition->addColumn('Collet Guard')
               ->forKey('values.collet_guard');
    $definition->addColumn('Min Hose ID')
               ->forImperialKey('values.min_hose_id_in', 'in')
               ->forMetricKey('values.min_hose_id_mm', 'mm');
    $definition->addColumn('Throttle Type')
               ->forKey('values.throttle_type');
    $definition->addColumn('Tool Configuration')
               ->forKey('values.tool_configuration');
    $definition->addColumn('Tool Termination')
               ->forKey('values.tool_termination');
    $definition->addColumn('Tool Type')
               ->forKey('values.tool_type');
  }
}
