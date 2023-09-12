<?php

namespace Drupal\commerce_us_custom_tax\EventSubscriber;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_price\Price;
use Drupal\commerce_us_custom_tax\UtilityTax;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters order total.
 */
class AlterOrderTotalEventsSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\commerce_us_custom_tax\UtilityTax definition.
   *
   * @var \Drupal\commerce_us_custom_tax\UtilityTax
   */
  protected $utilityObj;

  /**
   * Constructor.
   */
  public function __construct(UtilityTax $utilityObj) {
    // Own utility service obj.
    $this->utilityObj = $utilityObj;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      OrderEvents::ORDER_LOAD => 'totalChange',
    ];
  }

  /**
   * Adding new adjustment to order total.
   */
  public function totalChange(OrderEvent $event) {
    // Initial tax is 0.
    $tax = 0;

    // Order object .
    $order_obj = $event->getOrder();

    $order_total = 0;
    if ($order_obj->getSubtotalPrice() != NULL) {
      $order_total = $order_obj->getSubtotalPrice()->getNumber();
    }

    if ($order_obj->getBillingProfile() != NULL) {
      // Getting values from address field.
      $customer_address = $order_obj->getBillingProfile()->get('address')->first();

      $state = $customer_address->get('administrative_area')->getCastedValue();
      $city = $customer_address->get('locality')->getCastedValue();
      $postal_code = $customer_address->get('postal_code')->getCastedValue();
      $postal_code = (int) $postal_code;
      $county = '';
      if ($order_obj->getBillingProfile()->get('field_county')->value !== NULL) {
        $county = $order_obj->getBillingProfile()->get('field_county')->value;
      }
      // Getting tax rate based on the above datas.
      $matched_rate = $this->utilityObj->getMatching($state, $postal_code, $city, $county);
      // Calculating tax.
      $tax = (int) $order_total * (int) $matched_rate / 100;

      // Creating adjustment array.
      $adjustments = new Adjustment([
        'type' => 'custom_adjustment',
        'label' => 'Tax',
        'amount' => new Price($tax, 'USD'),
        'included' => FALSE,
      ]);
      $order_obj->addAdjustment($adjustments);
    }
  }

}
