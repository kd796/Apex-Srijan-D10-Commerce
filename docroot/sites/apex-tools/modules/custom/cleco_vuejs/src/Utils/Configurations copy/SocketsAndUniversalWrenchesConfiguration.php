<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class SocketsAndUniversalWrenchesConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Sockets & Universal Wrenches', 'Steckschlüsseleinsätze & Universalschlüssel']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
	$definition->addColumn('Overall Length A') // att667008
               ->forImperialKey('values.overall_length_a_in', 'in')
               ->forMetricKey('values.overall_length_a_mm', 'mm');
    $definition->addColumn('Diameter Nose End (B)') // att667014
               ->forImperialKey('values.diameter_nose_end_b_in', 'in')
               ->forMetricKey('values.diameter_nose_end_b_mm', 'mm');
	$definition->addColumn('Diameter Drive End (C)') // att667017
               ->forImperialKey('values.diameter_drive_end_c_in', 'in')
               ->forMetricKey('values.diameter_drive_end_c_mm', 'mm');
	$definition->addColumn('Socket Length (B)') // att666186
               ->forImperialKey('values.socket_length_b_in', 'in')
               ->forMetricKey('values.socket_length_b_mm', 'mm');
	$definition->addColumn('Socket Diameter (C)') // att666296
               ->forImperialKey('values.socket_diameter_c_in', 'in')
               ->forMetricKey('values.socket_diameter_c_mm', 'mm');
	$definition->addColumn('Clearance Depth (E)') // att666305
               ->forImperialKey('values.clearance_depth_e_in', 'in')
               ->forMetricKey('values.clearance_depth_e_mm', 'mm');
	$definition->addColumn('Opening Depth (D)') // att667020
               ->forImperialKey('values.opening_depth_d_in', 'in')
               ->forMetricKey('values.opening_depth_d_mm', 'mm');
  }
}
