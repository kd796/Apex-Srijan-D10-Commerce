<?php

namespace Drupal\apex_migrations;

use Drupal\Core\Config\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\UnableToConnectToSftpHost;

/**
 * Ftp class.
 *
 * This class goes out to the client's FTP server, validates that an asset exists,
 * then downloads it for use in the import processes.
 */
class Ftp {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

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
   * The filesystem class.
   *
   * @var \League\Flysystem\Filesystem
   */
  protected Filesystem $filesystem;

  /**
   * The path to the assets.
   *
   * @var string
   */
  protected string $assetDirectory = '/Assets/';

  /**
   * Initializes the ftp connection.
   */
  public function __construct() {
    $this->config = \Drupal::config('apex_migrations.settings');

    $sftp_host = $this->config->get('sftp_host');
    $sftp_username = $this->config->get('sftp_username');
    $sftp_password = $this->config->get('sftp_password');

    $this->connection = new SftpConnectionProvider(
      $sftp_host,
      $sftp_username,
      $sftp_password
    );

    $this->adapter = new SftpAdapter($this->connection, '/');

    // Load up our SFTP connection.
    $this->filesystem = new Filesystem($this->adapter);
  }

  /**
   * This tells whether we have a valid connection.
   *
   * @todo Maybe I should change this to push an error message for the import.
   *
   * @return bool
   *   Whether we have a good connection.
   */
  public function hasValidConnection(): bool {
    try {
      $this->connection->provideConnection();
      return TRUE;
    }
    catch (UnableToConnectToSftpHost $e) {
      return FALSE;
    }
  }

  /**
   * Checks to see if the file exists.
   *
   * @param string $image_path
   *   The path to the image on the FTP server.
   *
   * @return bool
   *   Returns TRUE or FALSE.
   *
   * @throws \League\Flysystem\FilesystemException
   */
  public function checkFileExists(string $image_path): bool {
    return $this->filesystem->fileExists($image_path);
  }

}
