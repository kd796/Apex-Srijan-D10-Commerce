<?php

namespace Drupal\apex_migrations\Plugin\migrate\destination;

use Drupal\taxonomy\Entity\Term;
use Drupal\media\Entity\Media;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;

/**
 * The 'apex_product' destination plugin.
 *
 * @MigrateDestination(
 *   id = "apex_product"
 * )
 */
class ApexProduct extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  public static $entityType = 'node';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return parent::create($container, $configuration, 'entity:' . static::$entityType, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) {
    $entity = $this->storage->load(reset($destination_identifier));

    if ($entity && $entity instanceof NodeInterface) {
      if ($entity->bundle() === 'product') {
        // Delete imported media and files.
        if (!empty($entity->get('field_media')->target_id)) {
          // Get fid from field_media.
          $mid = $entity->get('field_media')->target_id;
          $media = Media::load($mid);
          $fid = $media->field_media_image->target_id;

          if ($mid) {
            $media_storage_handler = \Drupal::entityTypeManager()->getStorage('media');
            $media_item = $media_storage_handler->load($mid);

            if ($media_item) {
              $media_storage_handler->delete([$media_item]);
            }
          }

          if ($fid) {
            $file_storage_handler = \Drupal::entityTypeManager()->getStorage('file');
            $file = $file_storage_handler->load($fid);

            if ($file) {
              $file_storage_handler->delete([$file]);
            }
          }
        }

        if (!empty($entity->get('field_product_images'))) {
          foreach ($entity->get('field_product_images') as $image) {
            if (isset($image->target_id)) {
              $mid = $image->target_id;
              $media = Media::load($mid);

              $media_storage_handler = \Drupal::entityTypeManager()->getStorage('media');
              $media_item = $media_storage_handler->load($mid);

              if ($media_item) {
                $media_storage_handler->delete([$media_item]);
              }
            }

            if (isset($media->field_media_image->target_id)) {
              $fid = $media->field_media_image->target_id;
              $file_storage_handler = \Drupal::entityTypeManager()->getStorage('file');
              $file = $file_storage_handler->load($fid);

              if ($file) {
                $file_storage_handler->delete([$file]);
              }
            }
          }
        }

        // Delete imported taxonomy items.
        if (!empty($entity->get('field_product_specifications')->getValue())) {
          $specifications = array_column($entity->get('field_product_specifications')->getValue(), 'target_id');

          foreach ($specifications as $specification) {
            if ($term = Term::load($specification)) {
              // Delete the term itself.
              $term->delete();
            }
          }
        }
      }
    }

    // Execute the normal rollback from here.
    parent::rollback($destination_identifier);
  }

}
