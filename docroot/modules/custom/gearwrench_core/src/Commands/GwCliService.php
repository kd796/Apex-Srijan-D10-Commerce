<?php

namespace Drupal\gearwrench_core\Commands;

use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;

/**
 * Drush version agnostic commands.
 */
class GwCliService extends DrushCommands {

  /**
   * Load the warranty webform submission and upload to FTP.
   *
   * @command gw:warranty-export
   * @aliases gwwe
   */
  public function gwWarrantyExport() {
    // Go get the latest warranty file.
    $dest_directory = 'private://warrant_submissions';
    $filename = 'warranty_replacement_form.xml';

    /** @var \Drupal\Core\File\FileSystem $fileService */
    $fileService = \Drupal::service('file_system');
    $realpath = $fileService->realpath($dest_directory . '/' . $filename);

    if (file_exists($realpath)) {
      $this->output()->writeln('File exists');
      $xml_contents = @file_get_contents($realpath);
    }
    else {
      $this->output()->writeln('File not found man.');
    }

    if (!empty($xml_contents)) {
      $this->output()->writeln('File not empty');

      try {
        // Load up our SFTP connection.
        $filesystem = new Filesystem(new SftpAdapter(
          new SftpConnectionProvider(
            '199.115.148.37',
            'ftpmagento',
            'UbgrBlA3'
          ),
          '/Magento/WarrantyExport/Queue'
        ));

        $dateObj = new \DateTime();
        $new_filename = $dateObj->format('Ymd-His') . '.xml';

        // Push that file.
        $filesystem->write($new_filename, $xml_contents);
        $this->output()->writeln('File has been sent to the server.');
      }
      catch (\Exception $e) {
        echo 'Failure to do something with FTP. Message: ' . $e->getMessage();
      }
    }
    else {
      $this->output()->writeln('Empty file.');
    }
  }

}
