<?php

namespace Drupal\campbell_product_category_filtering\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a product listing filters block.
 *
 * @Block(
 *   id = "campbell_product_category_filters",
 *   admin_label = @Translation("Product Listing Filters"),
 *   category = @Translation("Campbell")
 * )
 */
class ProductCategoryFilters extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'product_category_filters_form' => \Drupal::formBuilder()->getForm('Drupal\campbell_product_category_filtering\Form\ProductCategoryFiltersForm'),
    ];
  }

}
