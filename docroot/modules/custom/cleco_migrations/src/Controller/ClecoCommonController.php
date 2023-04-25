<?php

namespace Drupal\cleco_migrations\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for cleco Common routes.
 */
class ClecoCommonController extends ControllerBase {

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
