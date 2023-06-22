<?php

namespace Drupal\atg_callout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

class CalloutSettingsForm extends ConfigFormBase
{

  const FORM_ID = 'atg_callout_settings';
  const SETTINGS = 'atg_callout.settings';

  public function getFormId()
  {
    return self::FORM_ID;
  }

  protected function getEditableConfigNames()
  {
    return [
      self::SETTINGS
    ];
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config(self::SETTINGS);

    $form['enable'] = [
      '#type'  => 'details',
      '#title' => $this->t('Callout Enable/Disable'),
      '#open'  => TRUE,
    ];

    $form['enable']['atg_callout_enable'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enable callout'),
      '#default_value' => 1,
      '#input'         => TRUE,
      '#return_value'  => 1,
      '#description'   => $this->t("The callout will not show if this box is left unchecked."),
    ];

    $form['position'] = [
      '#type'  => 'details',
      '#title' => $this->t('Callout Positioning'),
      '#open'  => TRUE,
    ];

    $form['position']['atg_callout_position'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Position'),
      '#default_value' => $config->get('atg_callout_position'),
      '#description'   => $this->t("The callout will not show if this box is left unchecked."),
      '#options'       => [
        // 'top'   => $this->t('Top'),
        'right' => $this->t('Right'),
        // 'bottom'  => $this->t('Bottom'),
        'left'  => $this->t('Left'),
      ]
    ];

    $form['content'] = [
      '#type'  => 'details',
      '#title' => $this->t('Callout Content'),
      '#open'  => TRUE,
    ];

    $form['content']['atg_callout_title'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Title'),
      '#default_value' => $config->get('atg_callout_title')
    ];

    $form['content']['atg_callout_copy'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Copy'),
      '#default_value' => $config->get('atg_callout_copy')
    ];

    $form['content']['atg_callout_path'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('URL'),
      '#default_value' => $config->get('atg_callout_path'),
      '#description'   => $this->t("Can be a full URL or relative to the domain, e.g. /tools/tool-catalog"),
    ];

    $form['content']['atg_callout_path_de'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('URL_DE'),
      '#default_value' => $config->get('atg_callout_path_de'),
      '#description'   => $this->t("Can be a full URL or relative to the domain, e.g. /tools/tool-catalog"),
    ];

    $form['content']['atg_callout_target'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Open in new tab/window'),
      '#default_value' => 1,
      '#input'         => TRUE,
      '#return_value'  => 1,
      '#description'   => $this->t("The callout will not show if this box is left unchecked."),
    ];

    $form['image'] = [
      '#type'  => 'details',
      '#title' => $this->t('Callout Image'),
      '#open'  => TRUE,
    ];

    $form['image']['atg_callout_image_path'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Path to callout image'),
      '#default_value' => $config->get('atg_callout_image_path')
    ];

    $form['image']['or'] = [
      '#markup'  => '<strong>'.t('- OR -').'</strong>',
    ];

    $form['image']['atg_callout_image_upload'] = [
      '#type'              => 'managed_file',
      '#title'             => $this->t('Upload callout image'),
      '#default_value'     => $config->get('atg_callout_image_upload'),
      '#description'       => $this->t("Enter a complete URL to an image above or use this field to upload your image."),
      '#upload_location'   => 'public://',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    $form['image']['atg_callout_image_upload_de'] = [
      '#type'              => 'managed_file',
      '#title'             => $this->t('Upload callout image for DE'),
      '#default_value'     => $config->get('atg_callout_image_upload_de'),
      '#description'       => $this->t("Enter a complete URL to an image above or use this field to upload your image."),
      '#upload_location'   => 'public://',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    if ($form_state->getValue('atg_callout_image_upload') and $form_state->getValue('atg_callout_image_upload_de')) {
      $image = $form_state->getValue('atg_callout_image_upload');
	  $image_de = $form_state->getValue('atg_callout_image_upload_de');
	  $file = File::load($image[0]);
      $file->setPermanent();
      $file->save();
      $file = File::load($image_de[0]);
      $file->setPermanent();
      $file->save();
    }

    parent::submitForm($form, $form_state);

    $this->config(self::SETTINGS)
      ->set('atg_callout_enable', $form_state->getValue('atg_callout_enable'))
      ->set('atg_callout_position', $form_state->getValue('atg_callout_position'))
      ->set('atg_callout_title', $form_state->getValue('atg_callout_title'))
      ->set('atg_callout_copy', $form_state->getValue('atg_callout_copy'))
      ->set('atg_callout_path', $form_state->getValue('atg_callout_path'))
      ->set('atg_callout_path_de', $form_state->getValue('atg_callout_path_de'))
      ->set('atg_callout_target', $form_state->getValue('atg_callout_target'))
      ->set('atg_callout_image_path', $form_state->getValue('atg_callout_image_path'))
      ->set('atg_callout_image_upload', $form_state->getValue('atg_callout_image_upload'))
	  ->set('atg_callout_image_upload_de', $form_state->getValue('atg_callout_image_upload_de'))
      ->save();
  }
}
