<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Determine Set Status.
 *
 * @MigrateProcessPlugin(
 *   id = "determine_set_status"
 * )
 */
class DetermineSetStatus extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ((string) $value == 'SKU-Set') {
      return 1;
    }
    else {
      return 0;
    }
  }

}
