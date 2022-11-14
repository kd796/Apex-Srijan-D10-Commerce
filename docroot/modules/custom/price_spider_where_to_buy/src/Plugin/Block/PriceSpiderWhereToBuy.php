<?php

namespace Drupal\price_spider_where_to_buy\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Plugin implementation of the price spider where to buy block.
 *
 * @Block(
 *   id = "price_spider_where_to_buy",
 *   admin_label = @Translation("Price Spider Where To Buy Block"),
 *   category = @Translation("Custom")
 * )
 */
class PriceSpiderWhereToBuy extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Add the Price Spider Product and Reviews key.
    $price_spider_generic_key = theme_get_setting('price_spider_generic_key');
    $build = [];
    $build['#attached']['library'][] = 'price_spider_where_to_buy/price_spider_where_to_buy';
    $build['output'] = [
      '#theme' => 'price_spider_where_to_buy',
      '#price_spider_generic_key' => $price_spider_generic_key,
    ];
    return $build;
  }

}
