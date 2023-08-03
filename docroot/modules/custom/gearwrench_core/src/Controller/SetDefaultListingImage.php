<?php

namespace Drupal\gearwrench_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines a route controller for importing primary categories into nodes.
 */
class SetDefaultListingImage extends ControllerBase {

  /**
   * Handler for process request.
   */
  public function process() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'product', '=')
      ->notExists('field_media');

    $nids = $query->accessCheck(FALSE)->execute();

    $chunks = array_chunk($nids, 1);
    $batch = [
      'title' => t('Setting default Listing Images.'),
      'operations' => [],
      'init_message' => t('Listing images being set now..'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('Listing Image process has encountered an error.'),
    ];
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [
        'Drupal\gearwrench_core\Controller\SetDefaultListingImage::setImage',
        [$chunk],
      ];
    }

    batch_set($batch);
    return batch_process('/admin/config/gearwrench-core/set-default-listing-image');
  }

  /**
   * Tags the nodes with the category from the uploaded spreadsheet (column 2).
   */
  public static function setImage($items, &$context) {
    foreach ($items as $item) {
      $node = Node::load($item);
      if ($node) {
        $product_images = $node->get('field_product_images')->getValue();
        if (!empty($product_images)) {
          $node->set('field_media', ['target_id' => $product_images[0]['target_id']]);
          $node->save();
        }
        else {
          $query = \Drupal::entityQuery('media');
          $query->condition('bundle', 'image');
          $query->condition('name', 'Product Default Image');
          $ids = $query->accessCheck(FALSE)->execute();
          $media = \Drupal::entityTypeManager()->getStorage('media')->loadMultiple($ids);
          if (!empty($media)) {
            $default_media = reset($media);
            $node->set('field_media', ['target_id' => $default_media->id()]);
            $node->save();
          }
        }
      }
    }
  }

}
