<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class RivetingConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Riveting', 'Nietquetschen']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('River Squeezer Set Shank Dia')
      ->forKey('values.rivet_squeezer_set_shank_dia');
    /**
     * Rivet Squeezer Set Shank Diameter [ATT665662]
     */
    $definition->addColumn('Maximum Force at 6.3 bar/90 PSI')
      ->forKey('values.maximum_force_at_6_3_bar_90_psi');
    $definition->addColumn('Reach')
      ->forKey('values.reach');
    $definition->addNumericColumn('Gap')
      ->forImperialKey('values.gap_inh', 'in')
      ->forMetricKey('values.gap_mmt', 'mm');
    $definition->addNumericColumn('Max Travel')
      ->forImperialKey('values.max_travel_inh', 'in')
      ->forMetricKey('values.max_travel_mmt', 'mm');
    $definition->addNumericColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');
    $definition->addNumericColumn('Hose Length')
      ->forImperialKey('values.hose_length_fot', 'ft')
      ->forMetricKey('values.hose_length_mtr', 'm');
    $definition->addColumn('Riveting Cylinder with Connection Block')
      ->forKey('values.riveting_cylinder_with_connection_block');
    $definition->addColumn('Riveting Cylinder with Manual Control on Cylinder')
      ->forKey('values.riveting_cylinder_with_manual_control_on_cylinder');
    $definition->addNumericColumn('Free Speed')
      ->forKey('values.free_speed')
      ->withUnits('1/min');
    $definition->addColumn('Horsepower Range')
      ->forKey('values.horsepower_range');
    $definition->addColumn('Abrasive Capacity Grinder')
      ->forKey('values.abrasive_capacity_grinder_1');
    $definition->addNumericColumn('Length')
      ->forImperialKey('values.length_in', 'in')
      ->forMetricKey('values.length_mm', 'mm');
    $definition->addNumericColumn('Bore')
      ->forImperialKey('values.bore_in', 'in')
      ->forMetricKey('values.bore_mm', 'mm');
    $definition->addNumericColumn('Blows Per Minute')
      ->forKey('values.blows_per_minute');
    $definition->addNumericColumn('Air Inlet Size')
      ->forKey('values.air_inlet_size_inh')
      ->withUnits('in');
    $definition->addColumn('Accessories')
      ->forKey('values.accessories');
    $definition->addNumericColumn('Capacity Light Alloy')
      ->forImperialKey('values.capacity_light_alloy_inh', 'in')
      ->forMetricKey('values.capacity_light_alloy_mmt', 'mm');
    $definition->addNumericColumn('Capacity Monel')
      ->forImperialKey('values.capacity_monel_inh', 'in')
      ->forMetricKey('values.capacity_monel_mmt', 'mm');
    // $definition->addNumericColumn('Diameter Max')
    //            ->forImperialKey('values.diameter_max_in', 'in')
    //            ->forMetricKey('values.diameter_max_mm', 'mm');
    $definition->addNumericColumn('Diameter Max')
      ->forImperialKey('values.max_diameter_in', 'in')
      ->forMetricKey('values.max_diameter_mm', 'mm');
    $definition->addNumericColumn('Diameter Min')
      ->forImperialKey('values.diameter_min_in', 'in')
      ->forMetricKey('values.diameter_min_mm', 'mm');
    // Added on kevin myhill request to show Body Diameter.
    $definition->addNumericColumn('Body Diameter')
      ->forImperialKey('values.body_dia_in', 'in')
      ->forMetricKey('values.body_dia_mm', 'mm');
    $definition->addColumn('Jaws')
      ->forKey('values.jaws');
    $definition->addNumericColumn('Max Cylinder Stroke')
      ->forImperialKey('values.max_cylinder_stroke_inh', 'in')
      ->forMetricKey('values.max_cylinder_stroke_mmt', 'mm');
    $definition->addNumericColumn('Max Opening')
      ->forImperialKey('values.max_opening_mm_inh', 'in')
      ->forMetricKey('values.max_opening_mm_mmt', 'mm');
    $definition->addNumericColumn('Piston Stroke')
      ->forImperialKey('values.piston_stroke_inh', 'in')
      ->forMetricKey('values.piston_stroke_mmt', 'mm');
    $definition->addColumn('Retainer Type')
      ->forKey('values.retainer_type');
    $definition->addNumericColumn('Rivet Dia.')
      ->forImperialKey('values.rivet_dia_inh', 'in')
      ->forMetricKey('values.rivet_dia_mmt', 'mm');
    $definition->addNumericColumn('Fastener Size')
      ->forImperialKey('values.fastener_size_inh', 'in')
      ->forMetricKey('values.fastener_size_mmt', 'mm');
    $definition->addColumn('Shank')
      ->forKey('values.shank');
    $definition->addNumericColumn('Snap Holder Adj.')
      ->forImperialKey('values.snap_holder_adj_inh', 'in')
      ->forMetricKey('values.snap_holder_adj_mmt', 'mm');
    $definition->addNumericColumn('Speed')
      ->forKey('values.speed_rpm')
      ->withUnits('1/min');
    $definition->addColumn('Used On')
      ->forKey('values.used_on');

  }

}
