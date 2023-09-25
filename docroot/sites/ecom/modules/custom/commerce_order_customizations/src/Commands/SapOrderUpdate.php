<?php

namespace Drupal\commerce_order_customizations\Commands;

use Drupal\commerce_order_customizations\UtilityOrder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;
use League\Flysystem\FileAttributes;
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
      $remoteFiles = $this->filesystem->listContents($this->root)->toArray();
      if (is_dir($public_folder_path)) {
        if (count($remoteFiles) != 0) {

          foreach ($remoteFiles as $remoteFile) {

            $this->downloadAndStoreFile($remoteFile, $public_folder_path);
            $counter++;
          }
          $this->logger()->notice('Download and stored phase from FTP Completed');
          $this->logger()->notice("Total number of files downloaded are: '{$counter}'");
          // Getting order details array after processing the xml files and creating shipments.
          $data = $this->customXmlReader($public_folder_path);
          // Deleting all the files from public folder.
          $this->deleteLocalFiles($folder_path);
        }
        else {
          $this->logger()->notice('There are no Files for Order Update at this moment');
        }
      }
      else {
        // Sending mail if Local folder does not exist.
        $params['message'] = t("Folder: '{$public_folder_path}' does not exist");
        $this->utilityObj->sendMail('order_update', $params);
        $this->logger()->error($params['message']);
      }
    }
    else {
      $this->logger()->error("Please check the FTP credential form");
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
      // Sending mail if FTP Connection fails.
      $params['message'] = $e->getMessage();
      $this->utilityObj->sendMail('order_update', $params);
      $this->output()->writeln($e->getMessage());
      return 0;
    }
  }

  /**
   * Download the file from FTP and store it in the local directory.
   *
   * @param \League\Flysystem\FileAttributes $remoteFile
   *   The file object.
   * @param string $localPath
   *   The local public folder path.
   */
  public function downloadAndStoreFile(FileAttributes $remoteFile, $localPath) {
    // Defining Public Folder path.
    $remotePath = $remoteFile->path();
    $expanded_path = explode('/', $remotePath);
    $simple_filename = array_pop($expanded_path);
    $file_resource = $this->filesystem->read($remotePath);
    $this->drupalFileSystem->saveData($file_resource, $localPath . $simple_filename);
    $this->logger()->success("File '{$remotePath}' successfully downloaded and stored.");

  }

  /**
   * Reading XML files to store relevent data.
   *
   * @param string $localPath
   *   The local public folder path.
   */
  public function customXmlReader($localPath) {

    $xml_files = $this->drupalFileSystem->scanDirectory($localPath, '/\.xml$/');
    if (!empty($xml_files)) {
      foreach ($xml_files as $xml_file) {
        $operations[] = [
          '\Drupal\commerce_order_customizations\BatchService::processOrderUpdate',
        [$xml_file, $this->root],
        ];
      }
      batch_set([
        'title' => t('Batch Processing for Order Update.'),
        'operations' => $operations,
        'finished' => '\Drupal\commerce_order_customizations\BatchService::processUserUpdateFinished',
      ]);
      // Start the batch process.
      drush_backend_batch_process();
      $this->logger()->notice("Batch operations end.");

      $this->logger()->success("Shipment Creation and Order Update Completed.");
    }
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
