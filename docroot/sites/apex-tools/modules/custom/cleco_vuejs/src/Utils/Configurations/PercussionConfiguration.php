<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class PercussionConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Percussion', 'Schlagwerkzeuge']);
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
    $definition->addNumericColumn('Chisel')
      ->forKey('values.chisel');
    $definition->addColumn('Exhaust')
      ->forKey('values.exhaust');
    $definition->addColumn('Retainer Type')
      ->forKey('values.retainer_type');
    $definition->addColumn('Tool Configuration')
      ->forKey('values.tool_configuration');
    $definition->addColumn('Tool Type')
      ->forKey('values.tool_type');
  }

}
