<?php

namespace Drupal\cleco_vuejs\Utils;

/**
 *
 */
interface ComparisonTableConfiguration {

  /**
   * Apply Configuration.
   * Determines whether the configuration should be applied
   *
   * @param array $data
   *   The data object passed to the table definition.
   *
   * @return bool
   */
  public function apply(array $data);

  /**
   * Configure Definition.
   * Apply any configuration to the comparison table definition.
   *
   * @param \Drupal\step\Utils\ComparisonTableDefinition $definition
   *   The
   *   table definition.
   *
   * @return void
   */
  public function configure(ComparisonTableDefinition $definition);

}
