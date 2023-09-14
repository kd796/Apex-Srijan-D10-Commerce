<?php

namespace Drupal\ecom_addrexx;

/**
 * Interface for Address API.
 */
interface AddrexxInterface {

  /**
   * Returns the address by ZIP filter by starting text.
   *
   * @return string
   *   The ZIP.
   */
  public function getbyZip($prefixText = '', $contextKey = '');

  /**
   * Returns the address by First name filter by starting text.
   *
   * @return string
   *   The ZIP.
   */
  public function getbyFirstName($prefixText = '', $contextKey = '');

  /**
   * Returns the address by Last name filter by starting text.
   *
   * @return string
   *   The ZIP.
   */
  public function getbyLastName($prefixText = '', $contextKey = '');

  /**
   * Returns the address by Street filter by starting text.
   *
   * @return string
   *   The ZIP.
   */
  public function getbyStreet($prefixText = '', $contextKey = '');

}
