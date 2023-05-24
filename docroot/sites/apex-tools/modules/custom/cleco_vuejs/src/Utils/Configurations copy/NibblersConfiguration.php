<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class NibblersConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Nibblers', 'Knabber']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Horsepower Range')
      ->forKey('values.horsepower_range');
    $definition->addColumn('Exhaust')
      ->forKey('values.exhaust');
    $definition->addColumn('Capacity')
      ->forKey('values.capacity');
    $definition->addNumericColumn('Speed')
      ->forKey('values.speed_spm')
      ->withUnits('SPM');
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
