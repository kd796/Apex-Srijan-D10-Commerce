<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Determine Set Status.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_determine_set_filter_show"
 * )
 */
class DetermineSetFilterShow extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['allowed_categories']) && !array_key_exists('allowed_categories', $this->configuration)) {
      throw new MigrateException('Determine set filter show plugin is missing the allowed categories configuration.');
    }

    $allowed_categories = $this->configuration['allowed_categories'];
    $value = (string) $value;

    // Enable for all categories except those specified above.
    if (in_array((string) $value, $allowed_categories)) {
      return 1;
    }

    return 0;
  }

}
