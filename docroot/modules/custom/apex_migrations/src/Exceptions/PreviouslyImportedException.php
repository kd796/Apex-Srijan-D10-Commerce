<?php

namespace Drupal\apex_migrations\Exceptions;

/**
 * The Previously Imported Exception.
 *
 * Defines an exception to throw during automated imports when the file we find
 * is one that was previously imported. This allows us to always grab the latest
 * file as we sort by modified date descending.
 */
class PreviouslyImportedException extends \Exception {

  /**
   * PreviouslyImportedException constructor.
   *
   * @param string $filename
   *   The name of the previously imported file.
   */
  public function __construct($filename) {
    $message = sprintf('The latest file on the FTP server matches the last downloaded file. So we will skip this import. The filename is: %s', $filename);
    parent::__construct($message);
  }

}
