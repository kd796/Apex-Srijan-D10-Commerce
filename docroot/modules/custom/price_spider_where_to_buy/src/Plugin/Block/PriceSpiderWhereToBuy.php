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
    $build = [];
    $build['#attached']['library'][] = 'price_spider_where_to_buy/price_spider_where_to_buy';
    $build['output'] = [
      '#theme' => 'price_spider_where_to_buy',
    ];
    return $build;
  }

}
