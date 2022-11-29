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
    $price_spider_generic_product_button = NULL;

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $title = $node->get('field_long_description')->value;
      $sku = $node->get('title')->value;
    }

    // Add the Price Spider Product key, Generic product checkbox value and wtb url.
    $price_spider_product_key = theme_get_setting('price_spider_product_key');
    $price_spider_generic_product_button = theme_get_setting('price_spider_generic_product_button');
    $wtb_url = sata_core_get_wtb_url();

    return [
      '#title' => $title,
      '#sku' => $sku,
      '#price_spider_product_key' => $price_spider_product_key,
      '#price_spider_generic_product_button' => $price_spider_generic_product_button,
      '#wtb_url' => $wtb_url,
      '#theme' => 'gw_product_buy_now_sticky',
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
