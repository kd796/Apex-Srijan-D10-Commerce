<?php

namespace Drupal\ecom_migrations\Plugin\migrate\process;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Unpublish discontinued products.
 *
 * @MigrateProcessPlugin(
 *   id = "unpublish_discontinued_products"
 * )
 */
class UnpublishDiscontinued extends ProcessPluginBase {
  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $values = $this->configuration['value'];
    $published = TRUE;
    $sku_value = $row->getSourceProperty('remote_sku_group');
    foreach ($sku_value as $item) {
      $pValue_list[(string) $item->attributes()->AttributeID] = (string) $item[0];
    }

    if (empty($pValue_list['CustomerPrice']) || $pValue_list['CustomerPrice'] == '') {
      $published = FALSE;
    }

    if (empty($values) || empty($value)) {
      return $published;
    }

    // If $value matches any of the configured values, unpublish the node.
    foreach ($values as $val) {
      if ($value !== $val) {
        continue;
      }
      $this->getLogger('ecom_migrations')->notice("Product: @product is unpublished as it is discontinued.", ['@product' => $row->getIdMap()['sourceid1']]);
      $published = FALSE;
    }

    return $published;
  }

}
