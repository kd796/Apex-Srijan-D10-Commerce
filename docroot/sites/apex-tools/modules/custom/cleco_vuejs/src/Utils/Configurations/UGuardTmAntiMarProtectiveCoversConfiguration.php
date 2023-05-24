<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class UGuardTmAntiMarProtectiveCoversConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['u-GUARD™ Anti-Mar Protective Covers', 'u-GUARD Anti-Mar Schutzhülsen']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Metal Part Number') // att666969
               ->forKey('values.metal_part_number');
    $definition->addColumn('Drive End Size') // att666970
               ->forImperialKey('values.drive_end_size_in', 'in')
               ->forMetricKey('values.drive_end_size_mm', 'mm');
	$definition->addColumn('Drive Type') // att499
	           ->forKey('values.drive_type');
	$definition->addColumn('Drive End Sex') // att666973
	           ->forKey('values.drive_end_sex');
	$definition->addColumn('U-Guard Part Length') // att666977
               ->forImperialKey('values.u_guard_part_length_in', 'in')
               ->forMetricKey('values.u_guard_part_length_mm', 'mm');
	$definition->addColumn('Socket Type Length') // att669755
	           ->forKey('values.socket_type_length');
	$definition->addColumn('Fastener End Size') // att666981
               ->forImperialKey('values.fastener_end_size_in', 'in')
               ->forMetricKey('values.fastener_end_size_mm', 'mm');
	$definition->addColumn('Fastener End Type') // att666982
	           ->forKey('values.fastener_end_type');
	$definition->addColumn('Fastener End Sex') // att666983
	           ->forKey('values.fastener_end_sex');
	$definition->addColumn('Assembly Features') // att666984
	           ->forKey('values.assembly_features');
  }
}
