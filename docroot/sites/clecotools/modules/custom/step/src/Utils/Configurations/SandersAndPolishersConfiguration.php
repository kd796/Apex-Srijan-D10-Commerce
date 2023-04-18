<?php

namespace Drupal\step\Utils\Configurations;

use Drupal\step\Utils\ComparisonTableConfiguration;
use Drupal\step\Utils\ComparisonTableDefinition;

class SandersAndPolishersConfiguration implements ComparisonTableConfiguration
{

    /**
     * {@inheritdoc}
     */
    public function apply(array $data)
    {
        return array_intersect($data['product_category'], ['Sanders & Polishers', 'Schmirgel- und Poliermaschinen']);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ComparisonTableDefinition $definition)
    {
        $definition->addColumn('Horsepower Range')
                   ->forKey('values.horsepower_range');
        $definition->addColumn('Vacuum')
                   ->forKey('values.vacuum');
        $definition->addColumn('Exhaust')
                   ->forKey('values.exhaust');
        $definition->addColumn('Termination')
                   ->forKey('values.termination');
        $definition->addColumn('Free Speed')
                   ->forKey('values.free_speed_rpm')
                   ->withUnits('1/min');
        $definition->addColumn('RPM Range')
                   ->forKey('values.rmpm_range');
        $definition->addColumn('Abrasive Capacity Sander')
                   ->forKey('values.abrasive_capacity_sander_2');
        $definition->addColumn('Abrasive Capacity Grinder')
                   ->forKey('values.abrasive_capacity_grinder_1');
        $definition->addNumericColumn('Weight')
                   ->forImperialKey('values.weight_lbs', 'lbs')
                   ->forMetricKey('values.weight_kg', 'kg');
        $definition->addNumericColumn('Length')
                   ->forImperialKey('values.length_in', 'in')
                   ->forMetricKey('values.length_mm', 'mm');
        $definition->addNumericColumn('Head Height')
                   ->forImperialKey('values.head_height_in', 'in')
                   ->forMetricKey('values.head_height_mm', 'mm');
        $definition->addNumericColumn('Air Inlet Size')
                   ->forKey('values.air_inlet_size_inh')
                   ->withUnits('in');
        $definition->addColumn('Abrasive Type')
                   ->forKey('values.abrasive_type');
        $definition->addColumn('Collet Guard')
                   ->forKey('values.collet_guard');
        $definition->addColumn('Collet Size')
                   ->forKey('values.collet_size');
        $definition->addColumn('Tool Type')
                   ->forKey('values.tool_type');
    }
}
