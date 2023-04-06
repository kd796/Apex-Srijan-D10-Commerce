<?php

namespace Drupal\apex_common;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Batch\BatchBuilder;

/**
 * Class RedirectURLimporter handles the import of URL.
 */
class RedirectURLimporter {

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
    $data = self::read($file);
    if (empty($data)) {
      return;
    }
    else {
      $batchBuilder = new BatchBuilder();
      $batchId = 0;
      foreach ($data as $key => $url) {
        $batchBuilder->addOperation('\Drupal\apex_common\RedirectURLimporter::importRedirects', [
          $url,
        ]);
        $batchId++;
      }
      $batchBuilder
        ->setTitle('Saving Redirects...')
        ->setFinishCallback([
          RedirectURLimporter::class, 'importRedirectsFinished',
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
  public static function importRedirectsFinished($success, array $results, array $operations) {
    if ($success) {
      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed...
      \Drupal::messenger()->addMessage(t('@count results processed.', [
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
    if ((count(fgetcsv($f, 0))) > 1) {
      \Drupal::messenger()->addWarning(t('The uploaded file contains no rows with compatible redirect data. No redirects have imported.'));
      return;
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
   * Import Redirect.
   */
  public static function importRedirects($url, &$context) {
    $alias = parse_url($url);
    $redirectpath = $alias['path'];
    $path = self::stripLeadingSlash($alias['path']);

    $redirects = \Drupal::entityTypeManager()
      ->getStorage('redirect')
      ->loadByProperties(['redirect_source__path' => $path]);
    try {
      if (empty($redirects)) {
        $destinationpath = "";
        if (preg_match('/product/i', $path)) {
          $redirectdestinationpath = "/all-tools";
        }
        else {
          $redirectdestinationpath = "<front>";
        }
        $parsed_url = UrlHelper::parse($url);
        $redirectquery = $parsed_url['query'] ? $parsed_url['query'] : [];
        /** @var \Drupal\redirect\Entity\Redirect $redirect */
        $redirectEntityManager = \Drupal::entityTypeManager()->getStorage('redirect');
        $redirect = $redirectEntityManager->create();
        $redirect->setSource($redirectpath, $redirectquery);
        $redirect->setRedirect($redirectdestinationpath);
        $redirect->setStatusCode('301');
        $redirect->setLanguage('en');
        $redirect->save();
        \Drupal::messenger()->addMessage(t("The redirect has been created:") . $url);
      }
      else {
        \Drupal::messenger()->addMessage(t("Not processed (URL redirect already exist):") . $url);
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('apex_common')->error($e->getMessage());
    }

    $context['results'][] = $url;
  }

  /**
   * Remove leading slash, if present.
   *
   * @param string $path
   *   A user-supplied URL path.
   *
   * @return string
   *   A URL without the leading slash.
   */
  protected static function stripLeadingSlash($path) {
    if (strpos($path, '/') === 0) {
      return substr($path, 1);
    }
    return $path;
  }

}
