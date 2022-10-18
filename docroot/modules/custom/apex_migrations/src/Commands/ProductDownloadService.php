<?php

namespace Drupal\apex_migrations\Commands;

/**
 * A Drush commandfile.
 *
 * Downloads the product data from the SFTP server and runs the import.
 * This is a command to run the automated product import process.
 *
 * @todo Consider renaming the product_download and download_product functions as that is confusing.
 *
 * @see ProductDownloadService::productsDownload()
 */
class ProductDownloadService extends ProductServices {

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
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function productsDownload(?string $full_or_delta = '', ?int $search_limit = 10, ?string $migration_name = ''): int {
    $this->config = \Drupal::config('apex_migrations.settings');
    $result = $this->downloadProducts($full_or_delta, $search_limit);

    // A 0 means success, a 1 means failure, per Drush command silliness.
    if ($result === 0) {
      return $this->runProductImport($migration_name);
    }

    // For failure.
    return 1;
  }

}
