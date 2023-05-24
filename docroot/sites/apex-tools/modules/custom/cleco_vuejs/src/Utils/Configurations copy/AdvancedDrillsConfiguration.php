<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

/**
 *
 */
class AdvancedDrillsConfiguration implements ComparisonTableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function apply(array $data) {
    return array_intersect($data['product_category'], ['Advanced Drills', 'Halbautomatische Bohrmaschinen']);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ComparisonTableDefinition $definition) {
    $definition->addColumn('Nominal Power')
      ->forImperialKey('values.nominal_power_hp', 'hp')
      ->forMetricKey('values.nominal_power_kw', 'kw');
    $definition->addColumn('Thrust')
      ->forImperialKey('values.thrust_lbs', 'lbs')
      ->forMetricKey('values.thrust_n', 'N');
    $definition->addColumn('Speeds')
      ->forKey('values.speeds')
      ->withUnits('1/min');
    $definition->addColumn('Feed')
      ->forImperialKey('values.feed_inches_rev_ipr', 'inches/rev (ipr)')
      ->forMetricKey('values.feed_mm_rev', 'mm/rev');
    $definition->addColumn('Spindle Attachment')
      ->forKey('values.spindle_attachment');
    $definition->addColumn('Stroke')
      ->forKey('values.stroke');
    $definition->addColumn('Fixturing Options')
      ->forKey('values.fixturing_options');
    $definition->addColumn('Accessories')
      ->forKey('values.accessories');
    $definition->addColumn('Recommended Hose Size ID')
      ->forKey('values.recommended_hose_size_id');
    $definition->addNumericColumn('Air Consumption')
      ->forKey('values.air_consumption');
    $definition->addColumn('Weight')
      ->forImperialKey('values.weight_lbs', 'lbs')
      ->forMetricKey('values.weight_kg', 'kg');
  }

}
