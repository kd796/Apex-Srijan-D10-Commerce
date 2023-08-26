<?php

namespace Drupal\commerce_custom_stock\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cart Event Subscriber.
 */
class UpdateStockEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['orderCompleteHandler'];

    return $events;
  }

  /**
   * Reduces the stock value after purchase.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   Event object.
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    // Order items in the cart.
    $items = $order->getItems();
    foreach ($items as $key => $item) {

      $entity = $item->getPurchasedEntity();
      // Quantity.
      $quantity = $item->getQuantity();
      $stock_field_value = $entity->field_stock->value;
      $entity->field_stock->value = $stock_field_value - $quantity;
      $entity->save();
    }
  }

}
