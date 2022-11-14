<?php

namespace Drupal\sata_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Provides a search block for the header.
 *
 * @Block(
 *   id = "product_buy_now_sticky",
 *   admin_label = @Translation("Product: Buy Now Sticky"),
 *   category = @Translation("sata Core")
 * )
 */
class ProductBuyNowSticky extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $title = NULL;
    $sku = NULL;
    $price_spider_product_key = NULL;
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $title = $node->get('field_long_description')->value;
      $sku = $node->get('title')->value;
    }

    // Add the Price Spider Product and Reviews key.
    $price_spider_product_key = theme_get_setting('price_spider_product_key');

    return [
      '#title' => $title,
      '#sku' => $sku,
      '#price_spider_product_key' => $price_spider_product_key,
      '#theme' => 'gw_product_buy_now_sticky',
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
