<?php

namespace Drupal\cleco_vuejs\Utils\Configurations;

use Drupal\cleco_vuejs\Utils\ComparisonTableConfiguration;
use Drupal\cleco_vuejs\Utils\ComparisonTableDefinition;

class BitHoldersConfiguration implements ComparisonTableConfiguration
{

    /**
     * {@inheritdoc}
     */
    public function apply(array $data)
    {
      return array_intersect($data['product_category'], ['Bit Holders', 'Bithalter']);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ComparisonTableDefinition $definition)
    {
      $definition->addColumn('Spindle Types')
        ->forKey('values.bit_holders');
      $definition->addColumn('Square Drive') // att666324
        ->forImperialKey('values.square_drive_in', 'in')
        ->forMetricKey('values.square_drive_mm', 'mm');
      $definition->addColumn('Female Thread') // att666316
        ->forKey('values.female_thread');
      $definition->addColumn('Overall Length') // att326
        ->forImperialKey('values.overall_length_in', 'in')
        ->forMetricKey('values.overall_length_mm', 'mm');
      $definition->addColumn('Overall Diameter') // att666292
        ->forImperialKey('values.overall_diameter_in', 'in')
        ->forMetricKey('values.overall_diameter_mm', 'mm');
      $definition->addColumn('Opening Depth') // att666300
        ->forImperialKey('values.opening_depth_in', 'in')
        ->forMetricKey('values.opening_depth_mm', 'mm');
      $definition->addColumn('Socket Diameter (C)') // att666296
        ->forImperialKey('values.socket_diameter_c_in', 'in')
        ->forMetricKey('values.socket_diameter_c_mm', 'mm');
      $definition->addColumn('Magnetism') // att669761
        ->forKey('values.magnetism');
      $definition->addColumn('Description') // att728149
        ->forKey('values.description');
    }
}
