<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get ID Attribute.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_get_default_value"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: cleco_get_default_value
 *   source: text
 * @endcode
 */
class GetDefaultValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $default_value = $this->configuration['default_value'] ?? 0;
    $set_default_value = $this->configuration['set_default_value'] ?? 1;

    if (empty($value) && $set_default_value) {
      $value = $default_value;
    }
    return $value;
  }

}
