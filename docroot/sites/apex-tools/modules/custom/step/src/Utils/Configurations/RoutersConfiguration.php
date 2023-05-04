<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class RoutersConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Routers', 'Fräser']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Exhaust')
               ->forKey('values.exhaust');
    $definition->addColumn('Free Speed')
               ->forKey('values.free_speed_rpm')
               ->withUnits('1/min');
    $definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs')
               ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Length')
               ->forImperialKey('values.length_in', 'in')
               ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Air Inlet Size')
               ->forKey('values.air_inlet_size_inh')
               ->withUnits('in');
    $definition->addColumn('Collet Guard')
               ->forKey('values.collet_guard');
    $definition->addColumn('Horsepower Range')
               ->forKey('values.horsepower_range');
    $definition->addColumn('RPM Range')
               ->forKey('values.rmpm_range');
    $definition->addColumn('Shank Diameter')
               ->forKey('values.shank_diameter_inh')
               ->withUnits('in|mm');
    $definition->addColumn('Tool Configuration')
               ->forKey('values.tool_configuration');
    $definition->addColumn('Tool Termination')
               ->forKey('values.tool_termination');
  }
}
