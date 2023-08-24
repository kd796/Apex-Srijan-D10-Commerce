<?php

namespace Drupal\commerce_custom_stock;

use Drupal\commerce\Context;
use Drupal\commerce_order\AvailabilityCheckerInterface;
use Drupal\commerce_order\AvailabilityResult;
use Drupal\commerce_order\Entity\OrderItemInterface;

/**
 * Class to check stock.
 */
class VariationAvailabilityChecker implements AvailabilityCheckerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(OrderItemInterface $order_item) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function check(OrderItemInterface $order_item, Context $context) {

    $entity = $order_item->getPurchasedEntity();
    $quantity = $order_item->getQuantity();
    // Order should not be placed if Stock is zero.
    $route_name = \Drupal::routeMatch()->getRouteName();

    if ($entity->field_stock->value <= 0) {
      $result = t('This product is out of stock.');
      return AvailabilityResult::unavailable($result);
    }
    // Cart quantity should not be more than stock value and checking this only on cart page.
    if ($route_name == 'commerce_cart.page' && $quantity > $entity->field_stock->value) {
      $result = t('The quantity is more than the stock quantity');
      return AvailabilityResult::unavailable($result);
    }

    return AvailabilityResult::neutral();
  }

}
