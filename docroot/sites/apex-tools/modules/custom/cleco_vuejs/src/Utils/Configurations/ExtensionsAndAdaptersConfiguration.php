<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category ExtensionsAndAdaptersConfiguration.
 */
class ExtensionsAndAdaptersConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = ['Extensions & Adapters', 'Verlängerungen und Adapter'];
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
    // att666986.
    $definition->addColumn('Driver Size')
      ->forKey('values.driver_size_0');
    // att667014.
    $definition->addColumn('Diameter Nose End (B)')
      ->forImperialKey('values.diameter_nose_end_b_in', 'in')
      ->forMetricKey('values.diameter_nose_end_b_mm', 'mm');
    // att667017.
    $definition->addColumn('Diameter Drive End (C)')
      ->forImperialKey('values.diameter_drive_end_c_in', 'in')
      ->forMetricKey('values.diameter_drive_end_c_mm', 'mm');
    // att728148 // Mising from Melissa's excel.
    $definition->addColumn('Male Square Drive')
      ->forImperialKey('values.male_square_drive_in', 'in')
      ->forMetricKey('values.male_square_drive_mm', 'mm');
    // att728152 // Mising from Melissa's excel.
    $definition->addColumn('Female Square')
      ->forImperialKey('values.female_square_in', 'in')
      ->forMetricKey('values.female_square_mm', 'mm');
    // att666325.
    $definition->addColumn('Type of Lock')
      ->forKey('values.type_of_lock');
    // att326.
    $definition->addColumn('Overall Length')
      ->forImperialKey('values.overall_length_in', 'in')
      ->forMetricKey('values.overall_length_mm', 'mm');
    // att667007.
    $definition->addColumn('Largest Diameter')
      ->forImperialKey('values.largest_diameter_in', 'in')
      ->forMetricKey('values.largest_diameter_mm', 'mm');
  }

}
