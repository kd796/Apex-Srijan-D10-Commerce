<?php

namespace Drupal\apex_migrations;

use Drupal\Core\Config\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\UnableToConnectToSftpHost;

/**
 * ImageFtp class.
 *
 * This class goes out to the client's FTP server, validates that an image exists,
 * then downloads it for use in the import processes.
 */
class ImageFtp {

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
   * The path to the image assets.
   *
   * @var string
   */
  private string $imageDirectory = '/Assets/';

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
   * Gets an image from the FTP server or returns false if it doesn't exist.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   *
   * @return false|string
   *   Returns the image contents or FALSE.
   *
   * @throws \Drupal\apex_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getImage(string $asset_id): bool|string {
    $image_path = $this->buildImagePath($asset_id);

    if ($this->checkFileExists($image_path)) {
      return $this->filesystem->read($image_path);
    }

    throw new ImageNotFoundOnFtpException('image path: ' . $image_path);
  }

  /**
   * Construct the image path to use.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   *
   * @return string
   *   Returns the constructed image path.
   */
  public function buildImagePath(string $asset_id): string {
    return $this->imageDirectory . $asset_id . '.jpg';
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
