<?php

namespace Drupal\commerce_order_customizations;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class BatchService for Batch Process.
 */
class BatchService {
  use StringTranslationTrait;

  /**
   * Batch process callback.
   *
   * @param object $xml_file
   *   File object.
   * @param string $ftp_folder
   *   Ftp folder name.
   * @param object $context
   *   Context for operations.
   */
  public static function processOrderUpdate($xml_file, $ftp_folder, &$context = []) {

    $expanded_path = explode('/', $xml_file->uri);
    $xml_data = file_get_contents($xml_file->uri);
    if ($xml_data != "") {
      // Xml string is Convert into an object.
      $xmlObj = simplexml_load_string($xml_data);
      if ($xmlObj) {
        $orders_details['order_number'] = '';
        $orders_details['sap_order_number'] = '';
        $orders_details['shipment_date'] = '';
        $orders_details['shipment_number'] = '';
        $orders_details['carrier_name'] = '';
        $orders_details['carrier_tracking_number'] = [];
        $orders_details['item_and_quantity'] = [];
        $orders_details['total_quantity'] = 0;
        // order_number.
        foreach ($xmlObj->IDOC->E1EDL20->E1EDL24 as $element) {
          if ((string) $element->E1EDL41->QUALI === '001') {
            $orders_details['order_number'] = (string) $element->E1EDL41->BSTNR;
            break;
          }
        }
        // sap_order_number.
        foreach ($xmlObj->IDOC->E1EDL20->E1EDL24 as $element) {
          if ((string) $element->E1EDL43->QUALF === 'C') {
            $orders_details['sap_order_number'] = (string) $element->E1EDL43->BELNR;
            break;
          }
        }
        // shipment_date.
        foreach ($xmlObj->IDOC->E1EDL20->E1EDT13 as $element) {
          if ((string) $element->QUALF === '006') {
            $orders_details['shipment_date'] = (string) $element->ISDD;
            break;
          }
        }
        // shipment_number.
        $orders_details['shipment_number'] = (string) $xmlObj->IDOC->E1EDL20->VBELN;
        // carrier_name.
        $orders_details['carrier_name'] = (string) $xmlObj->IDOC->E1EDL20->E1EDL28->E1EDL29->ROUTE_BEZ;
        // carrier_tracking_number.
        foreach ($xmlObj->IDOC->E1EDL20->E1EDL18 as $element) {
          if ((string) $element->QUALF === 'PRO') {
            $orders_details['carrier_tracking_number'][] = (string) $element->PARAM;
            break;
          }
        }
        foreach ($xmlObj->IDOC->E1EDL20->E1EDL37 as $element) {
          if ($element->EXIDV2) {
            $orders_details['carrier_tracking_number'][] = trim((string) $element->EXIDV2);
          }
        }
        // material_number(Mostly SKU number)
        // quantity_shipped.
        foreach ($xmlObj->IDOC->E1EDL20->E1EDL24 as $element) {
          $item_sku = trim((string) $element->MATNR);
          $qty_shipped = trim((string) $element->LFIMG);
          $orders_details['item_and_quantity'][$item_sku] = $qty_shipped;
        }
        // Calculate total items.
        foreach ($orders_details['item_and_quantity'] as $sku => $qty) {
          $orders_details['total_quantity'] = $orders_details['total_quantity'] + (int) $qty;
        }
        $create_shipment_status = \Drupal::service('commerce_order_customizations.utility')->createShipment($orders_details);
        if ($create_shipment_status) {
          $order_update_status = \Drupal::service('commerce_order_customizations.utility')->orderStatusUpdate($orders_details);
          if ($order_update_status) {
            \Drupal::logger('commerce_order_customizations')->notice("New shipment created and order is updated for Order Number: '{$orders_details['order_number']}'");
            // Getting Current order id.
            $order_id = \Drupal::service('commerce_order_customizations.utility')->getOrderId($orders_details['order_number']);
            // Sending mail after creation of shipment.
            \Drupal::service('commerce_order_customizations.utility')->postShipmentMail($order_id, $orders_details['order_number']);
            \Drupal::service('commerce_order_customizations.utility')->deleteFtpFiles(array_pop($expanded_path), $ftp_folder);
          }
        }
        else {
          \Drupal::logger('commerce_order_customizations')->notice("No new shipment created for Order Number: '{$orders_details['order_number']}'");
          \Drupal::service('commerce_order_customizations.utility')->deleteFtpFiles(array_pop($expanded_path), $ftp_folder);
        }
      }
      else {
        // Sending mail if XML obj creation fails.
        $file_name = array_pop($expanded_path);
        $params['message'] = t("There were errors to parse the XML file: '{$file_name}'.");
        \Drupal::service('commerce_order_customizations.utility')->sendMail('order_update', $params);
        \Drupal::logger('commerce_order_customizations')->error($params['message']);
      }
    }
    else {
      $file_name = array_pop($expanded_path);
      $params['message'] = t("XML file: '{$file_name}' had no data");
      // Sending mail if remote file has no Data.
      \Drupal::service('commerce_order_customizations.utility')->sendMail('order_update', $params);
      \Drupal::logger('commerce_order_customizations')->error("XML file: '{$file_name}' had no data");
      \Drupal::service('commerce_order_customizations.utility')->deleteFtpFiles(array_pop($expanded_path), $ftp_folder);
    }
  }

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public static function processUserUpdateFinished($success, array $results, array $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {

      \Drupal::logger('commerce_order_customizations')->notice(t('Successfully processed All the files'));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $messenger->addMessage(
        $this->t('An error occurred while processing @operation with arguments : @args',
          [
            '@operation' => $error_operation[0],
            '@args' => print_r($error_operation[0], TRUE),
          ]
        )
      );
    }
  }

}
