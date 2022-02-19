<?php

namespace Drupal\apex_migrations\Commands;

use Drupal\Core\Config\Config;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\StorageAttributes;

/**
 * A Drush commandfile.
 *
 * Downloads the product data from the SFTP server and runs the import.
 */
class ProductImportService extends DrushCommands {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Brings in the config, pulls in the latest XML file, then runs the import.
   *
   * @command apex:product-import
   * @aliases axpi
   */
  public function productImport() {
    $this->config = \Drupal::config('apex_migrations.settings');
    $this->downloadProducts();
  }

  /**
   * Pulls in the latest XML file from the SFTP server.
   */
  protected function downloadProducts() {
    $this->output()->writeln('Importing products');

    $sftp_host = $this->config->get('sftp_host');
    $sftp_port = $this->config->get('sftp_port');
    $sftp_username = $this->config->get('sftp_username');
    $sftp_password = $this->config->get('sftp_password');
    $sftp_directory = $this->config->get('sftp_directory');
    $newestFileModifiedTimestamp = $this->config->get('newest_file_modified_timestamp');
    $lastDownloadedFilename = $this->config->get('last_downloaded_file_name');

    $sftp_host = '199.115.148.13';
    $sftp_username = 'StiboAcquiaHTUS';
    $sftp_password = 'NRXI37rh';
    $sftp_directory = '/NA GW/Full';

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

      /** @var \League\Flysystem\FileAttributes $file */
      foreach ($allFiles as $file) {
        $name = $file->path();
        $expandedPath = explode('/', $file->path());
        $simpleFilename = array_pop($expandedPath);
        $this->output()->writeln('Inspecting file: ' . $file->path());

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
          $this->config->set('last_downloaded_file_name', $simpleFilename);
          $this->output()->writeln('Created the file: ' . $destination);
          return TRUE;
        }
      }
      else {
        $this->output()->writeln('No file found.');
      }
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
    }

    return FALSE;
  }

  /**
   * Launches the import.
   */
  protected function launchImport() {
    $this->output()->writeln('Launching import');
  }

}
