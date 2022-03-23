<?php

namespace Drupal\apex_migrations\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PostMigrationSubscriber.
 *
 * Run a test to validate that the image server is available.
 *
 * @package Drupal\YOUR_MODULE
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_IMPORT][] = ['onMigratePostImport'];
    return $events;
  }

  /**
   * Check for our specified last node migration and run our flagging mechanisms.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {
    $migration_id = $event->getMigration()->getBaseId();

    // Do a little bit of cleanup.
    if (strpos($migration_id, '_products', -9)) {
      $store = \Drupal::service('tempstore.private')->get('apex_migrations');
      $store->delete('image_server_available');
    }
  }

}
