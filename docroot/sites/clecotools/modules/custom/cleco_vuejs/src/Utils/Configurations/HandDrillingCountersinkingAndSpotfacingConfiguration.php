<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class HandDrillingCountersinkingAndSpotfacingConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Hand Drilling, Countersinking & Spotfacing', 'Handbohrmaschinen, Bohr- und Senkfräsen']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Input Style')
      ->forKey('values.input_style');
    $definition->addNumericColumn('Shank')
      ->forImperialKey('values.shank_in', 'in')
      ->forMetricKey('values.shank_mm', 'mm');
    $definition->addColumn('Cutter Thread')
      ->forKey('values.cutter_thread');
    $definition->addNumericColumn('Stroke')
      ->forImperialKey('values.stroke_in', 'in')
      ->forMetricKey('values.stroke_mm', 'mm');
    $definition->addColumn('Vacuum')
      ->forKey('values.vacuum');
    $definition->addColumn('Mounting Base Style')
      ->forKey('values.mounting_base_style');
    $definition->addColumn('Window Style')
      ->forKey('values.window_style');
    $definition->addColumn('Nose Insert Style')
      ->forKey('values.nose_insert_style');
    $definition->addNumericColumn('Diameter Max')
      ->forImperialKey('values.diameter_max_in', 'in')
      ->forMetricKey('values.diameter_max_mm', 'mm');
    $definition->addNumericColumn('Max Overal Length')
      ->forImperialKey('values.max_overall_length_in', 'in')
      ->forMetricKey('values.max_overall_length_mm', 'mm');
    $definition->addNumericColumn('Min Overal Length')
      ->forImperialKey('values.min_overall_length_in', 'in')
      ->forMetricKey('values.min_overall_length_mm', 'mm');
    $definition->addNumericColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');

    $definition->addColumn('Termination')
      ->forKey('values.termination');
    $definition->addColumn('Horsepower Range')
      ->forKey('values.horsepower_range');
    $definition->addNumericColumn('Speed')
      ->forKey('values.speed_rpm')
      ->withUnits('1/min');
    $definition->addNumericColumn('Length')
      ->forImperialKey('values.length_in', 'in')
      ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Air Inlet Size')
      ->forKey('values.air_inlet_size_inh')
      ->withUnits('in');
    $definition->addColumn('Chuck Size')
      ->forKey('values.chuck_size');
    $definition->addNumericColumn('Drill Diameter Capacity')
      ->forImperialKey('values.drill_diameter_capacity_inh', 'in')
      ->forMetricKey('values.drill_diameter_capacity_mmt', 'mm');
    $definition->addColumn('Exhaust')
      ->forKey('values.exhaust');
    $definition->addNumericColumn('Height')
      ->forImperialKey('values.height_in', 'in')
      ->forMetricKey('values.height_mm', 'mm');

    // Hand Reamers.
    $definition->addColumn('Dia Required')
      ->forKey('values.dia_required_mm_tolerance')
      ->withUnits('mm/tolerance');
    $definition->addNumericColumn('Number of Flutes')
      ->forKey('values.number_of_flutes');
    $definition->addNumericColumn('Cutter Flute Length')
      ->forImperialKey('values.cutter_flute_length_in', 'in')
      ->forMetricKey('values.cutter_flute_length_mm', 'mm');
    $definition->addColumn('Flutes')
      ->forKey('values.flutes');
    $definition->addColumn('Ground Points')
      ->forKey('values.ground_points');
    $definition->addNumericColumn('Max Diameter')
      ->forImperialKey('values.max_diameter_in', 'in')
      ->forMetricKey('values.max_diameter_mm', 'mm');
    $definition->addNumericColumn('Taper Lead Length')
      ->forKey('values.taper_lead_length_mm')
      ->withUnits('mm');

    // Countersinking.
    $definition->addNumericColumn('Countersinking Angle')
      ->forKey('values.countersinking_angle_degrees_dd')
      ->withUnits('degrees');
    $definition->addColumn('Drill dia')
      ->forImperialKey('values.drill_dia_in', 'in')
      ->forMetricKey('values.drill_dia_mm', 'mm');
    $definition->addColumn('Shank Diameter')
      ->forKey('values.shank_diameter_inh')
      ->withUnits('in');

  }

}
