<?php

namespace Drupal\apex_migrations\Commands;

use Drush\Commands\DrushCommands;
use Drush\Log\LogLevel;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;

/**
 * A Drush commandfile.
 *
 * Downloads the product data from the SFTP server and runs the import.
 */
class ProductDownloadService extends DrushCommands {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Brings in the config, pulls in the latest XML file, then runs the import.
   *
   * @command apex:products-download
   * @aliases axpd
   */
  public function productsDownload() {
    $this->config = \Drupal::config('apex_migrations.settings');
    return $this->downloadProducts();
  }

  /**
   * Pulls in the latest XML file from the SFTP server.
   */
  protected function downloadProducts() {
    $this->output()->writeln('Downloading products');

    $sftp_host = $this->config->get('sftp_host');
    $sftp_username = $this->config->get('sftp_username');
    $sftp_password = $this->config->get('sftp_password');
    $sftp_directory = $this->config->get('sftp_directory');
    $lastDownloadedFilename = $this->config->get('last_downloaded_file_name') ?? '';

    $this->output()->writeln('Connecting to host: ' . $sftp_host);
    $this->output()->writeln('Using file root: ' . $sftp_directory);

    try {
      $sftp_connection = new SftpConnectionProvider(
        $sftp_host,
        $sftp_username,
        $sftp_password
      );

      $sftp_adapter = new SftpAdapter($sftp_connection, '/');

      // Load up our SFTP connection.
      $filesystem = new Filesystem($sftp_adapter);

      $allFiles = $filesystem->listContents($sftp_directory)->toArray();
      $simpleFilename = NULL;
      $newestFile = NULL;
      $name = '';

      $this->output()->writeln('Last downloaded file path: ' . $lastDownloadedFilename);
      $expandedFilePath = explode('/', $lastDownloadedFilename);
      $lastDownloadedFilename = array_pop($expandedFilePath);
      $this->output()->writeln('Last downloaded filename: ' . $lastDownloadedFilename);
      $this->output()->writeln('Found ' . count($allFiles) . ' files/items. Looping through them.');

      /** @var \League\Flysystem\FileAttributes $file */
      foreach ($allFiles as $file) {
        $name = $file->path();
        $expandedPath = explode('/', $file->path());
        $simpleFilename = array_pop($expandedPath);
        $this->output()->writeln('Inspecting file: ' . $file->path());
        $this->output()->writeln('Simple filename: ' . $simpleFilename);

        if ($file->type() == 'file'
            && stripos($name, 'xml', -3) !== FALSE
            && $simpleFilename != $lastDownloadedFilename) {
          $newestFile = $file;

          // We only want one file.
          break;
        }
      }

      // If we have a file, download it.
      if ($newestFile) {
        $this->output()->writeln('Downloading file: ' . $newestFile->path());
        $fileStr = $filesystem->read($newestFile->path());

        $size = strlen($fileStr);
        $this->output()->writeln('File size: ' . $size);
        $this->output()->writeln('Temp File name: ' . $simpleFilename);

        $temp_destination = \Drupal::service('file_system')->saveData($fileStr, 'temporary://' . $simpleFilename);
        $this->output()->writeln('Saved to: ' . $temp_destination);

        $destination = (string) _apex_migrations_clear_destination_and_pull_in_new($temp_destination);

        if (!empty($destination)) {
          $configFactory = \Drupal::service('config.factory');
          $configFactory->getEditable('apex_migrations.settings')->set('last_downloaded_file_name', $name)->save();
          $this->output()->writeln('Created the file: ' . $destination);
          drush_log('Successfully downloaded a new file. We will continue with the product import.', LogLevel::SUCCESS);

          // Returning 0 means success.
          return 0;
        }
      }
      else {
        drush_log('No different file found.', LogLevel::ERROR);
      }
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
    }

    // Returning 1 means failure.
    return 1;
  }

  /**
   * Launches the import.
   */
  protected function launchImport() {
    $this->output()->writeln('Launching import');
  }

}
