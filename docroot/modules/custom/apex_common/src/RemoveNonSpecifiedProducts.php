<?php

namespace Drupal\apex_common;

use Drupal\Core\Batch\BatchBuilder;

/**
 * Class RemoveNonSpecifiedProducts handles the import of URL.
 */
class RemoveNonSpecifiedProducts {

  /**
   * List of status messages to be output in screen.
   *
   * @var array
   */
  public static $messages = [];

  /**
   * Execute parsing and saving of redirects.
   *
   * @param mixed $file
   *   Drupal file object.
   */
  public static function triggerBatch($file) {
    // Parse the CSV file into a readable array.
    $csv_data = self::read($file);
    if (empty($csv_data)) {
      return;
    }
    else {
      $batchBuilder = new BatchBuilder();
      $batchId = 1;
      $nids = \Drupal::entityQuery('node')->condition('type', 'product')->execute();

      foreach ($nids as $key => $nid) {
        $batchBuilder->addOperation('\Drupal\apex_common\RemoveNonSpecifiedProducts::DeleteObsoleteProducts', [
          $nid, $csv_data,
        ]);
        $batchId++;
      }

      $batchBuilder
        ->setTitle('Removing Non-Specified Products...')
        ->setFinishCallback([
          RemoveNonSpecifiedProducts::class, 'importFinished',
        ])
        ->setErrorMessage(t('Batch has encountered an error'));
      batch_set($batchBuilder->toArray());
    }
  }

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public static function importFinished($success, array $results, array $operations) {
    if ($success) {
      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed...
      \Drupal::messenger()->addMessage(t('Processed @count items successfully.', [
        '@count' => count($results),
      ]));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      \Drupal::messenger()->addError(t('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]));
    }
  }

  /**
   * Convert CSV file into readable PHP array.
   *
   * @param mixed $file
   *   A Drupal file object.
   */
  protected static function read($file) {
    $filepath = \Drupal::service('file_system')->realpath($file->getFileUri());
    if (!$f = fopen($filepath, 'r')) {
      return ['success' => FALSE, 'message' => [t('Unable to read the file')]];
    }
    $line_no = 0;
    $data = [];
    while ($line = fgetcsv($f, 0)) {
      $line_no++;
      $data[$line_no] = $line[0];
    }
    fclose($f);
    return $data;
  }

  /**
   * Unpublish Obsolete products.
   *
   * @param string $nid
   *   A node id.
   * @param array $csv_data
   *   An array containing the SKU's from csv file.
   */
  public static function DeleteObsoleteProducts($nid, $csv_data, &$context) {
    // Crosscheck with the csv file,
    // if present do nothing
    // else un-publish the product.
    try {
      if (!empty($csv_data)) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        $sku = $node->getTitle();

        if (!in_array($sku, $csv_data)) {
          $node->setUnpublished();
          $node->save();
          \Drupal::messenger()->addMessage(t('Unpublished @sku successfully.', ['@sku' => $sku]));
        }
        else {
          // DO nothing. Found in the active list.
          \Drupal::messenger()->addMessage(t('No action taken for @sku', ['@sku' => $sku]));
        }
        $context['results'][] = $sku;
      }
      else {
        \Drupal::messenger()->addWarning(t("Uploaded file have No products"));
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('apex_common')->error($e->getMessage());
    }
  }

}
