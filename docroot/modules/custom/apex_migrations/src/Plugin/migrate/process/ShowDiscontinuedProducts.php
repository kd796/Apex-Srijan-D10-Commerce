<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Show discontinued products(Z3, Z5) along with Z2.
 *
 * If the ATT874509 is "Yes" unpublish irrespective of SAP statuses above.
 *
 * CR requested on 24/05/23 On Ticket APS-149.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_show_discontinued_products"
 * )
 */
class ShowDiscontinuedProducts extends ProcessPluginBase {
  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $values = $this->configuration['allowed_values'];
    $published = TRUE;

    if (empty($values) || empty($value)) {
      return $published;
    }

    // Get the Attribute value of ATT874509 and determine
    // whether to keep the product or not.
    if (!empty($value)) {
      $status_att_value = $this->findAttribute($value, 'ATT874509');
      if (!empty($status_att_value) && $status_att_value == 'Yes') {
        $published = FALSE;
        $this->getLogger('apex_migrations')->notice("Product: @product is unpublished as ATT874509 value is Yes.", ['@product' => $row->getIdMap()['sourceid1']]);
        return $published;
      }
      else {
        $published = TRUE;
      }

      // Get the SAP_SALES_ORG_STATUS value.
      $sap_status = $this->findAttributeId($value, 'SAP_SALES_ORG_STATUS');
      // If $sap_status matches any of the configured values, show the product.
      if (in_array($sap_status, $values)) {
        $published = TRUE;
      }
      else {
        $this->getLogger('apex_migrations')->notice("Product: @product is unpublished because its status not in Z2,Z3 or Z5.", ['@product' => $row->getIdMap()['sourceid1']]);
        $published = FALSE;
      }
      return $published;
    }
  }

  /**
   * Find the attribute given.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The XML object.
   * @param string $attribute
   *   The attribute ID.
   *
   * @return string|null
   *   The value we found.
   */
  protected function findAttribute($element, $attribute) {
    $attribute_value = NULL;

    foreach ($element->children() as $child) {
      if ($child->attributes()->AttributeID == $attribute) {
        $attribute_value = (string) $child;
      }
    }
    return $attribute_value;
  }

  /**
   * Get the attribute ID from the value.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The XML object.
   * @param string $attribute
   *   The attribute ID.
   *
   * @return string|null
   *   The ID we found.
   */
  protected function findAttributeId($element, $attribute) {
    $attribute_id = NULL;
    foreach ($element->children() as $child) {
      if ($child->attributes()->AttributeID == $attribute) {
        $attribute_id = $child->attributes()->ID;
      }
    }
    return $attribute_id;
  }

}
