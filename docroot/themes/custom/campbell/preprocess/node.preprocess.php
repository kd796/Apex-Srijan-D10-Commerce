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
  $variables['filters'] = \Drupal::formBuilder()->getForm('Drupal\campbell_product_category_filtering\Form\ProductCategoryFiltersForm');

}
