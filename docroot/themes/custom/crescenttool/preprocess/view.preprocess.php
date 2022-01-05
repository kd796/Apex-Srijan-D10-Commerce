<?php

/**
 * @file
 * Preprocess functions related to view entities.
 *
 * Index:
 *
 * @see crescenttool_preprocess_views_view()
 */

/**
 * Implements hook_preprocess_views_view().
 */
function crescenttool_preprocess_views_view(array &$variables) {
  /** @var \Drupal\views\ViewExecutable $view */
  $view = $variables['view'];
  $id = $view->storage->id();
  $display = $view->current_display;
}
