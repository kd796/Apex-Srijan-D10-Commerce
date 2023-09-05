<?php

namespace Drupal\commerce_custom_stock\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\UnableToConnectToSftpHost;

/**
 * Custom drush command to update stock in every 24 hrs.
 */
class StockUpdateService extends DrushCommands {

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
   * Initializes the ftp connection.
   */
  public function __construct(EntityTypeManagerInterface $eitityManager, ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, LoggerChannelFactoryInterface $factory) {
    parent::__construct();

    $this->entityTypeManager = $eitityManager;
    $this->config = $config_factory;
    $this->drupalFileSystem = $file_system;
    $this->loggerFactory = $factory;
    $sftp_config = $this->config->get('commerce_custom_stock.settings');

    $sftp_host = $sftp_config->get('stftp_host');
    $sftp_username = $sftp_config->get('stftp_user');
    $sftp_password = $sftp_config->get('stftp_password');
    $this->root = $sftp_config->get('stftp_root');

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

    }
    catch (UnableToConnectToSftpHost $e) {
      $this->output()->writeln($e->getMessage());
      return;
    }
    catch (\Exception $e) {
      $this->output()->writeln($e->getMessage());
      return;
    }
  }

  /**
   * Handle all the operations related to inventory.
   *
   * @command ecom:inventory-update
   * @aliases eiu
   */
  public function inventoryManagement() {

    $counter = 0;
    $public_folder_path = 'public://import/inventory_xml_files/';
    $folder_path = $this->drupalFileSystem->realpath($public_folder_path);
    $remoteFiles = $this->filesystem->listContents($this->root);

    foreach ($remoteFiles as $remoteFile) {
      $this->downloadAndStoreFile($remoteFile['path'], $public_folder_path);
      $counter++;
    }
    $this->output()->writeln('Download and stored phase from FTP Completed');
    $this->output()->writeln("Total number of files processed are: '{$counter}'");
    // Getting data after processing the xml files.
    $data = $this->customXmlReader($public_folder_path);
    // Updating the stock field in product variation.
    if (!empty($data)) {
      $this->updatestock($data);
    }
    // Deleting all the files from public folder.
    $this->deleteLocalFiles($folder_path);
    // Deleting all the files from FTP folder.
    $this->deleteFtpFiles();
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
      foreach ($xml_files as $xml_file) {
        $xml_data = file_get_contents($xml_file->uri);
        $xmlObj = simplexml_load_string($xml_data);

        foreach ($xmlObj->IDOC->E1MARAM as $element) {
          $sku = trim((string) $element->MATNR);
          $qty = trim((string) $element->E1MARCM->EISBE);
          $data[$sku] = $qty;
        }
      }
      $this->output()->writeln("Reading phase completed.");
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
            $this->output()->writeln("File '{$file_path}' deleted from local folder.");
          }
        }
      }
    }
  }

  /**
   * Update product stock field's value.
   *
   * @param array $products
   *   Array contains sku and quantity pair.
   */
  public function updatestock($products) {

    foreach ($products as $sku => $qty) {
      $product_node_arr = $this->entityTypeManager->getStorage('node')
        ->loadByProperties([
          'type' => 'product',
          'title' => $sku,
          'status' => '1',
        ]);
      if (!empty($product_node_arr)) {
        $product_node_arr = array_values($product_node_arr);
        $node = $product_node_arr[0];
        if ($node->field_commerce_product != NULL) {
          $prod_variation_obj = $node->field_commerce_product->entity->variations->entity;
        }
        if ($prod_variation_obj) {
          $prod_variation_obj->field_stock->value = $qty;
          $prod_variation_obj->save();
        }
      }
      else {
        $this->output()->writeln("Product '$sku' is unpublished or not present");
        $this->loggerFactory->get('commerce_custom_stock')->notice("Product '$sku' is unpublished or not present");
      }

    }
    $this->output()->writeln("Product update phase completed");
  }

  /**
   * Delete files from ftp server.
   */
  public function deleteFtpFiles() {
    $folder_loc = $this->root;
    try {
      $remoteFiles = $this->filesystem->listContents($folder_loc);
      foreach ($remoteFiles as $file) {

        if ($file['type'] === 'file') {
          // Delete the file.
          $this->filesystem->delete($file['path']);
          $this->output()->writeln("File '{$file['path']}' deleted successfully.");
        }
      }
    }
    catch (Exception $e) {
      $this->output()->writeln($e->getMessage());
    }
  }

}
