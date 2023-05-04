<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class ElectricTorqueWrenchesConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Torque Wrenches', 'Elektronische Drehmomentschlüssel']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Table Description')
               ->forKey('values.table_description');
    $definition->addNumericColumn('Torque Range Min')
               ->forImperialKey('values.torque_range_min_ft_lbs', 'ft/lbs')
               ->forMetricKey('values.torque_range_min_nm', 'Nm');
    $definition->addNumericColumn('Torque Range Max')
               ->forImperialKey('values.torque_range_max_ft_lbs', 'ft/lbs')
               ->forMetricKey('values.torque_range_max_nm', 'Nm');
    $definition->addColumn('Output Drive')
               ->forKey('values.output_drive');
   // $definition->addColumn('Output Drive')
   //            ->forKey('values.output_drive_in_mm')
   //            ->withUnits('in|mm');
    $definition->addNumericColumn('Length')
               ->forImperialKey('values.length_in', 'in')
               ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Weight')
               ->forImperialKey('values.weight_lbs', 'lbs')
               ->forMetricKey('values.weight_kg', 'kg');
    $definition->addColumn('Barcode Reader')
               ->forKey('values.barcode_reader');
  }
}
