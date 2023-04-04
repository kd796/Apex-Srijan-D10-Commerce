<?php

namespace Drupal\apex_common;

use Drupal\Component\Utility\UrlHelper;
use Drupal\redirect\Entity\Redirect;

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
  public static function import($file) {

    // Parse the CSV file into a readable array.
    $data = self::read($file);
    if (empty($data)) {
      \Drupal::messenger()->addWarning(t('The uploaded file contains no rows with compatible redirect data.'));
    }
    else {
      // Save valid redirects.
      foreach ($data as $row) {
        $source_path = parse_url($row['source']);
        $row['source'] = $source_path['path'];
        if (preg_match('/product/i', $row['source'])) {
          $operations[] = [
            ['\Drupal\apex_common\RedirectURLimporter', 'saveProductPage'],
            [$row],
          ];
        }
        else {
          $operations[] = [
            ['\Drupal\apex_common\RedirectURLimporter', 'saveNonProductPage'],
            [$row],
          ];
        }

      }

      $batch = [
        'title' => t('Saving Redirects'),
        'operations' => $operations,
        'finished' => [
          '\Drupal\apex_common\RedirectURLimporter',
          'finish',

        ],
      ];
      batch_set($batch);
    }

  }

  /**
   * Batch API callback.
   */
  public static function finish($success, $results, $operations) {
    if ($success) {
      $message = t('Redirects completed, all redirection are successfully added.');
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addStatus($message);
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
      if ($line_no == 1) {
        continue;
      }

      // Strip any trailing slashes as these are removed when looking for
      // matching redirects.
      // @see \Drupal\redirect\EventSubscriber\RedirectRequestSubscriber::onKernelRequestCheckRedirect()
      $line[0] = rtrim($line[0], '/');
      // Build a row of data.
      $data[$line_no] = [
        'source' => self::stripLeadingSlash($line[0]),
      ];

    }
    fclose($f);
    return $data;
  }

  /**
   * Save an individual redirect entity, if no redirect already exists.
   *
   * @param str[] $redirect_array
   *   Keyed array of redirects, in the format.
   */
  public static function saveNonProductPage(array $redirect_array) {
    if ($redirects = self::redirectExists($redirect_array)) {
      $redirect = reset($redirects);
    }
    else {
      $parsed_url = UrlHelper::parse(trim($redirect_array['source']));
      $path = $parsed_url['path'] ?? NULL;

      $query = $parsed_url['query'] ?? NULL;

      /** @var \Drupal\redirect\Entity\Redirect $redirect */
      $redirectEntityManager = \Drupal::entityTypeManager()->getStorage('redirect');
      $redirect = $redirectEntityManager->create();
      $redirect->setSource($path, $query);
    }

    $redirect->setRedirect('/home');
    $redirect->setStatusCode('301');
    $redirect->setLanguage('en');
    $redirect->save();

  }

  /**
   * Save an individual redirect entity, if no redirect already exists.
   *
   * @param str[] $redirect_array
   *   Keyed array of redirects, in the format.
   */
  public static function saveProductPage(array $redirect_array) {

    if ($redirects = self::redirectExists($redirect_array)) {
      $redirect = reset($redirects);
    }
    else {
      $parsed_url = UrlHelper::parse(trim($redirect_array['source']));
      $path = $parsed_url['path'] ?? NULL;

      $query = $parsed_url['query'] ?? NULL;

      /** @var \Drupal\redirect\Entity\Redirect $redirect */
      $redirectEntityManager = \Drupal::entityTypeManager()->getStorage('redirect');
      $redirect = $redirectEntityManager->create();
      $redirect->setSource($path, $query);
    }

    $redirect->setRedirect('/all-tools');
    $redirect->setStatusCode('301');
    $redirect->setLanguage('en');
    $redirect->save();

  }

  /**
   * Check if a redirect already exists for this source path.
   *
   * @param str[] $row
   *   Keyed array of redirects, in the format
   *    [source].
   *
   * @return mixed
   *   FALSE if the redirect does not exist, array of redirect objects
   *    if it does.
   */
  public static function redirectExists(array $row) {
    // @todo memoize the query.
    $parsed_url = UrlHelper::parse(trim($row['source']));
    $path = $parsed_url['path'] ?? NULL;
    $path = ltrim($path, '/');
    $query = $parsed_url['query'] ?? NULL;

    $hash = Redirect::generateHash($path, $query, 'en');

    // Search for duplicate.
    $redirects = \Drupal::entityTypeManager()
      ->getStorage('redirect')
      ->loadByProperties(['hash' => $hash]);
    if (!empty($redirects)) {
      return $redirects;
    }
    return FALSE;
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
