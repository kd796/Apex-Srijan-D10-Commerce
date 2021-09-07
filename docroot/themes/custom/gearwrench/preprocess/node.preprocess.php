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

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Html;
use Drupal\taxonomy\Entity\Term;

/**
 * Implements hook_preprocess_node().
 */
function gearwrench_preprocess_node(array &$variables) {
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
function gearwrench_preprocess_node__full(array &$variables) {
  // Nothing to see here.;.
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
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for page, full.
 */
function gearwrench_preprocess_node__product__full(array &$variables) {
  $node = $variables['elements']['#node'];
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
  // Product Specifications.
  $specs = $node->field_product_specifications->getValue();
  foreach ($specs as $key => $spec) {
    $term = Term::load($spec['target_id']);
    $vocab = Vocabulary::load($term->bundle());
    $initial_string = $variables['content']['field_product_specifications'][$key]['#plain_text'];
    $formatted_string = $vocab->label() . ' : ' . $initial_string;
    $variables['content']['field_product_specifications'][$key]['#plain_text'] = $formatted_string;
  }
  // Count Product Images.
  $variables['product_images'] = NULL;
  if (!empty($node->field_product_images->getValue())) {
    $variables['product_images'] = TRUE;
    $variables['product_image_count'] = count($node->field_product_images->getValue());
  }
  // Thumb Gallery.
  $thumbs = $node->field_product_images->getValue();
  foreach ($thumbs as $thumb) {
    $file = File::load($thumb['target_id']);
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
      '#uri' => $thumb_variables['uri']
    ];
  }

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

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product listing, teaser.
 */
function gearwrench_preprocess_node__product_listing__tile(&$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];
  $bundle = $node->bundle();
  $view_mode = $variables['view_mode'];
  $bundle_css = Html::cleanCssIdentifier($bundle);
  $view_mode_css = Html::cleanCssIdentifier($view_mode);
  // Track variables that should be converted to attribute objects.
  $variables['#attribute_variables'][] = 'media_attributes';
  $variables['inner_attributes']['class'][] = 'node__inner';
  $variables['media_attributes']['class'][] = 'node__media';
  if (isset($variables['content']['field_media'][0])) {
    $variables['media_attributes']['class'][] = 'node__product-listing-grid-image';
  }
  else {
    $variables['media_attributes']['class'][] = 'node__media--no-media';
  }
}
