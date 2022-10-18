<?php

namespace Drupal\apex_migrations\Commands;

use Drupal\apex_migrations\Exceptions\PreviouslyImportedException;
use Drupal\apex_migrations\FileOperations;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
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
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * A Drush commandfile.
 *
 * Downloads the product data from the SFTP server and runs the import.
 * This is a command to run the automated product import process.
 *
 * @todo Consider renaming the product_download and download_product functions as that is confusing.
 *
 * @see ProductCleanupService::cleanup()
 */
class ProductCleanupService extends ProductServices {

  /**
   * Brings in the config, pulls in the latest XML file, then runs the import.
   *
   * @param null|bool $skip_download
   *   Should we skip downloading the file (if we know we already have it)?
   *
   * @option SkipDownload
   *   Optional. Make it skip the file download.
   *
   * @command apex:products-cleanup
   * @aliases axpc
   *
   * @return null|int
   *   The Drush code for success or failure.
   *
   * @throws \League\Flysystem\FilesystemException
   */
  public function cleanup(?bool $skip_download = FALSE): ?int {
    $this->config = \Drupal::config('apex_migrations.settings');

    if ($skip_download === FALSE) {
      $result = $this->downloadProducts('full', 1, TRUE);
    }
    else {
      $result = 0;
    }

    // A 0 means success, a 1 means failure, per Drush command silliness.
    if ($result === 0) {
      try {
        $skus = $this->gatherValidProductSkus();
        $this->output()->writeln("\nActive SKU Count: " . count($skus));

        if (!empty($skus)) {
          $products_to_delete = $this->getProductsWithoutTheseSkus($skus);
          $this->output()->writeln(
            'Products to delete: ' . count($products_to_delete)
          );

          if (!empty($products_to_delete)) {
            $this->deleteProductsInList($products_to_delete);
          }
          else {
            $this->output()->writeln('No products to delete.');
          }
        }
        else {
          $this->output()->writeln('No valid SKUs found.');
        }

        return 0;
      }
      catch (\Exception $e) {
        $this->output()->writeln('Error: ' . $e->getMessage());
      }
    }

    // For failure.
    return 1;
  }

  /**
   * Gather a list of all of the valid SKUs from the full PIM export.
   *
   * @return array|false
   *   SKUs found in the full PIM export that are valid.
   */
  protected function gatherValidProductSkus(): bool|array {
    // Open the standard import file.
    $uri = 'public://import/pim_data/pim_export_with_schema.xml';
    $realpath = \Drupal::service('file_system')->realpath($uri);
    $contents = file_get_contents($realpath);

    $site_sales_org = $this->config->get('site_sales_org');
    $skus = [];

    if (!empty($contents)) {
      // Parse the XML file.
      $xmlObj = simplexml_load_string($contents);

      // Clear memory.
      unset($contents);

      $products = $xmlObj->xpath("//Product[@UserTypeID='SKU-Set']|//Product[@UserTypeID='SKU']");
      $cnt = count($products);

      $this->output()->writeln('Products Found: ' . $cnt);
      $this->output()->writeln('Gathering SKUs...');

      if ($cnt > 0) {
        $progress_bar = new ProgressBar($this->output, $cnt);
        $progress_bar->start();

        // Identify all the products that are valid.
        foreach ($products as $product) {
          $status = (string) $product->xpath(".//*[@AttributeID='SAP_SALES_ORG_STATUS']")[0];
          $sales_org = (string) $product->xpath(".//*[@AttributeID='SalesOrg']")[0];
          $progress_bar->advance();

          // Get all the SKUs we need to then look for products in Drupal.
          if ($status == 'Active' && $sales_org == $site_sales_org) {
            $sku = (string) $product->Name;
            $skus[$sku] = $sku;
          }
        }

        $progress_bar->finish();
      }

      return $skus;
    }

    return FALSE;
  }

  /**
   * Gets all of the products that are not in this SKU list.
   *
   * @param array $skus
   *   The array of SKUs to be looked for in the DB.
   *
   * @return mixed
   *   An array of products we can delete.
   */
  protected function getProductsWithoutTheseSkus(array $skus) {
    // Title is the SKU.
    // SQL: SELECT nid FROM node_field_data WHERE type = 'product' AND title NOT IN ('sku-array').
    $products = \Drupal::entityQuery('node')
      ->condition('type', 'product')
      ->condition('title', $skus, 'NOT IN')
      ->execute();

    return $products;
  }

  /**
   * Delete all the products in this list of entity IDs.
   *
   * @param array $products_to_delete
   *   The array of product entity IDs.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function deleteProductsInList(array $products_to_delete) {
    // Delete these DB products. Log the list of SKUs we are removing.
    $storageHandler = \Drupal::entityTypeManager()->getStorage('node');
    $entities = $storageHandler->loadMultiple($products_to_delete);

    foreach ($entities as $entity) {
      $entity->delete();
    }
  }

}
