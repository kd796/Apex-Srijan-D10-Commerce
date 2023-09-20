<?php

namespace Drupal\commerce_order_customizations\Commands;

use Drupal\commerce_order_customizations\UtilityOrder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\serialization\Encoder\XmlEncoder;
use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\UnableToConnectToSftpHost;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Custom drush command to update stock in every 24 hrs.
 */
class SapOrderCreate extends DrushCommands {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config object for the current module.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The FTP Root directory.
   *
   * @var string
   */
  protected string $root;

  /**
   * The FTP Connection.
   *
   * @var \League\Flysystem\PhpseclibV2\SftpConnectionProvider
   */
  protected SftpConnectionProvider $connection;

  /**
   * The FTP Adapter.
   *
   * @var \League\Flysystem\PhpseclibV2\SftpAdapter
   */
  protected SftpAdapter $adapter;

  /**
   * The logger class.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The filesystem class.
   *
   * @var \League\Flysystem\Filesystem
   */
  protected Filesystem $filesystem;

  /**
   * The file_system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $drupalFileSystem;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Drupal\commerce_order_customizations\UtilityOrder definition.
   *
   * @var \Drupal\commerce_order_customizations\UtilityOrder
   */
  protected $utilityObj;
  /**
   * The sftp config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $sftpConfig;

  /**
   * Construct object.
   */
  public function __construct(EntityTypeManagerInterface $eitityManager, ConfigFactoryInterface $config_factory, UtilityOrder $utilityObj) {
    parent::__construct();

    $this->entityTypeManager = $eitityManager;
    $this->config = $config_factory;
    // Own utility service obj.
    $this->utilityObj = $utilityObj;
    $this->sftpConfig = $this->config->get('commerce_order_customizations.settings');
  }

  /**
   * Exports orders to XML and then to FTP.
   *
   * @command ecom:order-export
   * @aliases eoe
   */
  public function orderExport() {
    // Loading config related to sftp connection.
    $connection_status = $this->loadConfig();
    if ($connection_status != 0) {
      // Give order objects which fullfil followings.
      // A.order is not processed previously, B. order status is processing.
      // C. Payement status of the order is completed.
      $eligible_order_obj_arr = $this->getOrderobjects();
      if (!empty($eligible_order_obj_arr)) {
        // Build order items with order id array.
        $order_items_with_oid = $this->buildOrderItems($eligible_order_obj_arr);
        // Build XML array.
        $data = $this->convertSubmissions($eligible_order_obj_arr, $order_items_with_oid);
        // Creates XML file from the above array.
        $xml_content = $this->buildSubmissionXml($data);
        // Upload to FTP Server.
        $status = $this->uploadToWarrantyFtp($xml_content);
        if ($status) {
          $this->updateOrderExportedFlag($eligible_order_obj_arr);
        }
      }
      else {
        $this->output()->writeln('There are no orders to export');
      }
    }
    else {
      $this->output()->writeln("Please check the FTP credential form");
    }
  }

  /**
   * Get the eligible objects.
   */
  public function getOrderobjects() {
    $order_query = $this->entityTypeManager->getStorage('commerce_order')->getQuery();
    $order_ids = $order_query->condition('state', 'processing')
      ->accessCheck(FALSE)
      ->condition('field_order_exported', 0)
      ->execute();
    if (!empty($order_ids)) {
      $order_ids = array_values($order_ids);

      $eligible_order_ids = [];
      foreach ($order_ids as $order_id) {
        $payment = $this->entityTypeManager->getStorage('commerce_payment')->loadByProperties([
          'order_id' => $order_id,
        ]);
        $payment = array_values($payment);
        $state = $payment[0]->getState()->getId();
        if ($state == 'completed') {
          array_push($eligible_order_ids, $order_id);
        }
      }
      if (!empty($eligible_order_ids)) {
        $order_objs = $this->entityTypeManager->getStorage('commerce_order')->loadByProperties([
          'order_id' => $eligible_order_ids,
        ]);
        $order_objs = array_values($order_objs);
        return $order_objs;
      }
      else {
        return [];
      }
    }
    else {
      return [];
    }
  }

  /**
   * Collect the order items corresponding to order id.
   */
  public function buildOrderItems($eligible_order_obj_arr) {
    $order_items = [];
    foreach ($eligible_order_obj_arr as $order_obj) {
      $order_items[$order_obj->id()] = $order_obj->getItems();
    }
    return $order_items;
  }

  /**
   * Creates the xml array.
   */
  protected function convertSubmissions($eligible_order_obj_arr, $order_items_with_oid) {
    $this->output()->writeln('Converting submissions to the clients format...');
    foreach ($eligible_order_obj_arr as $order) {
      // Getting items array.
      // Get order id.
      $id = $order->id();
      // Get the corresponding items.
      $items_obj_array = $order_items_with_oid[$id];
      $items_arr = $this->createItems($items_obj_array);
      $order_arr[] = [
        'websitecode' => 'atg',
        'orderinfo' => $this->getOrderinfoArr($order),
        'items' => $items_arr,
        'shippingamount' => $this->utilityObj->getShippingAmmount($order),
        'taxamount' => $this->utilityObj->getTaxAmmount($order),
        'grandtotal' => $this->utilityObj->getOrderTotal($order),
      ];
    }
    $data_result = ['order' => $order_arr];
    return $data_result;
  }

  /**
   * Creates orderinfo array.
   */
  public function getOrderinfoArr($order_obj) {
    $billing_profile = $order_obj->getBillingProfile();
    $shipping_profile = $order_obj->get('shipments')->entity->getShippingProfile();
    return [
      'orderid' => $order_obj->getOrderNumber(),
      'orderstatus' => 'processing',
      'orderemail' => $order_obj->getEmail(),
      'shippingmethod' => 'tablerate_bestway',
      'billingname' => $this->utilityObj->getBillingName($billing_profile),
      'billingstreet1' => $this->utilityObj->getAddressLineOne($billing_profile),
      'billingstreet2' => $this->utilityObj->getAddressLineTwo($billing_profile),
      'billingcity' => $this->utilityObj->getCity($billing_profile),
      'billingstate' => $this->utilityObj->getStateName($billing_profile),
      'billingcounty' => $this->utilityObj->getCounty($billing_profile),
      'billingstateabbreviation' => $this->utilityObj->getStateCode($billing_profile),
      'billingpostcode' => $this->utilityObj->getPostalCode($billing_profile),
      'billingcountry' => 'US',
      'shippingname' => $this->utilityObj->getBillingName($shipping_profile),
      'shippingname2' => $this->utilityObj->getCompany($shipping_profile),
      'shippingstreet1' => $this->utilityObj->getAddressLineOne($shipping_profile),
      'shippingstreet2' => $this->utilityObj->getAddressLineTwo($shipping_profile),
      'shippingcity' => $this->utilityObj->getCity($shipping_profile),
      'shippingstate' => $this->utilityObj->getStateName($shipping_profile),
      'shippingcounty' => $this->utilityObj->getCounty($shipping_profile),
      'shippingstateabbreviation' => $this->utilityObj->getStateCode($shipping_profile),
      'shippingpostcode' => $this->utilityObj->getPostalCode($shipping_profile),
      'shippingcountry' => 'US',
      'ordercurrencycode' => 'USD',
      'couponcode' => '',
    ];
  }

  /**
   * Creates Item array.
   */
  public function createItems($items_obj_array) {
    foreach ($items_obj_array as $item_obj) {
      $item_arr[] = [
        'sku' => $this->utilityObj->getItemSku($item_obj),
        'name' => $this->utilityObj->getName($item_obj),
        'qty' => $this->utilityObj->getitemQuantity($item_obj),
        'price' => $this->utilityObj->getItemPrice($item_obj),
        'rowtotal' => $this->utilityObj->getrawTotal($item_obj),
        'couponcode' => '',
      ];
    }
    // Wrap the result in an outer array with 'item' key.
    $item_result = ['item' => $item_arr];
    return $item_result;
  }

  /**
   * Uploads the XML file contents to the client's FTP server.
   */
  protected function uploadToWarrantyFtp($xml_contents) {
    if (!empty($xml_contents)) {
      $this->output()->writeln('XML content not empty');

      // Load config values.
      $host = $this->sftpConfig->get('stftp_host');
      $root = $this->sftpConfig->get('stftp_root_order_export');

      $this->output()->writeln('Connecting to host: ' . $host);
      $this->output()->writeln('Using file root: ' . $root);

      try {
        // Test the FTP connection.
        $this->connection->provideConnection();

        $dateObj = new \DateTime();
        // E.g Atg_1201_Orders_20230915092707.xml.
        $new_filename = 'Atg_1201_Orders_' . $dateObj->format('YmdHis') . '.xml';

        // Push that file.
        $this->filesystem->write($new_filename, $xml_contents);
        $this->output()->writeln('XML File has been sent to the server.');
        return 1;
      }
      catch (\Exception $e) {
        throw new \Exception('FTP Failure. Message: ' . $e->getMessage());
      }
    }
    else {
      $this->output()->writeln('Empty content.');
    }
  }

  /**
   * Builds the XML from the xml array.
   */
  protected function buildSubmissionXml($submissions) {
    $this->output()->writeln('Generating XML...');
    $encoder = new XmlEncoder();
    $serializer = new Serializer([new GetSetMethodNormalizer()]);
    $encoder->setSerializer($serializer);

    return $encoder->encode($submissions, 'xml', [
      'xml_root_node_name' => 'orders',
    ]);
  }

  /**
   * Load the needed config for this command and initiate the FTP connection.
   */
  protected function loadConfig() {
    $sftp_host = $this->sftpConfig->get('stftp_host');
    $sftp_username = $this->sftpConfig->get('stftp_user');
    $sftp_password = $this->sftpConfig->get('stftp_password');
    if ($this->sftpConfig->get('stftp_root_order_export')) {
      $this->root = $this->sftpConfig->get('stftp_root_order_export');
    }
    else {
      return 0;
    }

    $this->connection = new SftpConnectionProvider(
      $sftp_host,
      $sftp_username,
      $sftp_password
    );
    $this->adapter = new SftpAdapter($this->connection, $this->root);
    // Load up our SFTP connection.
    $this->filesystem = new Filesystem($this->adapter);
    try {
      $this->connection->provideConnection();
      return 1;
    }
    catch (UnableToConnectToSftpHost $e) {
      $this->output()->writeln($e->getMessage());
      return 0;
    }
    catch (\Exception $e) {
      $this->output()->writeln($e->getMessage());
      return 0;
    }
  }

  /**
   * Marks the order as exported.
   */
  public function updateOrderExportedFlag($eligible_order_obj_arr) {
    foreach ($eligible_order_obj_arr as $order_obj) {
      try {
        $order_obj->get('field_order_exported')->value = 1;
        $order_obj->save();
      }
      catch (\Exception $e) {
        $this->output()->writeln($e->getMessage());
        continue;
      }
    }
  }

}
