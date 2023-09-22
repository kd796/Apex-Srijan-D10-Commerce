<?php

namespace Drupal\commerce_order_customizations\Commands;

use Drupal\commerce_order_customizations\UtilityOrder;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\UnableToConnectToSftpHost;

/**
 * Custom drush command to update stock in every 24 hrs.
 */
class SapOrderUpdate extends DrushCommands {

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
  public function __construct(EntityTypeManagerInterface $eitityManager, ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, LoggerChannelFactoryInterface $factory, UtilityOrder $utilityObj) {
    parent::__construct();

    $this->entityTypeManager = $eitityManager;
    $this->config = $config_factory;
    $this->drupalFileSystem = $file_system;
    $this->loggerFactory = $factory;
    // Own utility service obj.
    $this->utilityObj = $utilityObj;
    $this->sftpConfig = $this->config->get('commerce_order_customizations.settings');
  }

  /**
   * Exports orders to XML and then to FTP.
   *
   * @command ecom:order-update
   * @aliases eou
   */
  public function orderUpdate() {
    $counter = 0;
    $connection_status = $this->loadConfig();
    if ($connection_status != 0) {
      $public_folder_path = 'public://import/order_update_xml_files/';
      $folder_path = $this->drupalFileSystem->realpath($public_folder_path);
      $remoteFiles = $this->filesystem->listContents($this->root);
      // dump(($remoteFiles));
      // dump($this->root);.
      foreach ($remoteFiles as $remoteFile) {

        $this->downloadAndStoreFile($remoteFile['path'], $public_folder_path);
        $counter++;
      }
      $this->logger()->notice('Download and stored phase from FTP Completed');
      $this->logger()->notice("Total number of files processed are: '{$counter}'");
      // Getting order details array after processing the xml files and creating shipments.
      $data = $this->customXmlReader($public_folder_path);
      // Deleting all the files from public folder.
      $this->deleteLocalFiles($folder_path);
    }
    else {
      $this->output()->writeln("Please check the FTP credential form");
    }
  }

  /**
   * Load the needed config for this command and initiate the FTP connection.
   */
  protected function loadConfig() {
    $sftp_host = $this->sftpConfig->get('stftp_host');
    $sftp_username = $this->sftpConfig->get('stftp_user');
    $sftp_password = $this->sftpConfig->get('stftp_password');
    if ($this->sftpConfig->get('stftp_root_order_update')) {
      $this->root = $this->sftpConfig->get('stftp_root_order_update');
    }
    else {
      return 0;
    }

    $this->connection = new SftpConnectionProvider(
      $sftp_host,
      $sftp_username,
      $sftp_password
    );
    $this->adapter = new SftpAdapter($this->connection, '/');
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
   * Download the file from FTP and store it in the local directory.
   *
   * @param string $remotePath
   *   The path to the folder ftp server.
   * @param string $localPath
   *   The local public folder path.
   */
  public function downloadAndStoreFile($remotePath, $localPath) {
    // Defining Public Folder path.
    $expanded_path = explode('/', $remotePath);
    $simple_filename = array_pop($expanded_path);
    $file_resource = $this->filesystem->read($remotePath);
    $this->drupalFileSystem->saveData($file_resource, $localPath . $simple_filename);
    $this->output()->writeln("File '{$remotePath}' successfully downloaded and stored.");
  }

  /**
   * Reading XML files to store relevent data.
   *
   * @param string $localPath
   *   The local public folder path.
   */
  public function customXmlReader($localPath) {

    $xml_files = $this->drupalFileSystem->scanDirectory($localPath, '/\.xml$/');
    $data = [];
    if (!empty($xml_files)) {
      try {
        foreach ($xml_files as $xml_file) {
          $xml_data = file_get_contents($xml_file->uri);
          $xmlObj = simplexml_load_string($xml_data);
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
          $this->createShipment($orders_details);
        }

      }
      catch (\Exception $e) {
        $this->output()->writeln($e->getMessage());
        return [];
      }
      $this->logger()->notice("Reading phase completed.");
    }
    return $data;
  }

  /**
   * Creates shipments.
   *
   * @param array $orders_details
   *   Array contains order contents.
   */
  public function createShipment($orders_details) {
    $flag = 0;
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
        // $order_obj = $this->entityTypeManager->getStorage('commerce_order')->load($order_id);
        // $shipping_profile = $order_obj->get('shipments')->entity->getShippingProfile();
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
        // You can set the initial state as needed.
          'state' => 'draft',
        ]);
        $shipment->setTitle($orders_details['shipment_number']);
        $shipment->save();
      }
    }
    catch (Exception $e) {
      $this->output()->writeln($e->getMessage());
    }
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
      $paragraph_arr[$i] = Paragraph::create([
        'type' => 'order_item_and_quantity',
        'field_item_name' => $sku,
        'field_item_quantity_shipped' => $qty,
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
      $paragraph_arr[$i] = Paragraph::create([
        'type' => 'sap_shipment_tracking',
        'field_tracking_number' => $item,
        'field_tracking_link' => ['uri' => $linkValue],
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
   * Delete all files from a folder.
   *
   * @param string $folder_path
   *   The path to the folder to delete files from.
   */
  public function deleteLocalFiles($folder_path) {
    // Ensure that the provided folder path exists and is a directory.
    if (is_dir($folder_path)) {
      // Get a list of all files in the folder.
      $files = scandir($folder_path);
      // Loop through the files and delete them.
      foreach ($files as $file) {
        // Exclude "." and ".." entries.
        if ($file != "." && $file != "..") {
          $file_path = $folder_path . '/' . $file;
          // Check if the file exists before attempting to delete it.
          if (file_exists($file_path)) {
            // Delete the file.
            unlink($file_path);
            $this->logger()->notice("File '{$file_path}' deleted from local folder.");
          }
        }
      }
    }
  }

}
