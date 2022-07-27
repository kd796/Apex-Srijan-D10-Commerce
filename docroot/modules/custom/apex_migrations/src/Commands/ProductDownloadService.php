<?php

namespace Drupal\apex_migrations\Commands;

use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drush\Commands\DrushCommands;
use Drush\Log\LogLevel;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_tools\MigrateExecutable;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\StorageAttributes;

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
   * @param null|string $full_or_delta
   *   Whether this is a full import or a delta import.
   * @param null|int $search_limit
   *   How many files to search through before stopping.
   * @param null|string $migration_name
   *   The name of the migration to run after this is successful.
   *
   * @option FullOrDelta
   *   Optional. Is this the full feed or the delta feed.
   * @option SearchLimit
   *   Optional. How many files you would search through before stopping.
   * @option MigrationName
   *   Optional. The name of the migration to run next.
   *
   * @command apex:products-download
   * @aliases axpd
   */
  public function productsDownload($full_or_delta = '', ?int $search_limit = 10, $migration_name = '') {
    $this->config = \Drupal::config('apex_migrations.settings');
    $result = $this->downloadProducts($full_or_delta, $search_limit);

    // A 0 means success, a 1 means failure, per Drush command silliness.
    if ($result === 0) {
      return $this->runProductImport($migration_name);
    }

    // For failure.
    return 1;
  }

  /**
   * Runs the product import after a successful download.
   *
   * @param string|null $migration_name
   *   The name of the migration to run after this is successful.
   *
   * @return int
   *   Return the response that we give to Drush.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function runProductImport(?string $migration_name = '') {
    if (empty($migration_name)) {
      $migration_name = $this->config->get('migration_name');
    }

    /** @var \Drupal\migrate\Plugin\MigrationPluginManager $manager */
    $manager = \Drupal::service('plugin.manager.migration');

    /** @var \Drupal\migrate\Plugin\Migration $migration */
    $migration = $manager->createInstance($migration_name);

    $migration->getIdMap()->prepareUpdate();
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $result = NULL;

    try {
      // Run the migration.
      $result = $executable->import();

      if ($result == MigrationInterface::RESULT_FAILED) {
        throw new \Exception('Failed to import.');
      }
    }
    catch (\Exception $e) {
      $migration->setStatus(MigrationInterface::STATUS_IDLE);

      $this->output()->writeln('Migration failure: ' . $e->getMessage());
      drush_log(
        'Successfully downloaded a new file. We will continue with the product import.',
        LogLevel::ERROR
      );

      // Failure.
      return 1;
    }

    if ($result == MigrationInterface::RESULT_COMPLETED) {
      drush_log('Successfully ran the product migration.', LogLevel::SUCCESS);

      // Success!
      return 0;
    }

    drush_log('Migration failed with code: ' . $result, LogLevel::ERROR);

    // Failure.
    return 1;
  }

  /**
   * Pulls in the latest XML file from the SFTP server.
   *
   * @param null|string $full_or_delta
   *   Whether this is a full import or a delta import.
   * @param int|null $search_limit
   *   How many files to search through before stopping.
   *
   * @return int
   *   Return a 0 on success and a 1 on failure, per Drush command silliness.
   *
   * @throws \League\Flysystem\FilesystemException
   */
  protected function downloadProducts($full_or_delta = '', ?int $search_limit = 10) {
    $this->output()->writeln('Downloading products');

    if (!empty($full_or_delta) && $full_or_delta === 'full') {
      $sftp_directory = $this->config->get('sftp_root_products_full');
    }
    else {
      $sftp_directory = $this->config->get('sftp_root_products_delta');
    }

    $sftp_host = $this->config->get('sftp_host');
    $sftp_username = $this->config->get('sftp_username');
    $sftp_password = $this->config->get('sftp_password');

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

      $all_files = $filesystem->listContents($sftp_directory)->toArray();
      $all_files = $this->sortByLastModified($all_files);

      /*
       * Thoughts on this:
       *
       * We could store the path to both the Delta and the Full product files
       * then create command options to select which to use.
       *
       * We should also keep a DB record of which product files we have downloaded
       * and imported.
       */

      $file_result = $this->findFileToUse($all_files, $search_limit);

      // If we have a file, download it.
      if (!empty($file_result)) {
        $name = $file_result->path();
        $file_extension = substr($name, -3);
        $temp_destination = $this->downloadFile($file_result, $filesystem);

        if ($temp_destination === FALSE) {
          $this->output()->writeln('There was a problem during the file download process. Unable to continue.');
          return 1;
        }

        // Now we set this as the file the product import uses.
        $destination = (string) _apex_migrations_clear_destination_and_pull_in_new($temp_destination);

        if ($file_extension == 'zip') {
          // Now we have to do some cleanup.
          \Drupal::service('file_system')->delete($temp_destination);
        }

        if (!empty($destination)) {
          $configFactory = \Drupal::service('config.factory');
          $configFactory->getEditable('apex_migrations.settings')->set('last_downloaded_file_name', $name)->save();
          $this->output()->writeln('Created the file: ' . $destination);
          drush_log(
            'Successfully downloaded a new file. We will continue with the product import.',
            LogLevel::SUCCESS
          );

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
   * Download the file to the server filesystem.
   *
   * @param \League\Flysystem\FileAttributes $file
   *   The file object.
   * @param \League\Flysystem\Filesystem $filesystem
   *   The network connection object.
   *
   * @return mixed
   *   The temporary file destination.
   *
   * @throws \League\Flysystem\FilesystemException
   */
  protected function downloadFile(FileAttributes $file, Filesystem $filesystem) {
    try {
      $this->output()->writeln('Downloading file: ' . $file->path());

      /** @var \Drupal\Core\File\FileSystem $drupal_filesystem */
      $drupal_filesystem = \Drupal::service('file_system');

      $name = $file->path();
      $expanded_path = explode('/', $name);
      $simple_filename = array_pop($expanded_path);
      $file_extension = substr($name, -3);
      $file_resource = $filesystem->read($file->path());
      $zip_result = FALSE;
      $found_file = NULL;

      // If we have a zip file then we need to handle it a little different.
      if ($file_extension == 'zip') {
        // Ok, we have to download the file first, then extract it.
        $temp_zip = $drupal_filesystem->saveData($file_resource, 'temporary://' . $simple_filename);
        $temp_zip_realpath = $drupal_filesystem->realpath($temp_zip);
        $this->output()->writeln('Temp Zip name: ' . $temp_zip_realpath);

        if (!is_string($temp_zip_realpath)) {
          throw new \Exception('Problems saving the data for the zip file. Unable to proceed.');
        }

        // Open the zip file in a way we can use.
        $archive = new \ZipArchive();
        $zip_result = $archive->open($temp_zip_realpath);

        // Now lets look for the file in the directory.
        $temp_dir = $drupal_filesystem->getTempDirectory();
        $zip_dir = $temp_dir . '/pim_zip';

        $this->output()->writeln(
          'Now we loaded the temp directory. Path: ' . $zip_dir
        );

        if ($zip_result === TRUE) {
          $archive->extractTo($zip_dir);
          $archive->close();

          // Now we need to read the directory and get the one newest file.
          $dir_listing = $drupal_filesystem->scanDirectory($zip_dir, '/.+\.xml$/i');

          if (empty($dir_listing)) {
            throw new \Exception('Failed to load anything when scanning the temporary directory.');
          }

          $found_file = array_pop($dir_listing);

          if (is_object($found_file)) {
            $file_resource = file_get_contents($found_file->uri);
            $simple_filename = $found_file->filename;
          }
          else {
            throw new \Exception('We did not get a file object back.');
          }
        }
        else {
          switch ($zip_result) {
            case \ZipArchive::ER_EXISTS:
              $error = 'File already exists';
              break;

            case \ZipArchive::ER_INCONS:
              $error = 'Zip archive inconsistent';
              break;

            case \ZipArchive::ER_INVAL:
              $error = 'Invalid argument';
              break;

            case \ZipArchive::ER_MEMORY:
              $error = 'Malloc failure';
              break;

            case \ZipArchive::ER_NOENT:
              $error = 'No such file';
              break;

            case \ZipArchive::ER_NOZIP:
              $error = 'Not a zip archive';
              break;

            case \ZipArchive::ER_OPEN:
              $error = 'Can\'t open file';
              break;

            case \ZipArchive::ER_READ:
              $error = 'Read error';
              break;

            case \ZipArchive::ER_SEEK:
              $error = 'Seek error';
              break;

            default:
              $error = 'Unknown problem';
          }

          throw new \Exception('Unable to open the zip file. Error: ' . $error);
        }
      }

      $this->output()->writeln('Temp File name: ' . $simple_filename);

      $temp_destination = $drupal_filesystem->saveData(
        $file_resource,
        'temporary://' . $simple_filename
      );

      if ($zip_result !== FALSE && $found_file !== NULL) {
        $drupal_filesystem->delete($found_file->uri);
      }

      $this->output()->writeln('Saved to: ' . $temp_destination);
    }
    catch (FilesystemException $e) {
      $this->output()->writeln(
        'File System Exception, error: ' . $e->getMessage()
      );

      return FALSE;
    }
    catch (FileWriteException $e) {
      $this->output()->writeln(
        'File Write Exception, error: ' . $e->getMessage()
      );

      return FALSE;
    }
    catch (FileException $e) {
      $this->output()->writeln(
        'File Exception, error: ' . $e->getMessage()
      );

      return FALSE;
    }
    catch (\Exception $e) {
      $this->output()->writeln(
        'Generic error: ' . $e->getMessage()
      );

      return FALSE;
    }

    return $temp_destination;
  }

  /**
   * Find a file to use.
   *
   * @param \League\Flysystem\DirectoryListing $all_files
   *   The iterable list of files from Flysystem FTP.
   * @param int $search_limit
   *   How many files we search through before we stop.
   *
   * @return null|\League\Flysystem\FileAttributes
   *   The file object to use.
   */
  protected function findFileToUse(DirectoryListing $all_files, $search_limit) {
    $last_downloaded_filename = $this->config->get('last_downloaded_file_name') ?? '';

    $this->output()->writeln(
      'Last downloaded file path: ' . $last_downloaded_filename
    );

    $expanded_file_path = explode('/', $last_downloaded_filename);
    $last_downloaded_filename = array_pop($expanded_file_path);

    $this->output()->writeln(
      'Last downloaded filename: ' . $last_downloaded_filename
    );

    $this->output()->writeln(
      'Found ' . $all_files->getIterator()->count() . ' files/items. Looping through '
      . $search_limit . ' of them.'
    );

    $files_searched_count = 0;

    /** @var \League\Flysystem\FileAttributes $file */
    foreach ($all_files as $file) {
      $name = $file->path();
      $expanded_path = explode('/', $name);
      $simple_filename = array_pop($expanded_path);
      $formatted_timestamp = date('l jS \of F Y h:i:s A', $file->lastModified());

      $this->output()->writeln('Inspecting file: ' . $name);
      $this->output()->writeln('Simple filename: ' . $simple_filename);
      $this->output()->writeln('Timestamp: ' . $formatted_timestamp);

      $file_extension = substr($name, -3);
      $files_searched_count++;

      if ($file->type() == 'file' && $simple_filename != $last_downloaded_filename) {
        if ($file_extension == 'xml' || $file_extension == 'zip') {
          // If this is an XML file then we can directly use it.
          return $file;
        }
      }

      if ($files_searched_count == $search_limit) {
        break;
      }
    }

    return NULL;
  }

  /**
   * Sort the listing by the last modified timestamp. Newest to oldest.
   *
   * @param array|object $listing
   *   The starting DirectoryListing object or an array from it.
   *
   * @return \League\Flysystem\DirectoryListing
   *   The DirectoryListing object.
   */
  protected function sortByLastModified($listing) {
    if (is_object($listing)) {
      $listing = $listing->toArray();
    }

    usort($listing, function (StorageAttributes $a, StorageAttributes $b) {
      return ($a->lastModified() > $b->lastModified()) ? -1 : 1;
    });

    return new DirectoryListing($listing);
  }

  /**
   * Launches the import.
   */
  protected function launchImport() {
    $this->output()->writeln('Launching import');
  }

}
