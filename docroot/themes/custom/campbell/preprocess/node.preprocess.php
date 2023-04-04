<?php

/**
 * @file
 * Preprocess functions related to node entities.
 */

use Drupal\Core\Cache\Cache;

/**
 * @file
 * Preprocess node templates.
 */

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product_category, full.
 */
function campbell_preprocess_node__product_category__full(array &$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  $main_view = \Drupal::entityTypeManager()
    ->getStorage('view')
    ->load('product_category')
    ->getExecutable();
  $view_args = [];

  // Get Product Classification ID's.
  if (!empty($node->get('field_product_classifications')->getValue())) {
    $classifications = array_column($node->get('field_product_classifications')->getValue(), 'target_id');
    $view_args[] = implode(',', $classifications);
  }

  $view_display = 'products_by_category';
  $main_view->initDisplay();
  $main_view->setDisplay($view_display);
  $main_view->setArguments($view_args);
  $main_view->preExecute();
  $main_view->execute();

  // Initialize cache contexts.
  if (!isset($variables['#cache']['contexts'])) {
    $variables['#cache']['contexts'] = [];
  }

  // Initialize cache tags.
  if (!isset($variables['#cache']['tags'])) {
    $variables['#cache']['tags'] = [];
  }

  // Initialize cache max-age.
  if (!isset($variables['#cache']['max-age'])) {
    $variables['#cache']['max-age'] = Cache::PERMANENT;
  }

  // Merge display cache tags.
  $variables['#cache']['contexts'] = Cache::mergeContexts($variables['#cache']['contexts'], $main_view->display_handler->getCacheMetadata()
    ->getCacheContexts());
  $variables['#cache']['tags'] = Cache::mergeTags($variables['#cache']['tags'], $main_view->display_handler->getCacheMetadata()
    ->getCacheTags());
  $variables['#cache']['max-age'] = Cache::mergeMaxAges($variables['#cache']['max-age'], $main_view->display_handler->getCacheMetadata()
    ->getCacheMaxAge());

  // Merge view cache tags.
  $variables['#cache']['contexts'] = Cache::mergeContexts($variables['#cache']['contexts'], $main_view->storage->getCacheContexts());
  $variables['#cache']['tags'] = Cache::mergeTags($variables['#cache']['tags'], $main_view->getCacheTags());
  $variables['#cache']['max-age'] = Cache::mergeMaxAges($variables['#cache']['max-age'], $main_view->storage->getCacheMaxAge());

  $variables['view'] = $main_view->buildRenderable($view_display, $main_view->args);
  $form = \Drupal::formBuilder()->getForm('Drupal\campbell_product_category_filtering\Form\ProductCategoryFiltersForm');
  $variables['filters'] = $form;
  $flag = (isset($form['category-filter']) || isset($form['attribute_filter']))? 1 : 0;
  $variables['check_filter'] = $flag;
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for news, full.
 */
function campbell_preprocess_node__news__full(array &$variables) {
  $node = $variables['elements']['#node'];
  $file_path = '';

  if (!empty($node->field_news_release_document->getValue())) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $document_id = $node->field_news_release_document->getValue()[0]['target_id'];
    $media_entity = $entity_type_manager->getStorage('media')->load($document_id);
    $file_entity = $entity_type_manager->getStorage('file')->load($media_entity->field_media_file->target_id);
    $file_path = $file_entity->createFileUrl();
  }

  $variables['news_release_document_path'] = $file_path;
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, full.
 */
function campbell_preprocess_node__product__full(array &$variables) {
  /** @var Drupal\node\NodeInterface $node */
  $node = $variables['elements']['#node'];
  $entity_type_manager = \Drupal::entityTypeManager();

  // Count Product Images.
  $variables['product_images'] = NULL;

  if (!empty($node->field_product_images->getValue())) {
    $variables['product_images'] = TRUE;
    $variables['product_image_count'] = count($node->field_product_images->getValue());
  }

  // Thumb Gallery.
  $thumbs = $node->field_product_images->getValue();

  foreach ($thumbs as $thumb) {
    $media = $entity_type_manager->getStorage('media')->load($thumb['target_id']);

    if (!empty($media->field_media_image)) {
      $field_media_image = $media->field_media_image;

      if (!empty($field_media_image->target_id)) {
        $fid = $media->field_media_image->target_id;
        $file = $entity_type_manager->getStorage('file')->load($fid);

        $thumb_variables = [
          'style_name' => 'thumbnail_cropped',
          'uri' => $file->getFileUri(),
        ];

        // The image.factory service will check if our image is valid.
        $image = \Drupal::service('image.factory')->get($file->getFileUri());

        if ($image->isValid()) {
          $thumb_variables['width'] = $image->getWidth();
          $thumb_variables['height'] = $image->getHeight();
        }
        else {
          $thumb_variables['width'] = $thumb_variables['height'] = NULL;
        }

        $variables['thumbnails'][] = [
          '#theme' => 'image_style',
          '#width' => $thumb_variables['width'],
          '#height' => $thumb_variables['height'],
          '#style_name' => $thumb_variables['style_name'],
          '#uri' => $thumb_variables['uri'],
          '#prefix' => '<div class="swiper-slide">',
          '#suffix' => '</div>',
        ];
      }
      else {
        $message = 'No target ID';
      }
    }
    elseif ($media && (!empty($media->field_media_video_embed_field) || $media->bundle() == 'remote_video')) {
      $video_thumbnail_fid = $media->get('thumbnail')->target_id;

      /** @var \Drupal\file\Entity\File $file */
      $file = $entity_type_manager->getStorage('file')->load($video_thumbnail_fid);
      $uri = $file->getFileUri();

      $variables['is_video_image'] = 1;
      if (!empty($uri)) {
        $variables['video_thumbnails'][] = [
          '#theme' => 'image_style',
          '#width' => 100,
          '#height' => 100,
          '#style_name' => 'thumbnail_cropped',
          '#uri' => $uri,
          '#prefix' => '<div class="swiper-slide">',
          '#suffix' => '</div>',
          '#attributes' => [
            'class' => 'video-thumbnail',
          ],
        ];
      }
    }
    else {
      $message = 'No field media image';
    }
  }

  $variables['pdfs'] = NULL;

  // List PDFs.
  if (!$node->get('field_pdfs')->isEmpty()) {
    $files = $node->get('field_pdfs')->getValue();
    $pdfs = [];

    foreach ($files as $pdf_file) {
      $pdf_media = $entity_type_manager->getStorage('media')->load($pdf_file['target_id']);
      $fid = $pdf_media->get('field_media_file')->getValue()[0]['target_id'];
      $file = $entity_type_manager->getStorage('file')->load($fid);

      $pdfs[] = [
        'uri' => $file->createFileUrl(),
        'name' => $pdf_media->label(),
      ];
    }

    if (!empty($pdfs)) {
      $variables['pdfs'] = $pdfs;
    }
  }
  $block = $entity_type_manager->getStorage('block')->load('addthis');
  if ($block) {
    $variables['addtothis_block_output'] = $entity_type_manager->getViewBuilder('block')->view($block);
  }
}
