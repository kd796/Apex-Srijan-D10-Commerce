<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class LintPickerConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Lint Picker', 'Fusselsammler']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Exhaust')
      ->forKey('values.exhaust');
    $definition->addColumn('Capacity')
      ->forKey('values.capacity');
    $definition->addColumn('Horsepower Range')
      ->forKey('values.horsepower_range');
    $definition->addColumn('Free Speed')
      ->forKey('values.free_speed_rpm')
      ->withUnits('1/min');
    $definition->addColumn('Collet Guard')
      ->forKey('values.collet_guard');
    $definition->addColumn('Tool Termination')
      ->forKey('values.tool_termination');
    $definition->addNumericColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Length')
      ->forImperialKey('values.length_in', 'in')
      ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Air Inlet Size')
      ->forKey('values.air_inlet_size_inh')
      ->withUnits('in');
  }

}
