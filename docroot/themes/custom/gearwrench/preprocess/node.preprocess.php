<?php

/**
 * @file
 * Preprocess functions related to node entities.
 *
 * Index:
 *
 * @see gearwrench_preprocess_node()
 * @see gearwrench_preprocess_node__full()
 * @see gearwrench_preprocess_node__tile()
 * @see gearwrench_preprocess_node__page__full()
 * @see gearwrench_preprocess_node__media_page__full()
 * @see gearwrench_preprocess_node__media_page__teaser()
 * @see gearwrench_preprocess_node__product__full()
 * @see gearwrench_preprocess_node__search_result()
 * @see gearwrench_preprocess_node__product__teaser()
 * @see gearwrench_preprocess_node__search_index()
 * @see gearwrench_preprocess_node__product__search_index()
 * @see gearwrench_preprocess_node__product_category__full()
 * @see gearwrench_preprocess_node__product_category__tile()
 * @see gearwrench_preprocess_node__social_post__teaser()
 * @see gearwrench_preprocess_node__product_industry__full()
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
use Drupal\node\NodeInterface;

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
  // kint($variables);
  //identify the template
  // $template_name = $variables['theme_hook_original'];
  // kint($template_name);
  // $terms = $variables['node']->field_product_specifications->referencedEntities();
  // $specifications = [];
  // foreach ($terms as $value) {
  //   $term_exploded = explode(":", $value->name->value);
  //   $specifications[$term_exploded[0]][] = $term_exploded[1];
  // }
  // $variables['field_product_specifications'] = $specifications;
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
  $something_to_see = 'NOTHING';
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for media page full.
 */
function gearwrench_preprocess_node__media_page__full(&$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];
  $bundle = $node->bundle();
  $view_mode = $variables['view_mode'];

  $bundle_css = Html::cleanCssIdentifier($bundle);
  $view_mode_css = Html::cleanCssIdentifier($view_mode);

  $variables['title'] = $node->title->value;
  $variables['summary'] = $variables['content']['body'];
  $variables['categories'] = $variables['content']['field_category'];
  $variables['tags'] = $variables['content']['field_tags'];
  $variables['mediaType'] = $node->get('field_media_type')->getValue()[0]['value'];

  unset($variables['content']['field_category']);
  unset($variables['content']['field_link']);
  unset($variables['content']['field_tags']);
  unset($variables['content']['body']);

  // Track variables that should be converted to attribute objects.
  $variables['#attribute_variables'][] = 'media_attributes';

  $variables['inner_attributes']['class'][] = 'node__inner';
  $variables['media_attributes']['class'][] = 'node__media';

  // Move media to media variable.
  $variables['file'] = NULL;
  $dle_array = $node->get('field_enable_download_link')->getValue();
  $downloadLinkEnable = !empty($dle_array) ? $dle_array[0]['value'] : '0';

  if (isset($variables['content']['field_preferred_listing_image'][0])) {
    $variables['media_attributes']['class'][] = 'node__media--with-media';
    $variables['media_attributes']['class'][] = 'node__listing-image';
    $variables['media'] = $variables['content']['field_preferred_listing_image'];

    unset($variables['media']['#theme']);
    unset($variables['content']['field_preferred_listing_image']);

    if (isset($variables['content']['field_media'][0]) && $downloadLinkEnable === '1') {
      $mediaItem = Media::load($node->get('field_media')->getValue()[0]['target_id']);
      $variables['mediaItem'] = $mediaItem;

      if ($mediaItem->bundle() == 'remote_video') {
        $url = $mediaItem->get('field_media_video_embed_field')->getValue()[0]['value'];
      }
      elseif ($mediaItem->bundle() == 'file' || $mediaItem->bundle() == 'image') {
        switch ($mediaItem->bundle()) {
          case 'file':
            $fid = $mediaItem->get('field_media_file')->getValue()[0]['target_id'];
            break;

          case 'image':
            $fid = $mediaItem->get('field_media_image')->getValue()[0]['target_id'];
            break;
        }

        $file = File::load($fid);
        $url = $file->createFileUrl();
      }

      $variables['file'] = $url;
    }

    unset($variables['content']['field_media'][0]);
  }
  elseif (isset($variables['content']['field_media'][0])) {
    $variables['media_attributes']['class'][] = 'node__media--with-media';
    $variables['media_attributes']['class'][] = 'node__listing-image';
    $mediaItem = Media::load($node->get('field_media')->getValue()[0]['target_id']);

    if ($mediaItem->bundle() == 'remote_video') {
      $build = \Drupal::entityTypeManager()->getViewBuilder('media')->view($mediaItem, 'modal');
      $variables['media'] = $build;
    }
    else {
      $variables['media'] = $variables['content']['field_media'];
      unset($variables['media']['#theme']);
    }

    unset($variables['content']['field_media']);
  }
  else {
    $variables['media_attributes']['class'][] = 'node__media--no-media';
  }

  if (!empty($variables['content']['field_preferred_listing_image'][0])) {
    $variables['media'] = $variables['content']['field_preferred_listing_image'];
    unset($variables['content']['field_preferred_listing_image']);
  }
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for media page teaser.
 */
function gearwrench_preprocess_node__media_page__teaser(&$variables) {
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

  if (!empty($variables['content']['field_preferred_listing_image'][0])) {
    $variables['media'] = $variables['content']['field_preferred_listing_image'];
    unset($variables['content']['field_preferred_listing_image']);
  }
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, set listing.
 */
function gearwrench_preprocess_node__product__set_listing(&$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  $sku = $node->title->value;
  $variables['sku'] = $sku;

  // Track variables that should be converted to attribute objects.
  $variables['inner_attributes']['class'][] = 'node__inner';
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, full.
 */
function gearwrench_preprocess_node__product__full(array &$variables) {
  /** @var Drupal\node\NodeInterface $node */
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

  if (!empty($node->field_product_images->getValue())) {
    $variables['product_images'] = TRUE;
    $variables['product_image_count'] = count($node->field_product_images->getValue());
  }

  // Thumb Gallery.
  $thumbs = $node->field_product_images->getValue();

  foreach ($thumbs as $tidx => $thumb) {
    $media = Media::load($thumb['target_id']);

    if (!empty($media->field_media_image)) {
      $field_media_image = $media->field_media_image;

      if (!empty($field_media_image->target_id)) {
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
      else {
        $message = 'No target ID';
      }
    }
    elseif (!empty($media->field_media_video_embed_field) || $media->bundle() == 'remote_video') {
      $video_thumbnail_fid = $media->get('thumbnail')->target_id;

      /** @var \Drupal\file\Entity\File $file */
      $file = File::load($video_thumbnail_fid);
      $uri = $file->getFileUri();

      if (!empty($uri)) {
        $variables['thumbnails'][] = [
          '#theme' => 'image_style',
          '#width' => 100,
          '#height' => 100,
          '#style_name' => 'thumbnail_cropped',
          '#uri' => $uri,
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
      $pdf_media = Media::load($pdf_file['target_id']);
      $fid = $pdf_media->get('field_media_file')->getValue()[0]['target_id'];
      $file = File::load($fid);

      $pdfs[] = [
        'uri' => $file->createFileUrl(),
        'name' => $pdf_media->label(),
      ];
    }

    if (!empty($pdfs)) {
      $variables['pdfs'] = $pdfs;
    }
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

  if (count($main_view->result) > 0) {
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
   //will work for the node--product--full.html.twig template
   $terms = $variables['node']->field_product_specifications->referencedEntities();
   $specifications = [];
   foreach ($terms as $value) {
     $term_exploded = explode(":", $value->name->value, 2);
     $specifications[$term_exploded[0]][] = $term_exploded[1];
   }
   $variables['field_product_specifications'] = $specifications;
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
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, teaser.
 */
function gearwrench_preprocess_node__product__teaser(&$variables) {
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
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for search index.
 */
function gearwrench_preprocess_node__search_index(&$variables) {
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

  // Move media to media variable.
  if (isset($variables['content']['field_media'][0]) || isset($variables['content']['field_preferred_listing_image'][0]) || isset($variables['content']['field_component_hero'][0])) {
    $variables['media_attributes']['class'][] = 'node__media--with-media';

    if (array_key_exists('field_component_hero', $variables['content'])
        && !empty($variables['content']['field_component_hero'][0])) {
      $slideParagraph = $variables['content']['field_component_hero'][0]['#paragraph'];
      $components = $slideParagraph->get('field_components')->getValue();

      if (!empty($components[0]['target_id'])) {
        $sid = $slideParagraph->get('field_components')->getValue()[0]['target_id'];
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('paragraph');
        $storage = \Drupal::entityTypeManager()->getStorage('paragraph');
        $slide = $storage->load($sid);

        if (!empty($slide)) {
          $build = $view_builder->view($slide, 'search_index');
          $variables['media'] = $build;
          unset($variables['content']['field_component_hero']);
        }
      }
    }
    elseif (array_key_exists('field_preferred_listing_image', $variables['content'])
        && !empty($variables['content']['field_preferred_listing_image'][0])) {
      $variables['media_attributes']['class'][] = 'node__listing-image';
      $variables['media'] = $variables['content']['field_preferred_listing_image'];
      unset($variables['content']['field_preferred_listing_image']);

      if (isset($variables['content']['field_media'])) {
        unset($variables['content']['field_media']);
      }
    }
    elseif (!empty($variables['content']['field_media'][0])) {
      $variables['media_attributes']['class'][] = 'node__listing-image';
      $variables['media'] = $variables['content']['field_media'];
      unset($variables['content']['field_media']);
    }

    unset($variables['media']['#theme']);
  }
  else {
    $variables['media_attributes']['class'][] = 'node__media--no-media';
  }
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product, search index.
 */
function gearwrench_preprocess_node__product__search_index(&$variables) {
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
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product_category, full.
 */
function gearwrench_preprocess_node__product_category__full(array &$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];
  $bundle = $node->bundle();
  $view_mode = $variables['view_mode'];

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
  $variables['filters'] = \Drupal::formBuilder()->getForm('Drupal\gearwrench_product_category_filtering\Form\ProductCategoryFiltersForm');
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for product_category, full.
 */
function gearwrench_preprocess_node__product_industry__full(array &$variables) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];
  $bundle = $node->bundle();
  $view_mode = $variables['view_mode'];

  $main_view = \Drupal::entityTypeManager()
    ->getStorage('view')
    ->load('product_category')
    ->getExecutable();
  $view_args = [];

  // Get Product Classification ID's.
  if (!empty($node->get('field_industry')->getValue())) {
    $classifications = array_column($node->get('field_industry')->getValue(), 'target_id');
    $view_args[] = implode(',', $classifications);
  }

  $view_display = 'products_by_industry';
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
}

/**
 * Implements hook_preprocess_node__VIEW_MODE() for product category, tile.
 */
function gearwrench_preprocess_node__tile(&$variables) {
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
    $variables['media_attributes']['class'][] = 'node__grid-image';
  }
  else {
    $variables['media_attributes']['class'][] = 'node__media--no-media';
  }
}

/**
 * Implements hook_preprocess_node__BUNDLE__VIEW_MODE() for social post, teaser.
 */
function gearwrench_preprocess_node__social_post__teaser(&$variables) {
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

  // Need to troubleshoot why this causes the crash when not logged in
  // Move url to drupal settings.
  // if (!empty($variables['content']['field_post_url'])) {
  // $variables['#attached']['drupalSettings']['social_feed'][] = $variables['content']['field_post_url'];
  // unset($variables['content']['field_post_url']);
  // } !
}
