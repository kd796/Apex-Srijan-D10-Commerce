<?php

namespace Drupal\apex_common;

use Drupal\Component\Utility\UrlHelper;
use Drupal\redirect\Entity\Redirect;
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
      \Drupal::messenger()->addWarning(t('The uploaded file contains no rows with compatible redirect data.'));
      return;
    }
    else {
      $batchBuilder = new BatchBuilder();
      $numOperations = 0;
      $batchId = 1;
      foreach ($data as $key => $url) {
        $batchBuilder->addOperation('\Drupal\apex_common\RedirectURLimporter::importRedirects', [
          $url,
        ]);
        $batchId++;
        $numOperations++;
      }
      $batchBuilder
        ->setTitle('Updating @num node(s)', ['@num' => $numOperations,])
        ->setFinishCallback([RedirectURLimporter::class, 'importRedirectsFinished'])
        ->setErrorMessage(t('Batch has encountered an error'));
      batch_set($batchBuilder->toArray());
    }
  }

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
    $line_no = 0;
    $data = [];
    while ($line = fgetcsv($f, 0)) {
      $line_no++;
      $data[$line_no] = $line[0];
    }
    fclose($f);
    return $data;
  }

  public static function importRedirects($url) {
    $alias = parse_url($url);
    $redirectpath = $alias['path'];
    $path = self::stripLeadingSlash($alias['path']);
    //     if (empty($alias['query'])) {
    //       $path = self::stripLeadingSlash($alias['path']);
    //       \Drupal::messenger()->addMessage(t($path));
    //     }
    //     else {
    //       $tmp = $alias['path'];
    //       $tmp1 = self::stripLeadingSlash($tmp);
    //       $tmp2 = substr($tmp1, 0, -1);
    //       $path = $tmp2 . "?" . $alias['query'];
    //       \Drupal::messenger()->addMessage(t($path));
    //     }
    $redirects = \Drupal::entityTypeManager()
      ->getStorage('redirect')
       ->loadByProperties(['redirect_source__path' => $path]);
    if (empty($redirects)) {
      $destinationpath = "";
      if (preg_match('/product/i', $path)) {
        $redirectdestinationpath = "/all-tools";
      }
      else {
        $redirectdestinationpath = "<front>";
      }
      $parsed_url = UrlHelper::parse($url);
      //       echo "<pre>"; print_r($parsed_url); die;
      //       if (!empty($parsed_url['query'])) {
      //         $redirectpath = $alias['path'];
      //       }
      //       $redirectpath = $parsed_url['path'] ? $parsed_url['path'] : NULL;
      $redirectquery = $parsed_url['query'] ? $parsed_url['query'] : [];
      /** @var \Drupal\redirect\Entity\Redirect $redirect */
      $redirectEntityManager = \Drupal::entityTypeManager()->getStorage('redirect');
      $redirect = $redirectEntityManager->create();
      $redirect->setSource($redirectpath, $redirectquery);
      $redirect->setRedirect($redirectdestinationpath);
      $redirect->setStatusCode('301');
      $redirect->setLanguage('en');
      $redirect->save();
    }
    else {
      \Drupal::messenger()->addMessage(t("Not processed (URL redirect already exist): " . $path));
    }
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
