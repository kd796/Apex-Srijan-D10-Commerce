<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class ShearsAndScissorsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Shears & Scissors', 'Scheren']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Exhaust')
      ->forKey('values.exhaust');
    $definition->addColumn('Spindle Size')
      ->forKey('spindle_size');
    $definition->addColumn('Horsepower Range')
      ->forKey('values.horsepower_range');
    $definition->addColumn('Abrasive Capacity')
      ->forKey('values.abrasive_capacity_grinder_1');
    $definition->addColumn('Termination')
      ->forKey('values.termination');
    $definition->addColumn('Retainer Type')
      ->forKey('values.retainer_type');
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
