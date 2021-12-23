<?php

/**
 * @file
 * Preprocess functions related to media entities.
 *
 * Index:
 * @see gearwrench_preprocess_media()
 * @see gearwrench_preprocess_media__remote_video__hero()
 */

use Drupal\Component\Utility\UrlHelper;

/**
 * Implements hook_preprocess_media().
 */
function gearwrench_preprocess_media(array &$variables) {
  /** @var \Drupal\media\MediaInterface $media */
  $media = $variables['media'];

  // Track that iframe_attributes should get converted.
  $variables['#attribute_variables'][] = 'media_attributes';

  // Add base classes.
  $variables['media_attributes']['class'][] = 'media__media';

  // Move respective media field into its own variable.
  $field_name = NULL;
  switch ($media->bundle()) {
    case 'audio':
      $field_name = 'field_media_audio_file';
      break;

    case 'file':
      $field_name = 'field_media_file';
      break;

    case 'image':
      $field_name = 'field_media_image';
      break;

    case 'remote_video':
      $field_name = 'field_media_video_embed_field';
      break;

    case 'video':
      $field_name = 'field_media_video_file';
      break;
  }

  if (!is_null($field_name) && array_key_exists($field_name, $variables['content'])) {
    $variables['media_embed'] = $variables['content'][$field_name];
    unset($variables['media_embed']['#theme']);
    unset($variables['content'][$field_name]);
  }
}

/**
 * Implements hook_preprocess_media__BUNDLE__VIEW_MODE() for remote_video, hero.
 */
function gearwrench_preprocess_media__remote_video__hero(array &$variables) {
  /** @var \Drupal\media\MediaInterface $media */
  $media = $variables['media'];

  if (isset($variables['media_embed'][0]['children']['#type'])) {
    $type = $variables['media_embed'][0]['children']['#type'];

    if ($type == 'video_embed_iframe') {
      $provider = $variables['media_embed'][0]['children']['#provider'];
      $url = $variables['media_embed'][0]['children']['#url'];
      $parsed_url = parse_url($url);

      $options = [];
      $options['query'] = isset($parsed_url['query']) ? parse_str($parsed_url['query']) : [];

      if ($provider == 'youtube') {
        $options['query']['rel'] = 0;
        $options['query']['autoplay'] = 0;
        $options['query']['enablejsapi'] = 1;
        $options['query']['modestbranding'] = 1;
      }
      elseif ($provider == 'vimeo') {
        $options['query']['autoplay'] = 0;
        $options['query']['background'] = 1;
        $options['query']['loop'] = 1;
      }

      /** @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface $unrouted_url_assembler */
      $unrouted_url_assembler = Drupal::service('unrouted_url_assembler');
      $url = $unrouted_url_assembler->assemble($url, $options, FALSE);

      $variables['media_embed'][0]['children']['#url'] = $url;
    }
  }

  // Make video autoplay, loop and disabling any controls and branding.
  if (isset($variables['media_embed'][0]['#build']['settings']['scheme']) && $variables['media_embed'][0]['#build']['settings']['type'] === 'video') {
    $settings = $variables['media_embed'][0]['#build']['settings'];
    $settings['autoplay'] = TRUE;

    // Make modifications by provider.
    $url = !empty($settings['autoplay_url']) ? $settings['autoplay_url'] : $settings['embed_url'];

    if (UrlHelper::isExternal($url)) {
      $options = [];

      switch ($settings['scheme']) {
        case 'vimeo':
          $options['query']['autoplay'] = 1;
          $options['query']['background'] = 1;
          $options['query']['loop'] = 1;
          $options['query']['muted'] = 1;
          break;

        case 'youtube':
          $options['query']['autoplay'] = 1;
          $options['query']['showinfo'] = 0;
          $options['query']['controls'] = 0;
          $options['query']['disablekb'] = 1;
          $options['query']['fs'] = 0;
          $options['query']['mute'] = 1;
          $options['query']['loop'] = 1;
          $options['query']['enablejsapi'] = 1;
          $options['query']['modestbranding'] = 1;
          $options['query']['playlist'] = $settings['video_id'];
          break;
      }

      // Replace url.
      /** @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface $unrouted_url_assembler */
      $unrouted_url_assembler = Drupal::service('unrouted_url_assembler');
      $url = $unrouted_url_assembler->assemble($url, $options, FALSE);

      $settings['autoplay_url'] = $url;
      $settings['embed_url'] = $url;

      $variables['media_embed'][0]['#build']['settings'] = $settings;
      $variables['media_embed']['#blazy'] = $settings;
    }
  }
}
