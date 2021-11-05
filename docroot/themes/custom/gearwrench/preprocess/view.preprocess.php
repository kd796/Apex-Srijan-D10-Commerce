<?php

/**
 * @file
 * Preprocess functions related to view entities.
 *
 * Index:
 *
 * @see gearwrench_preprocess_views_view()
 */

/**
 * Implements hook_preprocess_views_view().
 */
function gearwrench_preprocess_views_view(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_views_view__taxonomy_term().
 */
function gearwrench_preprocess_views_view__taxonomy_term(array &$variables) {
  // $variables['title'] = $variables['view_array']['#title']['#markup']; !
  $variables['title'] = $variables['title_attributes']['class'];
}
