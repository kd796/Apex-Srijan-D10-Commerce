<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class SpecialtyToolsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Specialty Tools', 'Sonderwerkzeuge']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addNumericColumn('Bore')
               ->forImperialKey('values.bore_in', 'in')
               ->forMetricKey('values.bore_mm', 'mm');
    $definition->addColumn('Abrasive Capacity')
               ->forKey('values.abrasive_capacity_grinder_1');
    $definition->addColumn('Stroke')
               ->forKey('values.stroke');
    $definition->addNumericColumn('Blows Per Minute')
               ->forKey('values.blows_per_minute');
    $definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs')
               ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Length')
               ->forImperialKey('values.length_in', 'in')
               ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Air Inlet Size')
               ->forKey('values.air_inlet_size_inh')
               ->withUnits('in');
    $definition->addColumn('Horsepower Range')
               ->forKey('values.horsepower_range');
    $definition->addNumericColumn('Chisel')
               ->forKey('values.chisel');
    $definition->addColumn('Exhaust')
               ->forKey('values.exhaust');
    $definition->addNumericColumn('Free Speed')
               ->forKey('values.free_speed_rpm')
               ->withUnits('1/min');
    $definition->addColumn('Collet Guard')
               ->forKey('values.collet_guard');
    $definition->addColumn('Shank Diameter')
               ->forKey('values.shank_diameter_capacity_in_mm_inh')
               ->withUnits('in');
    $definition->addColumn('Retainer Type')
               ->forKey('values.retainer_type');
    $definition->addColumn('Tool Configuration')
               ->forKey('values.tool_configuration');
    $definition->addColumn('Tool Termination')
               ->forKey('values.tool_termination');
    $definition->addColumn('Tool Type')
               ->forKey('values.tool_type');
  }
}
