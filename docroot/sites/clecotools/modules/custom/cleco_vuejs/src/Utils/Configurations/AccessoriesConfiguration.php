<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class AccessoriesConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Accessories', 'Zubehör']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Description')
      ->forKey('values.long_description');
    $definition->addColumn('Input Style')
      ->forKey('values.input_style');
    $definition->addColumn('Cutter Thread')
      ->forKey('values.cutter_thread');
    $definition->addColumn('Mounting Base Style')
      ->forKey('values.mounting_base_style');
    $definition->addColumn('Window Style')
      ->forKey('values.window_style');
    $definition->addColumn('Nose Insert Style')
      ->forKey('values.nose_insert_style');
    $definition->addColumn('Stroke')
      ->forImperialKey('values.stroke_in', 'in');
    $definition->addColumn('Shank')
      ->forImperialKey('values.shank_in', 'in')
      ->forMetricKey('values.shank_mm_mmt', 'mm');
    $definition->addColumn('Diameter Max')
      ->forImperialKey('values.diameter_max_in', 'in')
      ->forMetricKey('values.diameter_max_mm', 'mm');
    $definition->addColumn('Max Overall Length')
      ->forImperialKey('values.max_overall_length_in', 'in')
      ->forMetricKey('values.max_overall_length_mm', 'mm');
    $definition->addColumn('Min Overall Length')
      ->forImperialKey('values.min_overall_length_in', 'in')
      ->forMetricKey('values.min_overall_length_mm', 'mm');
    $definition->addColumn('Weight')
      ->forImperialKey('values.weight_g', 'g');

  }

}
