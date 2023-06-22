<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class AirMotorsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Air Motors', 'Pneumatische Motoren']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Spindle Type')
      ->forKey('values.spindle_type');
    $definition->addNumericColumn('RPM at Max HP')
      ->forKey('values.rpm_at_max_hp')
      ->withUnits('1/min');
    $definition->addNumericColumn('Free Speed')
      ->forKey('values.free_speed')
      ->withUnits('1/min');
    $definition->addNumericColumn('Torque Stall')
      ->forImperialKey('values.torque_stall_ftlbs', 'ft/lbs')
      ->forMetricKey('values.torque_stall_nm', 'Nm');
    $definition->addColumn('Gear Ratio')
      ->forKey('values.gear_ratio');
    $definition->addNumericColumn('Length')
      ->forImperialKey('values.length_inh', 'in')
      ->forMetricKey('values.length_mmt', 'mm');
    $definition->addNumericColumn('Air Consumption')
      ->forImperialKey('values.air_consumption_scfm', 'SCFM')
      ->forMetricKey('values.air_consumption_at_free_speed_m3_min', 'm3/min');
    $definition->addColumn('Control')
      ->forKey('values.control');
    $definition->addColumn('Exhaust')
      ->forKey('values.exhaust');
    $definition->addNumericColumn('Horsepower')
      ->forKey('values.horsepower_hj')
      ->withUnits('hj');
    $definition->addNumericColumn('Length (Pilot)')
      ->forKey('values.length_pilot_inh')
      ->withUnits('in');
    $definition->addColumn('Motor Configuration')
      ->forKey('values.motor_configuration');
    $definition->addColumn('Mount')
      ->forKey('values.mount');
    $definition->addNumericColumn('Weight (Catalog)')
      ->forImperialKey('values.weight_catalog_lbr', 'lbs')
      ->forMetricKey('values.weight_catalog_kgm', 'kg');
  }

}
