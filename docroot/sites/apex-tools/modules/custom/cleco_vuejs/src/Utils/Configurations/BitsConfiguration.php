<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;
use Drupal\cleco_vuejs\Utils\ProductsParentChildMapping;

/**
 * Class for product category BitsConfiguration.
 */
class BitsConfiguration implements ComparisonTableConfiguration {
  use ProductsParentChildMapping;

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    $category = ['Bits', 'Screwdriver Bits', 'Schraubendrehereinsätze', 'Drehschraubereinsätze'];
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
    // att339.
    $definition->addColumn('Point Size')
      ->forKey('values.point_size');
    // att666320.
    $definition->addColumn('Recess')
      ->forKey('values.recess');
    // att17319.
    $definition->addColumn('Nominal Size')
      ->forKey('values.nominal_size');
    // att666136.
    $definition->addColumn('Hex Size')
      ->forImperialKey('values.hex_size_in', 'in')
      ->forMetricKey('values.hex_size_mm', 'mm');
    // att728148 // Missing from Melissa's excel.
    $definition->addColumn('Male Square Drive')
      ->forImperialKey('values.male_square_drive_in', 'in')
      ->forMetricKey('values.male_square_drive_mm', 'mm');
    // att835.
    $definition->addColumn('Drive Size')
      ->forKey('values.drive_size');
    // att666310.
    $definition->addColumn('MorTorq Size')
      ->forKey('values.mortorq_size');
    // att533.
    $definition->addColumn('Screw Size')
      ->forKey('values.screw_size');
    // att666309.
    $definition->addColumn('Measurement Across Lobes')
      ->forImperialKey('values.measurement_across_lobes_in', 'in')
      ->forMetricKey('values.measurement_across_lobes_mm', 'mm');
    // att326.
    $definition->addColumn('Overall Length')
      ->forImperialKey('values.overall_length_in', 'in')
      ->forMetricKey('values.overall_length_mm', 'mm');
    // att666293.
    $definition->addColumn('Socket Nose Diameter')
      ->forImperialKey('values.socket_nose_diameter_in', 'in')
      ->forMetricKey('values.socket_nose_diameter_mm', 'mm');
    // att131.
    $definition->addColumn('Blade Thickness')
      ->forImperialKey('values.blade_thickness_in', 'in')
      ->forMetricKey('values.blade_thickness_mm', 'mm');
    // att133.
    $definition->addColumn('Blade Width')
      ->forImperialKey('values.blade_width_in', 'in')
      ->forMetricKey('values.blade_width_mm', 'mm');
    // att666187.
    $definition->addColumn('Turned Length')
      ->forImperialKey('values.turned_length_in', 'in')
      ->forMetricKey('values.turned_length_mm', 'mm');
    // att666190.
    $definition->addColumn('Turned OD (B)')
      ->forImperialKey('values.turned_od_b_in', 'in')
      ->forMetricKey('values.turned_od_b_mm', 'mm');
    // att667125.
    $definition->addColumn('Sleeve Diameter')
      ->forImperialKey('values.sleeve_diameter_in', 'in')
      ->forMetricKey('values.sleeve_diameter_mm', 'mm');
    // att666177.
    $definition->addColumn('Length of Insert')
      ->forImperialKey('values.length_of_insert_in', 'in')
      ->forMetricKey('values.length_of_insert_mm', 'mm');
    // att728147.
    $definition->addColumn('Bit Length (A)')
      ->forImperialKey('values.bit_length_a_in', 'in')
      ->forMetricKey('values.bit_length_a_mm', 'mm');
    // att728149.
    $definition->addColumn('Description')
    // Mising from Melissa's excell.
      ->forKey('values.description');
  }

}
