<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category UGuardTmAntiMarProtectiveCoversConfiguration.
 */
class UGuardTmAntiMarProtectiveCoversConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = [
      'u-GUARD™ Anti-Mar Protective Covers',
      'u-GUARD Anti-Mar Schutzhülsen',
    ];
    if (!array_intersect($data['product_category'], $category)) {
      if (is_array($data['product_category'])) {
        $condition_applied = FALSE;
        foreach ($data['product_category'] as $child) {
          if (in_array($child, $this->getChildren($category))) {
            $condition_applied = TRUE;
            break;
          }
        }
        return $condition_applied;
      }
      else {
        return array_intersect($data['product_category'], $this->getChildren($category));
      }
    }

    return $category;
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    // Missing from Melissa's excel sheet and xml
    // $definition->addColumn('Metal Part Number') // att666969
    // ->forKey('values.metal_part_number');
    // Missing from Melissa's excel sheet and xml
    // $definition->addColumn('Drive End Size') // att666970
    // ->forImperialKey('values.drive_end_size_in', 'in')
    // ->forMetricKey('values.drive_end_size_mm', 'mm');
    // Missing from Melissa's excel sheet and xml
    // $definition->addColumn('Drive Type') // att499
    // ->forKey('values.drive_type'); //
    // att666973.
    $definition->addColumn('Drive End Sex')
      ->forKey('values.drive_end_sex');
    // att666977.
    $definition->addColumn('Socket Length')
      ->forImperialKey('values.socket_length_in', 'in')
      ->forMetricKey('values.socket_length_mm', 'mm');
    // att669755.
    $definition->addColumn('Socket Type Length')
      ->forKey('values.socket_type_length');
    // att666981.
    $definition->addColumn('Fastener End Size')
      ->forImperialKey('values.fastener_end_size_in', 'in')
      ->forMetricKey('values.fastener_end_size_mm', 'mm');
    // att666982.
    $definition->addColumn('Fastener End Type')
      ->forKey('values.fastener_end_type');
    // att666983.
    $definition->addColumn('Fastener End Sex')
      ->forKey('values.fastener_end_sex');
    // att666984.
    $definition->addColumn('Assembly Features')
      ->forKey('values.assembly_features');
  }

}
