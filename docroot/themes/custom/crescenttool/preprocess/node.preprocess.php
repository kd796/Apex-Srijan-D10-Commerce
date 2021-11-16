<?php

/**
 * @file
 * Preprocess functions related to node entities.
 *
 * Index:
 *
 * @see crescenttool_preprocess_node()
 * @see crescenttool_preprocess_node__full()
 * @see crescenttool_preprocess_node__landing_page__full()
 * @see crescenttool_preprocess_node__page__full()
 * @see crescenttool_preprocess_node__search_result()
 * @see crescenttool_preprocess_node__product__teaser()
 * @see gearwrench_preprocess_node__product__full()
 */

use Drupal\Core\Cache\Cache;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\media\Entity\Media;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Html;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermStorage;
use Drupal\Core\Url;

/**
 * Implements hook_preprocess_node().
 */
function crescenttool_preprocess_node(array &$variables) {
  // dump($variables);
  /*
   * Removing theme from field_components so it doesn't render wrapper
   * "field__item" on all our components
   */
  if (array_key_exists('field_components', $variables['content'])) {
    unset($variables['content']['field_components']['#theme']);
  }
}

/**
 * Implements hook_preprocess_node__VIEW_MODE() for full.
 */
function crescenttool_preprocess_node__full(array &$variables) {
  // Nothing to see here.;.
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for page, full.
 */
function crescenttool_preprocess_node__landing_page__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for page, full.
 */
function crescenttool_preprocess_node__page__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_node__VIEW_MODE() for search result.
 */
function crescenttool_preprocess_node__search_result(array &$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];
  $bundle = $node->bundle();
  $view_mode = $variables['view_mode'];

  $bundle_css = Html::cleanCssIdentifier($bundle);
  $view_mode_css = Html::cleanCssIdentifier($view_mode);

  // Unset body if search summary is present.
  if (isset($variables['content']['search_api_excerpt'])) {
    $variables['attributes']['class'][] = 'node--with-search-excerpt';
    unset($variables['content']['body']);
  }
  else {
    unset($variables['content']['body']['#theme']);
  }
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, teaser.
 */
function crescenttool_preprocess_node__product__teaser(&$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];
  $bundle = $node->bundle();
  $view_mode = $variables['view_mode'];

  $sku = $node->title->value;
  $variables['sku'] = $sku;

  $bundle_css = Html::cleanCssIdentifier($bundle);
  $view_mode_css = Html::cleanCssIdentifier($view_mode);

  // Track variables that should be converted to attribute objects.
  $variables['#attribute_variables'][] = 'media_attributes';

  $variables['inner_attributes']['class'][] = 'node__inner';
  $variables['media_attributes']['class'][] = 'node__media';

  // Move media to media variable.
  if (isset($variables['content']['field_media'][0])) {
    $variables['media_attributes']['class'][] = 'node__media--with-media';
    $variables['media_attributes']['class'][] = 'node__listing-image';
    $variables['media'] = $variables['content']['field_media'];
    unset($variables['media']['#theme']);
    unset($variables['content']['field_media']);
  }
  else {
    $variables['media_attributes']['class'][] = 'node__media--no-media';
  }
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, full.
 */
function crescenttool_preprocess_node__product__full(array &$variables) {
  $node = $variables['elements']['#node'];
  if ($node instanceof NodeInterface) {
    $current_nid = $node->id();
  }
  $sku = $node->title->value;
  $variables['sku'] = $sku;
  // Product Features.
  $page_top_products_features = $variables['content']['field_product_features'];
  // Add first 3 Product Features to an array to display at the top of the page.
  $all_product_features = $node->get('field_product_features')->getValue();
  foreach ($all_product_features as $key => $feature) {
    if ($key > 2) {
      unset($page_top_products_features[$key]);
    }
  }
  $variables['page_top_products_features'] = $page_top_products_features;

  // Count Product Images.
  $variables['product_images'] = NULL;
  $variables['field_product_images_exist'] = NULL;
  if (!empty($node->field_product_images->getValue())) {
    $variables['product_images'] = TRUE;
    $variables['field_product_images_exist'] = TRUE;
    $variables['product_image_count'] = count($node->field_product_images->getValue());
    $thumbs = $node->field_product_images->getValue();
  }
  elseif (!empty($node->field_media->getValue())) {
    $variables['product_images'] = TRUE;
    $variables['product_image_count'] = count($node->field_media->getValue());
    $thumbs = $node->field_media->getValue();
  }

  // Thumb Gallery.
  // $thumbs = $node->field_media->getValue();
  foreach ($thumbs as $thumb) {
    $media = Media::load($thumb['target_id']);
    $fid = $media->field_media_image->target_id;
    $file = File::load($fid);
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
    ];
  }

  // Related Products.
  /** @var \Drupal\views\ViewExecutable $main_view */
  $main_view = \Drupal::entityTypeManager()
    ->getStorage('view')
    ->load('related_products')
    ->getExecutable();
  $view_args = [];
  $view_display = 'related_categories';
  $view_exposed_input = [];
  // Initialize, setup, and execute backfill view display.
  $main_view->initDisplay();
  $main_view->setDisplay($view_display);
  $main_view->setArguments($view_args);
  $main_view->setExposedInput($view_exposed_input);
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
  // Merge storage cache tags.
  $variables['#cache']['contexts'] = Cache::mergeContexts($variables['#cache']['contexts'], $main_view->storage->getCacheContexts());
  $variables['#cache']['tags'] = Cache::mergeTags($variables['#cache']['tags'], $main_view->getCacheTags());
  $variables['#cache']['max-age'] = Cache::mergeMaxAges($variables['#cache']['max-age'], $main_view->storage->getCacheMaxAge());
  $variables['related_items'] = $main_view->buildRenderable($view_display, $main_view->args);
}
