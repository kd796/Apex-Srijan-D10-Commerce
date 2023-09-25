<?php

namespace Drupal\commerce_order_customizations;

use Drupal\Core\Config\ConfigFactoryInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;

/**
 * Establish FTP connection.
 */
class FtpConnection {

  /**
   * The sftp config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $sftpConfig;

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
   * The FTP Connection.
   *
   * @var \League\Flysystem\PhpseclibV2\SftpConnectionProvider
   */
  protected SftpConnectionProvider $connection;

  /**
   * Construct object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory;
    $this->sftpConfig = $this->config->get('commerce_order_customizations.settings');
  }

  /**
   * Returns Filesystem object.
   */
  public function connect() {
    $sftp_host = $this->sftpConfig->get('stftp_host');
    $sftp_username = $this->sftpConfig->get('stftp_user');
    $sftp_password = $this->sftpConfig->get('stftp_password');
    $root_folder = $this->sftpConfig->get('stftp_root_order_update');

    $this->connection = new SftpConnectionProvider(
      $sftp_host,
      $sftp_username,
      $sftp_password
    );
    $this->adapter = new SftpAdapter($this->connection, '/');
    // Load up our SFTP connection.
    $this->filesystem = new Filesystem($this->adapter);
    return $this->filesystem;

  }

}
