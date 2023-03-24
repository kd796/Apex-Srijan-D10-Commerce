<?php

namespace Drupal\apex_migrations\EventSubscriber;

use Drupal\apex_migrations\ImageFtp;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PreMigrationSubscriber.
 *
 * Run a test to validate that the image server is available.
 *
 * @package Drupal\YOUR_MODULE
 */
class PreMigrationSubscriber implements EventSubscriberInterface {

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PRE_IMPORT][] = ['onMigratePreImport'];
    return $events;
  }

  /**
   * Check for the image server status just once to avoid thousands of requests.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePreImport(MigrateImportEvent $event) {
    $migration_id = $event->getMigration()->getBaseId();

    if (strpos($migration_id, '_products', -9)
      || str_starts_with($migration_id, 'campbell_product_category_pages')
      || str_starts_with($migration_id, 'campbell_product_classifications')) {
      $store = \Drupal::service('tempstore.private')->get('apex_migrations');

      if ($this->checkImageServerStatus()) {
        $store->set('image_server_available', TRUE);
      }
      else {
        $store->set('image_server_available', FALSE);
        $event->logMessage('The image server is unreachable.');
      }
    }
  }

  /**
   * Checks the status of the image server.
   *
   * @return bool
   *   TRUE if the image server is available, FALSE otherwise.
   */
  private function checkImageServerStatus() {
    $ftp = new ImageFtp();

    return $ftp->hasValidConnection();
  }

}
