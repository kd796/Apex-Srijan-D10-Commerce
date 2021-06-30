<?php

/**
 * @file
 * Preprocess functions related to node entities.
 *
 * Index:
 *
 * @see gearwrench_preprocess_node()
 * @see gearwrench_preprocess_node__full()
 * @see gearwrench_preprocess_node__page__full()
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements hook_preprocess_node().
 */
function gearwrench_preprocess_node(array &$variables) {
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
function gearwrench_preprocess_node__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for page, full.
 */
function gearwrench_preprocess_node__landing_page__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for page, full.
 */
function gearwrench_preprocess_node__page__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_node__VIEW_MODE() for search result.
 */
function gearwrench_preprocess_node__search_result(array &$variables) {
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
