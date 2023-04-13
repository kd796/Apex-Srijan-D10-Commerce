<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class ControllersAndSoftwareConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Controllers & Software', 'Schraubersteuerungen']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    // $definition->addColumn('Tool Type Compatibility')
    //            ->forKey('values.tool_type_compatibility');
    $definition->addColumn('Tool Compatibility')
      ->forKey('values.tool_compatibility');
    // $definition->addColumn('Control Type')
    //            ->forKey('values.control_type');
    $definition->addColumn('Height')
      ->forImperialKey('values.height_in', 'in')
      ->forMetricKey('values.height_mm', 'mm');
    $definition->addColumn('Depth')
      ->forImperialKey('values.depth_in', 'in')
      ->forMetricKey('values.depth_mm', 'mm');
    $definition->addColumn('Width')
      ->forImperialKey('values.width_in', 'in')
      ->forMetricKey('values.width_mm', 'mm');
    $definition->addColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');
    $definition->addColumn('Database Required')
      ->forKey('values.database_required');
    $definition->addColumn('Minimum Disk Space')
      ->forKey('values.minimum_disk_space');
    $definition->addColumn('Minimum Operating System')
      ->forKey('values.minimum_operating_system');
    $definition->addColumn('Minimum Processor')
      ->forKey('values.minimum_processor');
    $definition->addColumn('Minimum RAM')
      ->forKey('values.minimum_ram');
    $definition->addColumn('Minimum Virtual Memory')
      ->forKey('values.minimum_virtual_memory');
    $definition->addColumn('Price Method')
      ->forKey('values.price_method');
    // $definition->addColumn('Product Family')
    //            ->forKey('values.product_family');
  }

}
