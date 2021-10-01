<?php

namespace Drupal\apex_tools_instagram_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Apex Tools Instagram Feed settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apex_tools_instagram_feed_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['apex_tools_instagram_feed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter App ID'),
      '#default_value' => \Drupal::state()->get('app_id'),
      '#required' => true,
    ];
    $form['app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter App Secret'),
      '#default_value' => \Drupal::state()->get('app_secret'),
      '#required' => true,
    ];
    $form['redirect_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Redirect URI'),
      '#default_value' => \Drupal::state()->get('redirect_uri'),
      '#required' => true,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $api_id = $form_state->getValue('app_id');
    \Drupal::state()->set('app_id', $api_id);
    $app_secret = $form_state->getValue('app_secret');
    \Drupal::state()->set('app_secret', $app_secret);
    $redirect_uri = $form_state->getValue('redirect_uri');
    \Drupal::state()->set('redirect_uri', $redirect_uri);
    parent::submitForm($form, $form_state);
  }

}
