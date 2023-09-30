<?php

namespace Drupal\commerce_order_customizations;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Provides utility functions for order export-import.
 */
class UtilityOrder {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The SubdivisionRepository class.
   *
   * @var \CommerceGuys\Addressing\Subdivision\SubdivisionRepository
   */
  protected SubdivisionRepository $subdivisionRepository;
  /**
   * The filesystem class.
   *
   * @var \League\Flysystem\Filesystem
   */
  protected Filesystem $filesystem;
  /**
   * The FTP Connection.
   *
   * @var \League\Flysystem\PhpseclibV2\SftpConnectionProvider
   */
  protected SftpConnectionProvider $connection;
  /**
   * The logger class.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->subdivisionRepository = new SubdivisionRepository();
    $this->loggerFactory = $factory;
  }

  /**
   * Return product price.
   */
  public function getItemPrice($oderItemObj) {

    return $oderItemObj->getPurchasedEntity()->getPrice()->getNumber();
  }

  /**
   * Return product sku.
   */
  public function getItemSku($oderItemObj) {

    return $oderItemObj->getPurchasedEntity()->getSku();
  }

  /**
   * Item quantity.
   */
  public function getitemQuantity($oderItemObj) {

    return $oderItemObj->getQuantity();
  }

  /**
   * Order raw total.
   */
  public function getrawTotal($oderItemObj) {

    return (float) $this->getItemPrice($oderItemObj) * (float) $this->getitemQuantity($oderItemObj);
  }

  /**
   * Get the name of the product.
   */
  public function getName($oderItemObj) {
    $product_node = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'product',
      'title' => $oderItemObj->getPurchasedEntity()->getSku(),
    ]);
    $product_node = array_values($product_node);
    return $product_node[0]->field_long_description->value;
  }

  /**
   * Get shipping amount.
   */
  public function getShippingAmmount($orderObj) {

    foreach ($orderObj->getAdjustments() as $adjustment) {
      if ($adjustment->getType() == 'shipping') {
        return $adjustment->getAmount()->getNumber();
      }
    }
  }

  /**
   * Get tax amount.
   */
  public function getTaxAmmount($orderObj) {

    foreach ($orderObj->getAdjustments() as $adjustment) {
      if ($adjustment->getType() == 'custom_adjustment') {
        return $adjustment->getAmount()->getNumber();
      }
    }
  }

  /**
   * Get order total.
   */
  public function getOrderTotal($orderObj) {
    return $orderObj->getTotalPrice()->getNumber();
  }

  /**
   * Get Billing name.
   */
  public function getBillingName($profile) {
    $address = $profile->get('address')->first();
    $billing_name = $address->get('given_name')->getCastedValue() . ' ' . $address->get('family_name')->getCastedValue();
    return $billing_name;
  }

  /**
   * Get City.
   */
  public function getCity($profile) {
    return $profile->get('address')->first()->get('locality')->getCastedValue();
  }

  /**
   * Get State code.
   */
  public function getStateCode($profile) {
    return $profile->get('address')->first()->get('administrative_area')->getCastedValue();
  }

  /**
   * Get State name.
   */
  public function getStateName($profile) {
    $subdivisionRepository = new SubdivisionRepository();
    $state_code = $profile->get('address')->first()->get('administrative_area')->getCastedValue();
    $states_us = $subdivisionRepository->getAll(['US']);
    return $states_us[$state_code]->getName();
  }

  /**
   * Get City.
   */
  public function getPostalCode($profile) {
    return $profile->get('address')->first()->get('postal_code')->getCastedValue();
  }

  /**
   * Get StreetOne.
   */
  public function getAddressLineOne($profile) {

    return $profile->get('address')->first()->get('address_line1')->getCastedValue();
  }

  /**
   * Get StreetTwo.
   */
  public function getAddressLineTwo($profile) {
    return $profile->get('address')->first()->get('address_line2')->getCastedValue();
  }

  /**
   * Get company.
   */
  public function getCompany($profile) {
    return $profile->get('address')->first()->get('organization')->getCastedValue();
  }

  /**
   * Get county.
   */
  public function getCounty($profile) {
    return $profile->get('field_county')->value;
  }

  /**
   * Creates shipments.
   *
   * @param array $orders_details
   *   Array contains order contents.
   */
  public function createShipment($orders_details) {
    $create_shipment_status = 0;
    try {
      $shipment_query = $this->entityTypeManager->getStorage('commerce_shipment')->getQuery();
      $shipment_id_arr = $shipment_query->condition('title', $orders_details['shipment_number'])
        ->condition('field_sap_shipment', 1)
        ->accessCheck(FALSE)
        ->execute();

      if (empty($shipment_id_arr)) {
        $order_query = $this->entityTypeManager->getStorage('commerce_order')->getQuery();
        // It should return single id as order number is unique.
        $order_id_arr = $order_query->condition('order_number', $orders_details['order_number'])
          ->accessCheck(FALSE)
          ->execute();
        $shipment_date = DrupalDateTime::createFromFormat('Ymd', $orders_details['shipment_date']);
        $order_id = array_keys($order_id_arr);
        // Create a shipment entity.
        $shipment = Shipment::create([
        // You can choose the appropriate shipment type.
          'type' => 'default',
          'order_id' => $order_id[0],
          'field_sap_shipment' => 1,
          'field_sap_order_number' => $orders_details['sap_order_number'],
          'field_shipment_date' => $shipment_date->format('Y-m-d'),
          'field_total_item_quantity' => $orders_details['total_quantity'],
          'field_item_and_quantity' => $this->createItemParagraph($orders_details),
          'field_sap_shipment_tracking' => $this->createTrackingParagraph($orders_details),
          'state' => 'draft',
        ]);
        $shipment->setTitle($orders_details['shipment_number']);
        $shipment->save();
        $create_shipment_status = 1;
      }
    }
    catch (Exception $e) {
      $create_shipment_status = 0;
      $this->loggerFactory->get('commerce_order_customizations')->error($e->getMessage());
    }
    return $create_shipment_status;
  }

  /**
   * Creates Item and Quantity paragraph entity.
   */
  public function createItemParagraph($order_info) {
    $item_info = $order_info['item_and_quantity'];
    $paragraph_arr = [];
    $data = [];
    $i = 0;
    foreach ($item_info as $sku => $qty) {
      $product_node = $this->entityTypeManager->getStorage('node')
        ->loadByProperties([
          'type' => 'product',
          'title' => $sku,
        ]);
      $product_node = array_values($product_node);
      $product_name = $product_node[0]->get('field_long_description')->value;
      $paragraph_arr[$i] = Paragraph::create([
        'type' => 'order_item_and_quantity',
        'field_item_name' => $product_name,
        'field_sku' => $sku,
        'field_item_quantity_shipped' => (int) $qty,
      ]);
      $paragraph_arr[$i]->save();
      $data[] = [
        'target_id' => $paragraph_arr[$i]->id(),
        'target_revision_id' => $paragraph_arr[$i]->getRevisionId(),
      ];
      $i = $i + 1;
    }
    return $data;
  }

  /**
   * Creates SAP Shipment Tracking paragraph entity.
   */
  public function createTrackingParagraph($order_info) {
    $item_info = $order_info['carrier_tracking_number'];
    $paragraph_arr = [];
    $data = [];
    $i = 0;
    foreach ($item_info as $item) {
      $linkValue = 'https://www.ups.com/mobile/track?trackingNumber=' . $item;
      // Creating Tracking Number and Tracking Link.
      // Carrier is asumed to be ups always.
      $paragraph_arr[$i] = Paragraph::create([
        'type' => 'sap_shipment_tracking',
        'field_tracking_number' => $item,
        'field_tracking_link' => [
          'uri' => $linkValue,
          'title' => t('Track Your Order'),
        ],
      ]);
      $paragraph_arr[$i]->save();
      $data[] = [
        'target_id' => $paragraph_arr[$i]->id(),
        'target_revision_id' => $paragraph_arr[$i]->getRevisionId(),
      ];
      $i = $i + 1;
    }
    return $data;
  }

  /**
   * We will set order as completed if shipment quantity equals to order quantity.
   */
  public function orderStatusUpdate($orders_details) {
    $order_update_status = 0;
    try {
      $order_number = $orders_details['order_number'];
      // It should return single object.
      $order_obj = $this->entityTypeManager->getStorage('commerce_order')
        ->loadByProperties([
          'order_number' => $order_number,
        ]);
      $order_obj = array_values($order_obj);
      $items = $order_obj[0]->getItems();
      $total_item_quantity = 0;
      foreach ($items as $item) {
        $total_item_quantity = $total_item_quantity + (int) \Drupal::service('commerce_order_customizations.utility')->getitemQuantity($item);
      }
      // Shipment.
      $total_shipment_quantity = 0;
      $shipment_obj_arr = $this->entityTypeManager->getStorage('commerce_shipment')
        ->loadByProperties([
          'order_id' => $order_obj[0]->id(),
          'field_sap_shipment' => 1,
        ]);
      foreach ($shipment_obj_arr as $shipment) {
        $total_shipment_quantity = $total_shipment_quantity + (int) $shipment->get('field_total_item_quantity')->value;
      }
      if ($total_shipment_quantity == $total_item_quantity) {
        $order_obj[0]->set('state', 'completed');
        $order_obj[0]->save();
        // Sending mail if order is delivered.
        // Need to pass order number here.
        $params['message'] = '';
        $this->sendMail('order_complete', $params, $order_obj[0]);
      }
      $order_update_status = 1;
    }
    catch (Exception $e) {
      $order_update_status = 0;
      $this->loggerFactory->get('commerce_order_customizations')->error($e->getMessage());
    }

    return $order_update_status;
  }

  /**
   * Delete files from ftp server.
   */
  public function deleteFtpFiles($file_to_delete, $ftp_folder) {
    $ftp_con = \Drupal::service('commerce_order_customizations.ftpcon');
    try {
      $remoteFiles = $ftp_con->connect()->listContents($ftp_folder)->toArray();
      foreach ($remoteFiles as $file) {
        if ($file['type'] === 'file') {
          // Getting file name.
          $filePath = $file->path();
          $expanded_path = explode('/', $filePath);
          $filename = array_pop($expanded_path);
          if ($filename == $file_to_delete) {
            $ftp_con->connect()->delete($file['path']);
            break;
          }
        }
      }
    }
    catch (Exception $e) {
      // Should send this to mail.
      $this->loggerFactory->get('commerce_order_customizations')->error($e->getMessage());
    }
    $this->loggerFactory->get('commerce_order_customizations')->notice("File: '{$filename}' deleted from FTP folder: '{$ftp_folder}'");
  }

  /**
   * Send mails on failure.
   */
  public function sendMail($key, $params, $order_obj = NULL) {
    // Taxonomy term.
    $term_obj_arr = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => 'custom_email_templates',
        'name' => $key,
      ]);
    $term_obj_arr = array_values($term_obj_arr);
    try {
      if ($order_obj == NULL) {

        $to = $term_obj_arr[0]->get('field_recipients')->value;
        // Exploding to get multiple mails.
        $to = explode(',', $to);
        $params['subject'] = $term_obj_arr[0]->get('field_subject')->value;
        $params['message'] = $term_obj_arr[0]->get('description')->value . PHP_EOL . $params['message'];
      }
      else {
        // For order completion purpose.
        $to = $order_obj->getEmail();
        $params['subject'] = $term_obj_arr[0]->get('field_subject')->value;
        // Replacing placeholder with order number.
        $message = str_replace('[order_number]',$order_obj->getOrderNumber(), $term_obj_arr[0]->get('description')->value);
        $params['message'] = $message;
      }
      // Sending mails.
      $email_factory = \Drupal::service('email_factory');
      $email = $email_factory->newTypedEmail('symfony_mailer', 'custom_mail_notification')
        ->setSubject($params['subject'])
        ->setTo($to)
        ->setBody(['#markup' => $params['message']])
        ->send();
    }
    catch (\Throwable $e) {
      $this->loggerFactory->get('commerce_order_customizations')->error($e->getMessage() . '-' . 'Unable to send mail');
    }

  }

}
