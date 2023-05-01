<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;

/**
 * Unpublish discontinued products.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_determine_published_status"
 * )
 */
class DetermineProductPublishStatus extends ProcessPluginBase {
  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $values = $this->configuration['value'];
    $published = TRUE;

    if (empty($values) || empty($value)) {
      return $published;
    }

    // If $value matches any of the configured values, unpublish the node.
    if (in_array($value, $values)) {
      $this->getLogger('apex_migrations')->notice("Product: @product is unpublished as it is discontinued.", ['@product' => $row->getIdMap()['sourceid1']]);
      $published = FALSE;
    }
    return $published;
  }

}
