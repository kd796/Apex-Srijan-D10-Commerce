<?php

/**
 * @file
 * Preprocess functions related to paragraph entities.
 *
 * Index:
 *
 * @see crescenttool_preprocess_paragraph()
 * @see crescenttool_preprocess_paragraph__accordion__full()
 * @see crescenttool_preprocess_paragraph__accordion_item__full()
 * @see crescenttool_preprocess_paragraph__content__full()
 * @see crescenttool_preprocess_paragraph__hero__full()
 * @see crescenttool_preprocess_paragraph__hero_slide__full()
 * @see crescenttool_preprocess_paragraph__steps__full()
 * @see crescenttool_preprocess_paragraph__step__full()
 * @see crescenttool_preprocess_paragraph__embed_iframe__full()
 * @see crescenttool_preprocess_paragraph__featured_media__full()
 * @see crescenttool_preprocess_paragraph__section__full()
 * @see crescenttool_preprocess_paragraph__tabs__full()
 * @see crescenttool_preprocess_paragraph__tabs_tab__full()
 * @see crescenttool_preprocess_paragraph__product_slider__full()
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Implements hook_preprocess_paragraph().
 */
function crescenttool_preprocess_paragraph(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $bundle = $paragraph->bundle();
  $view_mode = $variables['view_mode'];
  $base_class = $variables['component_base_class'];

  // Initialize settings (this approach allows less IF/ELSE nesting).
  $setting_anchor_custom = FALSE;
  $setting_background_image_unwrap = FALSE;
  $setting_class_custom = FALSE;
  $setting_component_unwrap = FALSE;
  $setting_title_move = FALSE;

  // Toggle settings based on view-mode.
  switch ($variables['view_mode']) {
    case 'full':
      $setting_anchor_custom = TRUE;
      $setting_background_image_unwrap = TRUE;
      $setting_class_custom = TRUE;
      $setting_component_unwrap = TRUE;
      $setting_title_move = TRUE;
      break;
  }

  // Add custom anchor to the component.
  if ($setting_anchor_custom && $paragraph->hasField('field_html_anchor') && !$paragraph->get('field_html_anchor')->isEmpty()) {
    $variables['attributes']['class'][] = "paragraph-component--with-anchor";
    $variables['title_prefix']['anchor'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'paragraph-component__anchor',
          "{$base_class}__anchor",
        ],
        'id' => Html::cleanCssIdentifier($paragraph->get('field_html_anchor')->value),
      ],
      '#value' => ''
    ];
  }

  // Unset background-image field theme wrapper (to not print an empty div).
  if ($setting_background_image_unwrap && array_key_exists('field_background_image', $variables['content'])) {
    unset($variables['content']['field_background_image']);
  }

  // Add custom classes to the component.
  if ($setting_class_custom && $paragraph->hasField('field_class_custom') && !$paragraph->get('field_class_custom')->isEmpty()) {
    $field_class_custom = preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $paragraph->get('field_class_custom')->value);
    $variables['attributes']['class'] = array_merge($variables['attributes']['class'], explode(' ', $field_class_custom));
  }

  // Remove component field-wrappers.
  if ($setting_component_unwrap && array_key_exists('field_components', $variables['content'])) {
    unset($variables['content']['field_components']['#theme']);
  }

  // Set title variable from fields.
  if ($setting_title_move) {
    foreach (['field_title_link', 'field_title'] as $title_fieldname) {
      if (array_key_exists($title_fieldname, $variables['content'])) {

        $variables['title'] = $variables['content'][$title_fieldname];

        unset($variables['title']['#theme']);
        unset($variables['content'][$title_fieldname]);
        break;
      }
    }
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for accordion, full.
 */
function crescenttool_preprocess_paragraph__accordion__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Hide tab heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for accordion_item, full.
 */
function crescenttool_preprocess_paragraph__accordion_item__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Check if this accordion item should be open by default.
  $is_open = (!$paragraph->get('field_enabled')->isEmpty() && $paragraph->get('field_enabled')->value === '1');
  if ($is_open) {
    $variables['attributes']['class'][] = "{$base_class}--open";
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for content, full.
 */
function crescenttool_preprocess_paragraph__content__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $paragraphId = $paragraph->id();
  $base_class = $variables['component_base_class'];

  // Initialize variables.
  $variables['inner_attributes']['class'][] = "{$base_class}__inner";

  // Track if media should be placed outside of content.
  $media_outside = FALSE;

  // Move media field to new variable.
  if (isset($variables['content']['field_media_item']) && !empty($variables['content']['field_media_item'])) {
    $variables['attributes']['class'][] = "{$base_class}--with-media";

    $field_settings = [
      'type' => 'blazy_media',
      'label' => 'hidden',
    ];

    // Determine changes based on selected media layout.
    $layout = ($paragraph->get('field_media_layout')->isEmpty()) ? NULL : $paragraph->get('field_media_layout')->target_id;
    switch ($layout) {
      case 'content_media_layout__half_left':
      case 'content_media_layout__half_right':
        $field_settings['settings']['image_style'] = 'content_half';
        $media_outside = TRUE;
        break;

      case 'content_media_layout__top':
      case 'content_media_layout__bottom':
        $field_settings['settings']['responsive_image_style'] = 'content_top_bottom';
        $media_outside = TRUE;
        break;

      default:
        $field_settings['settings']['responsive_image_style'] = 'teaser';
        break;
    }

    $variables['media'] = $paragraph->get('field_media_item')->view($field_settings);
    $variables['media']['#attributes']['class'][] = "{$base_class}__media";
    $variables['media_outside'] = $media_outside;
    unset($variables['content']['field_media_item']);
  }

  // Move cta link to footer and add class.
  if (array_key_exists('field_link', $variables['content']) && !empty($variables['content']['field_link'])) {
    $variables['footer']['field_link'] = $variables['content']['field_link'];
    $variables['footer']['field_link'][0]['#options']['attributes']['class'] = "{$base_class}__link button";
    unset($variables['content']['field_link'], $variables['footer']['field_link']['#theme']);
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for content_callout, full.
 */
function crescenttool_preprocess_paragraph__content_callout__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_paragraph__VIEW_MODE() for steps, full.
 */
function crescenttool_preprocess_paragraph__steps__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for step, full.
 */
function crescenttool_preprocess_paragraph__step__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Initialize variables.
  $variables['inner_attributes']['class'][] = "{$base_class}__inner";
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for embed_iframe, full.
 */
function crescenttool_preprocess_paragraph__embed_iframe__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Track that iframe_attributes should get converted.
  $variables['#attribute_variables'][] = 'iframe_attributes';

  // Clean src input and compile style tag.
  $src = $paragraph->get('field_link')->isEmpty() ?: $paragraph->get('field_link')->uri;
  $style_tag = '';
  $height = '600px';
  if (!$paragraph->get('field_style_height')->isEmpty()) {
    $height = Html::escape($paragraph->get('field_style_height')->value);
    $style_tag .= "height: {$height}; ";
  }
  $width = '100%';
  if (!$paragraph->get('field_style_width')->isEmpty()) {
    $width = Html::escape($paragraph->get('field_style_width')->value);
    $style_tag .= "height: {$height}; ";
  }

  // Set attributes.
  $variables['iframe_attributes']['class'][] = "{$base_class}__iframe";
  $variables['iframe_attributes']['height'] = $height;
  $variables['iframe_attributes']['src'] = $src;
  $variables['iframe_attributes']['style'] = $style_tag;
  $variables['iframe_attributes']['width'] = $width;
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for featured_content, full.
 */
function crescenttool_preprocess_paragraph__featured_content__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Determine changes based on selected layout.
  $layout = ($paragraph->get('field_layout')->isEmpty()) ? NULL : $paragraph->get('field_layout')->target_id;
  switch ($layout) {
    case 'featured_content_layout__grid':
      $variables['#attached']['library'][] = 'crescenttool/paragraph--full--featured-content--layout-grid';
      $field_settings['type'] = 'entity_reference_entity_view';
      $field_settings['settings']['view_mode'] = 'teaser';
      $field_settings['label'] = 'hidden';
      break;

    case 'featured_content_layout__list':
    default:
      $variables['#attached']['library'][] = 'crescenttool/paragraph--full--featured-content--layout-list';
      $field_settings['type'] = 'entity_reference_label';
      $field_settings['settings']['link'] = TRUE;
      $field_settings['label'] = 'hidden';
  }

  // Replace field with new settings.
  $variables['content']['field_related_nodes'] = $paragraph->get('field_related_nodes')->view($field_settings);

  // Convert field_related_nodes to unordered list.
  $variables['list']['#attributes']['class'][] = "{$base_class}__list";
  $variables['list']['#wrapper_attributes']['class'][] = "{$base_class}__list-wrapper";
  $variables['list']['#items'] = [];
  $variables['list']['#theme'] = 'item_list';

  // Hide list content heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';

  // Run through field items'.
  foreach (Element::children($variables['content']['field_related_nodes']) as $delta) {
    // Add class to list-item.
    $variables['content']['field_related_nodes'][$delta]['#wrapper_attributes']['class'][] = "{$base_class}__list-item";
    // Add field item to list item.
    $variables['list']['#items'][] = $variables['content']['field_related_nodes'][$delta];
  }
  // Remove field render array.
  unset($variables['content']['field_related_nodes']);
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for featured_links, full.
 */
function crescenttool_preprocess_paragraph__featured_links__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Convert field_links to unordered list.
  $variables['list']['#attributes']['class'][] = "{$base_class}__list";
  $variables['list']['#wrapper_attributes'] = [];
  $variables['list']['#items'] = [];
  $variables['list']['#theme'] = 'item_list';
  // Run through field items'.
  foreach (Element::children($variables['content']['field_links']) as $delta) {
    // Add class to list-item.
    $variables['content']['field_links'][$delta]['#wrapper_attributes']['class'][] = "{$base_class}__list-item";
    // Add field item to list item.
    $variables['list']['#items'][] = $variables['content']['field_links'][$delta];
  }
  // Remove field render array.
  unset($variables['content']['field_links']);
}

/**
 * Implements hook_preprocess_paragraph__VIEW_MODE() for featured media, full.
 */
function crescenttool_preprocess_paragraph__featured_media__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Determine changes based on selected layout.
  $layout = ($paragraph->get('field_layout')->isEmpty()) ? NULL : $paragraph->get('field_layout')->target_id;
  switch ($layout) {
    case 'featured_media_layout__grid':
      $variables['#attached']['library'][] = 'crescenttool/paragraph--full--featured-media--layout-grid';
      $field_settings['type'] = 'entity_reference_entity_view';
      $field_settings['settings']['view_mode'] = 'teaser';
      $field_settings['label'] = 'hidden';
      break;

    case 'featured_media_layout__slider':
      $variables['#attached']['library'][] = 'crescenttool/paragraph--full--featured-media--layout-slider';
      $field_settings['type'] = 'entity_reference_entity_view';
      $field_settings['settings']['view_mode'] = 'hero';
      $field_settings['label'] = 'hidden';
      break;

    case 'featured_media_layout__live_area':
      $variables['#attached']['library'][] = 'crescenttool/paragraph--full--featured-media--layout-live-area';
      $field_settings['type'] = 'entity_reference_entity_view';
      $field_settings['settings']['view_mode'] = 'live_area';
      $field_settings['label'] = 'hidden';
      break;

    case 'featured_media_layout__full':
    default:
      $variables['#attached']['library'][] = 'crescenttool/paragraph--full--featured-media--layout-full';
      $field_settings['type'] = 'entity_reference_entity_view';
      $field_settings['settings']['view_mode'] = 'embed';
      $field_settings['label'] = 'hidden';
      break;
  }

  // Replace field with new settings.
  $variables['content']['field_media_items'] = $paragraph->get('field_media_items')->view($field_settings);

  // Convert field_media to unordered list.
  $variables['list']['#attributes']['class'][] = "{$base_class}__list";
  $variables['list']['#wrapper_attributes']['class'][] = "{$base_class}__list-wrapper";
  $variables['list']['#items'] = [];
  $variables['list']['#theme'] = 'item_list';

  // Run through field items'.
  foreach (Element::children($variables['content']['field_media_items']) as $delta) {
    // Add class to list-item.
    $variables['content']['field_media_items'][$delta]['#wrapper_attributes']['class'][] = "{$base_class}__list-item";
    // Add field item to list item.
    $variables['list']['#items'][] = $variables['content']['field_media_items'][$delta];
  }

  // Remove field render array.
  unset($variables['content']['field_media_items']);
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for hero, full.
 */
function crescenttool_preprocess_paragraph__hero__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Track whether the hero has slides.
  $variables['has_slides'] = !$paragraph->get('field_components')->isEmpty();

  // Track that inner_attributes should get converted.
  $variables['#attribute_variables'][] = 'inner_attributes';

  // Set inner attributes.
  $variables['inner_attributes']['class'][] = "{$base_class}__inner";
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for hero_slide, full.
 */
function crescenttool_preprocess_paragraph__hero_slide__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Initialize variables.
  $variables['inner_attributes']['class'][] = "{$base_class}__inner";

  // Move background image field to new variable.
  $variables['background_image'] = $variables['content']['field_media_background'];

  if (isset($variables['content']['field_link'][0])) {
    $variables['footer']['field_link'] = $variables['content']['field_link'];
    $variables['footer']['field_link'][0]['#options']['attributes']['class'] = "{$base_class}__link button";
    unset($variables['content']['field_link'], $variables['footer']['field_link']['#theme']);
  }

  unset($variables['media']['#theme']);
  unset($variables['content']['field_media_background']);
}

/**
 * Implements hook_preprocess_paragraph__VIEW_MODE() for pullquote, full.
 */
function crescenttool_preprocess_paragraph__pullquote__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Remove wrapper from field_content_plain.
  unset($variables['content']['field_content_plain']['#theme']);

  // Move attribution to footer.
  if (array_key_exists('field_attribution', $variables['content']) && !empty($variables['content']['field_attribution'])) {
    $variables['footer']['field_attribution'] = $variables['content']['field_attribution'];
    unset($variables['content']['field_attribution']);
  }
}

/**
 * Implements hook_preprocess_paragraph__VIEW_MODE() for section, full.
 */
function crescenttool_preprocess_paragraph__section__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $paragraphId = $paragraph->id();

  // Process background color if specified.
  if ($paragraph->hasField('field_background_color') && isset($paragraph->get('field_background_color')->getValue()[0])) {
    $variables['attributes']['class'][] = 'paragraph-component--background-color';
  }

  // Add styling to head tag since the color field module doesn't do it.
  if ($paragraph->hasField('field_background_color') && isset($paragraph->get('field_background_color')->getValue()[0])) {

    $backgroundColor = $paragraph->get('field_background_color')->getValue()[0]['color'];
    $backgroundOpacity = (isset($paragraph->get('field_background_color')->getValue()[0]['opacity']) ? ($paragraph->get('field_background_color')->getValue()[0]['opacity']) : (1));
    $backgroundColorStyling = [
      '#tag' => 'style',
      '#value' => '.paragraph-component--background-color[data-entity-id="' . $paragraphId . '"]::after { background-color: ' . $backgroundColor . '; opacity: ' . $backgroundOpacity . '; }',
    ];
    $variables['#attached']['html_head'][] = [
      $backgroundColorStyling,
      'backgroundColorBeforeStyling-' . $paragraphId,
    ];

  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for tabs, full.
 */
function crescenttool_preprocess_paragraph__tabs__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Hide tab heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';

  // Initialize Navigation variable.
  $variables['nav']['#attributes']['class'][] = "{$base_class}__nav";
  $variables['nav']['#wrapper_attributes'] = [];
  $variables['nav']['#items'] = [];
  $variables['nav']['#theme'] = 'item_list';

  // Validate and create nav from tab list.
  foreach ($paragraph->get('field_components') as $delta => $component) {
    /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $component */

    // Add tab to nav if title is set.
    if ($component->entity instanceof ParagraphInterface && !$component->entity->get('field_title')->isEmpty()) {
      $variables['nav']['#items'][] = [
        '#type' => 'link',
        '#title' => $component->entity->get('field_title')->value,
        '#url' => Url::fromUserInput("#paragraph-{$component->entity->id()}"),
        '#wrapper_attributes' => [
          'class' => ["{$base_class}__nav-item"],
        ],
      ];
    }
    // Otherwise remove it from being rendered.
    else {
      unset($variables['content']['field_components'][$delta]);
    }
  }

  // Toggle version of tabs. (This is done here mainly as a placeholder for if
  // we ever need to support vertical tabs or other version. Then we can make it
  // toggleable and just add the class.)
  $tab_version = 'horizontal';
  switch ($tab_version) {
    case 'horizontal':
    default:
      $variables['attributes']['class'][] = "{$base_class}--horizontal";
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for tabs_tab, full.
 */
function crescenttool_preprocess_paragraph__tabs_tab__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Default to tabindex -1 for accessibility. JS will initialize and update.
  $variables['attributes']['tabindex'] = '-1';
  // Hide tab heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for product_slider, full.
 */
function crescenttool_preprocess_paragraph__product_slider__full(&$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  $field_settings['type'] = 'entity_reference_entity_view';
  $field_settings['settings']['view_mode'] = 'teaser';
  $field_settings['label'] = 'hidden';

  $variables['#attached']['library'][] = 'crescenttool/paragraph--full--product-slider';
  $field_settings['type'] = 'entity_reference_entity_view';
  $field_settings['settings']['view_mode'] = 'teaser';
  $field_settings['label'] = 'hidden';

  // Replace field with new settings.
  $variables['content']['field_products'] = $paragraph->get('field_products')->view($field_settings);

  // Convert field_related_nodes to unordered list.
  $variables['list']['#attributes']['class'][] = "{$base_class}__list";
  $variables['list']['#wrapper_attributes']['class'][] = "{$base_class}__list-wrapper";
  $variables['list']['#items'] = [];
  $variables['list']['#theme'] = 'item_list';

  // Hide list content heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';

  // Run through field items'.
  foreach (Element::children($variables['content']['field_products']) as $delta) {
    // Add class to list-item.
    $variables['content']['field_products'][$delta]['#wrapper_attributes']['class'][] = "{$base_class}__list-item";
    // Add field item to list item.
    $variables['list']['#items'][] = $variables['content']['field_products'][$delta];
  }
  // Remove field render array.
  unset($variables['content']['field_products']);
}
