<?php

/**
 * @file
 * Preprocess functions related to block entities.
 *
 * Index:
 *
 * @see sata_preprocess_block()
 */

use Drupal\Component\Utility\Html;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Implements hook_preprocess_block().
 */
function sata_preprocess_block(array &$variables) {
  /*
   * Pass id into menu attributes for better hook-suggestions.
   * @see sata_theme_suggestions_menu_alter();
   */
  if (isset($variables['content']['#menu_name'])) {
    $variables['content']['#attributes']['data-block'] = $variables['elements']['#id'];
  }

  // Add the Price Spider Generic and Product keys.
  $variables['price_spider_product_key'] = theme_get_setting('price_spider_product_key');
}

/**
 * Implements hook_preprocess_block__HOOK() for page_title_block.
 */
function sata_preprocess_block__page_title_block(&$variables) {
  $plugin_css = Html::cleanCssIdentifier($variables['plugin_id']);
  $block_class = 'block-' . $plugin_css;

  // Move page-title content into 'title' variable and reset content variable.
  $variables['title'] = $variables['content'];
  $variables['content'] = [];

  // Load the node entity from current route.
  $node = \Drupal::routeMatch()->getParameter('node');

  if (is_null($node)) {
    $node = \Drupal::routeMatch()->getParameter('node_preview');
  }

  // Node-related processing.
  if ($node instanceof NodeInterface) {
    // Default variable declaration.
    $hide_block_title = FALSE;
    $render_content_body = FALSE;

    // Bundle-specific processing.
    switch ($node->bundle()) {
      case 'article':
        $render_pre_title_created = FALSE;
        break;

      case 'landing_page':
        $hide_block_title = TRUE;
        break;

      case 'page':
        break;

    }

    // Hide block title (because it's being used elsewhere).
    if ($hide_block_title) {
      $variables['attributes']['class'][] = 'visually-hidden';
    }

    // Render body into content variable.
    if ($render_content_body) {
      $variables['content']['body'] = $node->get('body')->view('full');
    }

    // Get Hero component to replace header.
    if ($node->hasField('field_component_hero') && !$node->get('field_component_hero')->isEmpty()) {
      $hero_slide = $node->get('field_component_hero')->entity;

      if ($hero_slide instanceof Paragraph && $hero_slide->hasField('field_components') && !$hero_slide->get('field_components')->isEmpty()) {
        $variables['hero'] = $node->get('field_component_hero')->view('block_title');
        unset($variables['hero']['#theme']);
      }

      unset($hero_slide);

      /** @var \Drupal\Core\Block\BlockManager $block_manager */
      $block_manager = \Drupal::service('plugin.manager.block');
      $plugin_block = $block_manager->createInstance('system_breadcrumb_block');
      $render = $plugin_block->build();
      $variables['sata_breadcrumbs'] = \Drupal::service('renderer')->render($render);
    }
  }
}

/**
 * Implements hook_preprocess_block__HOOK() for system_branding_block.
 */
function sata_preprocess_block__system_branding_block(&$variables) {
  // Branding block processing.
  $config = \Drupal::service('config.factory')->getEditable('sata.settings');

  /** @var \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator */
  $file_url_generator = \Drupal::service('file_url_generator');
  $logo_tagline_url = '';

  // Generate the path to the logo image.
  if (theme_get_setting('logo_tagline.use_default')) {
    $logo_tagline_url = sata_get_default_tagline();
    $config->set('logo_tagline.url', $logo_tagline_url);
  }
  elseif (theme_get_setting('logo_tagline.path')) {
    $logo_tagline_url = $file_url_generator->generateString(theme_get_setting('logo_tagline.path'));
    $config->set('logo_tagline.url', $logo_tagline_url);
  }

  if (empty($logo_tagline_url)) {
    $logo_tagline_url = sata_get_default_tagline();
  }

  $variables['site_logo_tagline'] = [
    '#theme' => 'image',
    '#uri' => $logo_tagline_url,
    '#alt' => t('SATA tagline logo'),
  ];
}
