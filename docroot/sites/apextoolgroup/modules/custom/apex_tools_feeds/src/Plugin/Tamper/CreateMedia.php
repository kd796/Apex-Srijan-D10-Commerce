<?php

namespace Drupal\apex_tools_feeds\Plugin\Tamper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\tamper\Exception\TamperException;
use Drupal\tamper\TamperableItemInterface;
use Drupal\tamper\TamperBase;
use Drupal\media\Entity\Media;

/**
 * Plugin implementation of the Str Len plugin.
 *
 * @Tamper(
 *   id = "apex_tools_create_media",
 *   label = @Translation("Create media entity"),
 *   description = @Translation("Create media entity"),
 *   category = "Other"
 * )
 */
class CreateMedia extends TamperBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['media_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select media type'),
      '#options' => [
        'image' => $this->t('Image'),
        'file' => $this->t('File')
      ],
      '#required' => TRUE
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->setConfiguration([
      'media_type' => $form_state->getValue('media_type'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function tamper($data, TamperableItemInterface $item = NULL) {
    if (!$data) {
      return $data;
    }
    if (!is_string($data)) {
      throw new TamperException('Input should be a string.');
    }

    // We already have the files in the same location.
    $url_parts = explode('/', $data);
    $file_name = end($url_parts);
    $file_name_alt = explode('.', $file_name);
    array_pop($file_name_alt);
    $file_name_alt = implode('.', $file_name_alt);

    // Check if file exists.
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $data]);
    $file = reset($files) ?: NULL;

    if (!$file) {
      $file = File::create([
        'filename' => $file_name,
        'uri' => $data,
        'status' => 1,
        'uid' => 1,
      ]);
      $file->save();
    }

    // See if media item in use already.
    $usage = \Drupal::service('file.usage')->listUsage($file);
    $media_id = NULL;
    if (count($usage) > 0 && !empty($usage['file']['media'])) {
      $media_id = array_key_first($usage['file']['media']);
    }

    if ($media_id) {
      return $media_id;
    }

    $image_media = Media::create([
      'name' => $file_name_alt,
      'bundle' => $this->getSetting('media_type'),
      'uid' => 1,
      'field_media_' . $this->getSetting('media_type') => [
        'target_id' => $file->id(),
        'alt' => $file_name_alt,
        'title' => $file_name_alt,
      ]
    ]);

    try {
      $image_media->save();
    }
    catch (\Exception $e) {
      // Return unaltered data.
      return $data;
    }

    return $image_media->id();
  }

}
