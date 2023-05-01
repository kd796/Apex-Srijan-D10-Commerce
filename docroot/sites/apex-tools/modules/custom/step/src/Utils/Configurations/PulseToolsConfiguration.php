<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class PulseToolsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Pulse Tools', 'Impulsschrauber']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Drive Type')
               ->forKey('values.drive_type');
    $definition->addNumericColumn('Torque Range Min')
               ->forImperialKey('values.torque_range_min_ft_lbs', 'ft/lbs')
               ->forMetricKey('values.torque_range_min_nm', 'Nm');
    $definition->addNumericColumn('Torque Range Max')
               ->forImperialKey('values.torque_range_max_ft_lbs', 'ft/lbs')
               ->forMetricKey('values.torque_range_max_nm', 'Nm');
    $definition->addNumericColumn('Free Speed')
               ->forKey('values.free_speed_rpm')
               ->withUnits('1/min');
    $definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs')
               ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Air Inlet Size')
               ->forKey('values.air_inlet_size_inh')
               ->withUnits('in');
    $definition->addNumericColumn('Air Consumption')
               ->forKey('values.scfm')
               ->withUnits('m3/min');
    $definition->addColumn('Drive Size')
               ->forKey('values.drive_size');
    $definition->addNumericColumn('Hose Inside Diameter')
               ->forKey('values.hose_inside_diameter_inh')
               ->withUnits('in');
    // $definition->addNumericColumn('Shut Off')
    //           ->forKey('values.shut_off');
  }
}
