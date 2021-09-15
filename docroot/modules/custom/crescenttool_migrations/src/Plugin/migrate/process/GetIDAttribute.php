<?php

namespace Drupal\crescenttool_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get ID Attribute.
 *
 * @MigrateProcessPlugin(
 *   id = "get_id_attribute"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_id_attribute
 *   source: text
 * @endcode
 */
class GetIDAttribute extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $value->attributes()->ID;
  }

}
