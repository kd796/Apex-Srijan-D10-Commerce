<?php

namespace Drupal\at_migrations\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for apex tools Common routes.
 */
class AtCommonController extends ControllerBase {

  /**
   * Product Listing.
   */
  public function productsListing() {
    return [
      '#theme' => 'product_listing',
    ];
  }

  /**
   * Product Details.
   */
  public function productsDetails() {
    return [
      '#theme' => 'product_details',
    ];
  }

}
