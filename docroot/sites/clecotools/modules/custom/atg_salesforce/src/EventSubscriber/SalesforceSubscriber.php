<?php

namespace Drupal\atg_salesforce\EventSubscriber;

use Drupal;
use Drupal\salesforce\Event\SalesforceEvents;
use Drupal\salesforce_mapping\Event\SalesforcePushOpEvent;
use Drupal\salesforce_mapping\Event\SalesforcePushAllowedEvent;
use Drupal\salesforce_mapping\Event\SalesforcePushParamsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\salesforce\Exception;

/**
 * Class SalesforceExampleSubscriber.
 * Trivial example of subscribing to salesforce.push_params event to set a
 * constant value for Contact.FirstName.
 * See salesforce_example for example Event Subscriber implementation.
 *
 * @package Drupal\salesforce_example
 */
class SalesforceSubscriber implements EventSubscriberInterface {

  public function pushAllowed(SalesforcePushAllowedEvent $event) {
    if (Drupal::languageManager()->getCurrentLanguage()->getId() !== 'en') {
      $event->disallowPush();
    }
  }

  public static function getSubscribedEvents() {
    $events = [
      SalesforceEvents::PUSH_ALLOWED => 'pushAllowed'
    ];
    return $events;
  }
}
