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
   *   The First name.
   */
  public function getbyFirstName($prefixText = '', $contextKey = '');

  /**
   * Returns the address by Last name filter by starting text.
   *
   * @return string
   *   The Last name.
   */
  public function getbyLastName($prefixText = '', $contextKey = '');

  /**
   * Returns the address by Street filter by starting text.
   *
   * @return string
   *   The Street.
   */
  public function getbyStreet($prefixText = '', $contextKey = '');

  /**
   * Returns the address by APT filter by starting text.
   *
   * @return string
   *   The APT.
   */
  public function getbyStreet2($prefixText = '', $contextKey = '');

}
