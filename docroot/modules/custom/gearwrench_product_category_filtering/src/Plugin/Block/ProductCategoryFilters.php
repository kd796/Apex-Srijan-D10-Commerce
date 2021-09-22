<?php

namespace Drupal\gearwrench_product_category_filtering\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a product listing filters block.
 *
 * @Block(
 *   id = "gearwrench_product_listing_filters",
 *   admin_label = @Translation("Product Listing Filters"),
 *   category = @Translation("Gearwrench")
 * )
 */
class ProductCategoryFilters extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'product_category_filters_form' => \Drupal::formBuilder()->getForm('Drupal\gearwrench_product_category_filtering\Form\ProductCategoryFiltersForm'),
    ];
  }

}
