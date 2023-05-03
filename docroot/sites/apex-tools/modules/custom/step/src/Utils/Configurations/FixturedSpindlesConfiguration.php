<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class FixturedSpindlesConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Fixtured Spindles', 'Einbauspindeln']);
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
    $definition->addNumericColumn('Float')
               ->forKey('values.float_mm_mmt')
               ->withUnits('mm');
    $definition->addColumn('Drive Type')
               ->forKey('values.drive_type');
    $definition->addNumericColumn('Min Center to Center')
               ->forKey('values.min_center_tocenter_mm_mmt')
               ->withUnits('mm');
    $definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs')
               ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Length')
               ->forImperialKey('values.length_in', 'in')
               ->forMetricKey('values.length_mm', 'mm');
  }
}
