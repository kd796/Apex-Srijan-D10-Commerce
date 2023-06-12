<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category CustomSolutionsAndSpecialtyToolsConfiguration.
 */
class CustomSolutionsAndSpecialtyToolsConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = [
      'Custom Solutions & Specialty Tools',
      'Kundenspezifische Lösungen & Sonderwerkzeuge',
    ];
    if (!array_intersect($data['product_category'], $category)) {
      if (is_array($data['product_category'])) {
        $conditionApplied = FALSE;
        if (in_array($data['product_category'][0],
          $this->getChildren($category, $data['product_category']))) {
          $conditionApplied = TRUE;
        }
        return $conditionApplied;
      }
      else {
        return array_intersect($data['product_category'], $this->getChildren($category, $data['product_category']));
      }
    }

    return $category;
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    // att26835.
    $definition->addColumn('Drive')
      ->forKey('values.drive');
    // att667001.
    $definition->addColumn('Tap Size')
      ->forKey('values.tap_size');
    // att22562.
    $definition->addColumn('Drill Size')
      ->forKey('values.drill_size');
    // att666136.
    $definition->addColumn('Hex Size')
      ->forImperialKey('values.hex_size_in', 'in')
      ->forMetricKey('values.hex_size_mm', 'mm');
    // att666315.
    $definition->addColumn('Male Thread')
      ->forKey('values.male_thread');
    // att728148 // Mising from Melissa's excel.
    $definition->addColumn('Male Square Drive')
      ->forImperialKey('values.male_square_drive_in', 'in')
      ->forMetricKey('values.male_square_drive_mm', 'mm');
    // att667000.
    $definition->addColumn('Male Hex Size')
      ->forImperialKey('values.male_hex_size_in', 'in')
      ->forMetricKey('values.male_hex_size_mm', 'mm');
    // att728152 // Mising from Melissa's excel.
    $definition->addColumn('Female Square')
      ->forImperialKey('values.female_square_in', 'in')
      ->forMetricKey('values.female_square_mm', 'mm');
    // att666324.
    $definition->addColumn('Square Drive')
      ->forImperialKey('values.square_drive_in', 'in')
      ->forMetricKey('values.square_drive_mm', 'mm');
    // att326.
    $definition->addColumn('Overall Length')
      ->forImperialKey('values.overall_length_in', 'in')
      ->forMetricKey('values.overall_length_mm', 'mm');
    // att667029.
    $definition->addColumn('Socket Length')
      ->forImperialKey('values.socket_length_in', 'in')
      ->forMetricKey('values.socket_length_mm', 'mm');
    // att667030.
    $definition->addColumn('Socket Diameter')
      ->forImperialKey('values.socket_diameter_in', 'in')
      ->forMetricKey('values.socket_diameter_mm', 'mm');
    // att667033.
    $definition->addColumn('Broach Depth')
      ->forImperialKey('values.broach_depth_in', 'in')
      ->forMetricKey('values.broach_depth_mm', 'mm');
    // att667036.
    $definition->addColumn('Magnet Depth')
      ->forImperialKey('values.magnet_depth_in', 'in')
      ->forMetricKey('values.magnet_depth_mm', 'mm');
    // att728154.
    $definition->addColumn('Description')
    // Missing from Melissa Excel.
      ->forKey('values.description_0');
  }

}
