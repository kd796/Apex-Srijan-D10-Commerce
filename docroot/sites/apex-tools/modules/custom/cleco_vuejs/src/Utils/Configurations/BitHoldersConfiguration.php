<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category BitHoldersConfiguration.
 */
class BitHoldersConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = ['Bit Holders', 'Bithalter'];
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
    // att666324.
    $definition->addColumn('Square Drive')
      ->forImperialKey('values.square_drive_in', 'in')
      ->forMetricKey('values.square_drive_mm', 'mm');
    // att666316.
    $definition->addColumn('Female Thread')
      ->forKey('values.female_thread');
    // att326.
    $definition->addColumn('Overall Length')
      ->forImperialKey('values.overall_length_in', 'in')
      ->forMetricKey('values.overall_length_mm', 'mm');
    // att666292.
    $definition->addColumn('Overall Diameter')
      ->forImperialKey('values.overall_diameter_in', 'in')
      ->forMetricKey('values.overall_diameter_mm', 'mm');
    // att666300.
    $definition->addColumn('Opening Depth')
      ->forImperialKey('values.opening_depth_in', 'in')
      ->forMetricKey('values.opening_depth_mm', 'mm');
    // att666296.
    $definition->addColumn('Socket Diameter (C)')
      ->forImperialKey('values.socket_diameter_c_in', 'in')
      ->forMetricKey('values.socket_diameter_c_mm', 'mm');
    // att669761.
    $definition->addColumn('Magnetism')
      ->forKey('values.magnetism');
    // att728149 // Missing from Melissa's excel.
    $definition->addColumn('Description')
      ->forKey('values.description');
  }

}
