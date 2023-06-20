<?php

namespace Drupal\apex_tools_custom_quotation;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a quotation entity type.
 */
interface QuotationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the quotation creation timestamp.
   *
   * @return int
   *   Creation timestamp of the quotation.
   */
  public function getCreatedTime();

  /**
   * Sets the quotation creation timestamp.
   *
   * @param int $timestamp
   *   The quotation creation timestamp.
   *
   * @return \Drupal\apex_tools_custom_quotation\QuotationInterface
   *   The called quotation entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the quotation status.
   *
   * @return bool
   *   TRUE if the quotation is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the quotation status.
   *
   * @param bool $status
   *   TRUE to enable this quotation, FALSE to disable.
   *
   * @return \Drupal\apex_tools_custom_quotation\QuotationInterface
   *   The called quotation entity.
   */
  public function setStatus($status);

}
