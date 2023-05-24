<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class CustomSolutionsAndSpecialtyToolsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Custom Solutions & Specialty Tools', 'Kundenspezifische Lösungen & Sonderwerkzeuge']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
	$definition->addColumn('Drive') // att26835
               ->forKey('values.drive');
    $definition->addColumn('Tap Size') // att667001
               ->forKey('values.tap_size');
	$definition->addColumn('Drill Size') // att22562
               ->forKey('values.drill_size');
	$definition->addColumn('Hex Size') // att666136
               ->forImperialKey('values.hex_size_in', 'in')
               ->forMetricKey('values.hex_size_mm', 'mm');
	$definition->addColumn('Male Thread') // att666315
               ->forKey('values.male_thread');
	$definition->addColumn('Male Square Drive') // att728148
               ->forImperialKey('values.male_square_drive_in', 'in')
               ->forMetricKey('values.male_square_drive_mm', 'mm');
	$definition->addColumn('Male Hex Size') // att667000
               ->forImperialKey('values.male_hex_size_in', 'in')
               ->forMetricKey('values.male_hex_size_mm', 'mm');
	$definition->addColumn('Female Square') // att666987
               ->forImperialKey('values.female_square_in', 'in')
               ->forMetricKey('values.female_square_mm', 'mm');
	$definition->addColumn('Square Drive') // att666324
               ->forImperialKey('values.square_drive_in', 'in')
               ->forMetricKey('values.square_drive_mm', 'mm');
	$definition->addColumn('Overall Length') // att326
               ->forImperialKey('values.overall_length_in', 'in')
               ->forMetricKey('values.overall_length_mm', 'mm');
    $definition->addColumn('Socket Length') // att667029
               ->forImperialKey('values.socket_length_in', 'in')
               ->forMetricKey('values.socket_length_mm', 'mm');
	$definition->addColumn('Socket Diameter') // att667030
               ->forImperialKey('values.socket_diameter_in', 'in')
               ->forMetricKey('values.socket_diameter_b_mm', 'mm');
	$definition->addColumn('Broach Depth') // att667033
               ->forImperialKey('values.broach_depth_in', 'in')
               ->forMetricKey('values.broach_depth_mm', 'mm');
	$definition->addColumn('Magnet Depth') // att667036
               ->forImperialKey('values.magnet_depth_in', 'in')
               ->forMetricKey('values.magnet_depth_mm', 'mm');
	$definition->addColumn('Description') // att728154
               ->forKey('values.description_0');
    // @TODO att728154 label can be renamed to "Type of Extention" not to comflict with label of att728149, check "Table Name" below
	/* <Attribute ID="ATT728154" MultiValued="false" ProductMode="Normal" FullTextIndexed="false" ExternallyMaintained="false" Derived="false" Referenced="true" Mandatory="false">
      <Name>Description</Name>
      <Validation BaseType="text" MinValue="" MaxValue="" MaxLength="100" InputMask=""/>
      <DimensionLink DimensionID="Language"/>
      <MetaData>
        <Value AttributeID="Table Name">Type of Extension</Value>
      </MetaData>
      <AttributeGroupLink AttributeGroupID="Table Attribute Group"/>
      <UserTypeLink UserTypeID="SKU"/>
      <UserTypeLink UserTypeID="SKU-Set"/>
    </Attribute> */
  }
}
