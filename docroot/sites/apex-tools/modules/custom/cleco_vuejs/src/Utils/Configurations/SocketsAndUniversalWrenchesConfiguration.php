<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category SocketsAndUniversalWrenchesConfiguration.
 */
class SocketsAndUniversalWrenchesConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = [
      'Sockets & Universal Wrenches',
      'Steckschlüsseleinsätze & Universalschlüssel',
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
    // att667008.
    $definition->addColumn('Overall Length A')
      ->forImperialKey('values.overall_length_a_in', 'in')
      ->forMetricKey('values.overall_length_a_mm', 'mm');
    // att667014.
    $definition->addColumn('Diameter Nose End (B)')
      ->forImperialKey('values.diameter_nose_end_b_in', 'in')
      ->forMetricKey('values.diameter_nose_end_b_mm', 'mm');
    // att667017.
    $definition->addColumn('Diameter Drive End (C)')
      ->forImperialKey('values.diameter_drive_end_c_in', 'in')
      ->forMetricKey('values.diameter_drive_end_c_mm', 'mm');
    // att666186.
    $definition->addColumn('Socket Length (B)')
      ->forImperialKey('values.socket_length_b_in', 'in')
      ->forMetricKey('values.socket_length_b_mm', 'mm');
    // att666296.
    $definition->addColumn('Socket Diameter (C)')
      ->forImperialKey('values.socket_diameter_c_in', 'in')
      ->forMetricKey('values.socket_diameter_c_mm', 'mm');
    // att666305.
    $definition->addColumn('Clearance Depth (E)')
      ->forImperialKey('values.clearance_depth_e_in', 'in')
      ->forMetricKey('values.clearance_depth_e_mm', 'mm');
    // att728153.
    $definition->addColumn('Opening Depth (D)')
      ->forImperialKey('values.opening_depth_d_in', 'in')
      ->forMetricKey('values.opening_depth_d_mm', 'mm');
  }

}
