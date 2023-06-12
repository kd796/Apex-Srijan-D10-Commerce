<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class SawsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Saws', 'Säge']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Horsepower Range')
               ->forKey('values.horsepower_range');
    $definition->addColumn('Exhaust')
               ->forKey('values.exhaust');
    $definition->addColumn('Free Speed')
               ->forKey('values.free_speed_rpm')
               ->withUnits('1/min');
    $definition->addColumn('RPM Range')
               ->forKey('values.rmpm_range');
    $definition->addNumericColumn('Speed')
               ->forKey('values.speed_rpm')
               ->withUnits('1/min');
    $definition->addNumericColumn('Saw Blade Capacity')
               ->forImperialKey('values.saw_blade_capacity_in', 'in')
               ->forMetricKey('values.saw_blade_capacity_mm', 'mm');
    $definition->addNumericColumn('Maximum Depth of Cut')
               ->forImperialKey('values.maximum_depth_of_cut_in', 'in')
               ->forMetricKey('values.maximum_depth_of_cut_mm', 'mm');
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
