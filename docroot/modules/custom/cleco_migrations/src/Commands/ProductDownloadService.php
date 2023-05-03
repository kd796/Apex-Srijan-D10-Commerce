<?php

namespace Drupal\cleco_migrations\Commands;

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
   * @command cleco:products-download
   * @aliases clpd
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function productsDownload(?string $full_or_delta = '', ?int $search_limit = 10, ?string $migration_name = ''): int {
    return 1;
  }

  /**
   * Run migration for Europe English (GB) Language.
   *
   * Brings in the config, pulls in the latest XML file, then runs the import.
   *
   * @command cleco:migrate-gb
   * @aliases clgb
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function migrateGb(): int {
    $this->config = \Drupal::config('apex_migrations.settings');
    $suffix = "_gb";
    $partial_import = 1;

    // Download file for the migration.
    $result = $this->downloadProducts();

    if ($result === 1) {
      return $result;
    }

    // Run all the migration.
    $migration_list = $this->getMigrationList($suffix);
    foreach ($migration_list as $migration_name => $run) {
      if ($run) {
        $result = $this->runProductImport($migration_name, $partial_import);
      }
    }
    return $result;
  }

  /**
   * Run migration for German (DE) Language.
   *
   * Brings in the config, pulls in the latest XML file, then runs the import.
   *
   * @command cleco:migrate-de
   * @aliases clmd
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function migrateDe(): int {
    $this->config = \Drupal::config('apex_migrations.settings');
    $suffix = "_de";
    $partial_import = 1;

    // Download file for the migration.
    $result = $this->downloadProducts($suffix, 'pim_export_with_schema_de.xml');

    if ($result === 1) {
      return $result;
    }

    // Run all the migration.
    $migration_list = $this->getMigrationList($suffix);
    foreach ($migration_list as $migration_name => $run) {
      if ($run) {
        $result = $this->runProductImport($migration_name, $partial_import);
      }
    }
    return $result;
  }

  /**
   * Run migration for North American English (EN) Language.
   *
   * Brings in the config, pulls in the latest XML file, then runs the import.
   *
   * @command cleco:migrate-en
   * @aliases clme
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function migrateEn(): int {
    $this->config = \Drupal::config('apex_migrations.settings');
    $suffix = "";
    $partial_import = 1;

    // Sucess: 0.
    $result = $this->downloadProducts($suffix);
    if ($result === 1) {
      return $result;
    }

    $migration_list = $this->getMigrationList();
    foreach ($migration_list as $migration_name => $run) {
      $result = $this->runProductImport($migration_name, $partial_import);
    }
    return $result;
  }

  /**
   * Process Asset level media processing.
   *
   * @param string $suffix
   *   Suffix used for no english languages.
   *
   * @return array
   *   Returns migration list.
   */
  public function getMigrationList($suffix = '') {
    $migrations = [
      'cleco_product_specifications' => 1,
      'cleco_product_model' => 1,
      'cleco_product_media' => 1,
      'cleco_products' => 1,
      'cleco_products_asset_category' => 1,
    ];
    $migration_list = [];
    foreach ($migrations as $migration => $active) {
      if ($active) {
        $migration_list[$migration . $suffix] = $active;
      }
    }
    return $migration_list;
  }

}
