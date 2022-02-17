<?php

namespace Drupal\apex_migrations\Commands;

use Drupal\Core\Config\Config;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\serialization\Encoder\XmlEncoder;
use Drupal\webform\Entity\WebformSubmission;
use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

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
   */
  public function productImport() {
    $this->config = \Drupal::config('apex_migrations.settings');
    $this->importProducts();
  }

  /**
   * Pulls in the latest XML file from the SFTP server.
   */
  protected function importProducts() {
    $this->logger()->notice('Importing products');

    $sftp_host = $this->config->get('sftp_host');
    $sftp_port = $this->config->get('sftp_port');
    $sftp_username = $this->config->get('sftp_username');
    $sftp_password = $this->config->get('sftp_password');
    $sftp_directory = $this->config->get('sftp_directory');

    $this->output()->writeln('Connecting to host: ' . $sftp_host);
    $this->output()->writeln('Using file root: ' . $sftp_directory);

    try {
      $sftp_connection = new SftpConnectionProvider([
        'host' => $sftp_host,
        'port' => $sftp_port,
        'username' => $sftp_username,
        'password' => $sftp_password,
      ]);

      $sftp_adapter = new SftpAdapter($sftp_connection, $sftp_directory);

      // Load up our SFTP connection.
      $filesystem = new Filesystem($sftp_adapter);
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
    }

  }

}
