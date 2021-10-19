<?php

namespace Drupal\gearwrench_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Provides a search block for the header.
 *
 * @Block(
 *   id = "product_buy_now_sticky",
 *   admin_label = @Translation("Product: Buy Now Sticky"),
 *   category = @Translation("Gearwrench Core")
 * )
 */
class ProductBuyNowSticky extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $title = NULL;
    $sku = NULL;
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $title = $node->get('field_long_description')->value;
      $sku = $node->get('title')->value;
    }

    return [
      '#title' => $title,
      '#sku' => $sku,
      '#theme' => 'gw_product_buy_now_sticky',
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
