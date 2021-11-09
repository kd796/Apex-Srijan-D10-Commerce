<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Determine Set Status.
 *
 * @MigrateProcessPlugin(
 *   id = "determine_set_filter_show"
 * )
 */
class DetermineSetFilterShow extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $disable_set_filter = ['W1_727497', 'W1_728251', 'W2_736544', 'W2_736540',
      'W2_736541', 'W2_736542', 'W2_736543', 'W2_736547', 'W2_736546',
      'W1_736539', 'W2_736542', 'W2_736540', 'W2_736543', 'W2_736541',
      'W2_736544', 'W2_736547', 'W2_736546', 'W1_15791', 'W2_15804',
      'W2_787570', 'W2_16111'
    ];

    $value = (string) $value;

    // Enable for all categories except those specified above.
    if (!in_array((string) $value, $disable_set_filter)) {
      return 1;
    }

    return 0;
  }

}
