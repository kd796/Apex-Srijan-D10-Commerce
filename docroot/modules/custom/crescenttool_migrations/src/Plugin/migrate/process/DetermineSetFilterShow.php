<?php

namespace Drupal\crescenttool_migrations\Plugin\migrate\process;

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
    $disable_set_filter = ['W1_736078', 'W2_792073', 'W2_800174', 'W2_803455',
      'W2_803456', 'W2_803457', 'W2_792079', 'W2_803454', 'W2_792547',
      'W3_792548', 'W3_792549', 'W3_792550', 'W3_792551', 'W3_792552',
      'W3_803453', 'W2_792546', 'W2_803458', 'W1_22484', 'W2_26534', 'W2_26532',
      'W2_26535', 'W2_26533', 'W2_26531', 'W2_26530'
    ];

    $value = (string) $value;

    // Enable for all categories except those specified above.
    if (!in_array((string) $value, $disable_set_filter)) {
      return 1;
    }

    return 0;
  }

}
