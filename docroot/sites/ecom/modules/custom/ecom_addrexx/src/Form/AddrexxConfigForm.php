<?php

namespace Drupal\ecom_addrexx\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The configuration form.
 */
class AddrexxConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ecom_addrexx_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'ecom_addrexx.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ecom_addrexx.settings');

    $form['ecom_api_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Details'),
      '#description' => $this->t('Addrexx API details'),
      '#open' => TRUE,
      '#collapsible' => TRUE,
    ];
    $form['ecom_api_details']['ecom_api_endpoint'] = [
      '#title' => $this->t('Addrexx API endpoint'),
      '#type' => 'textfield',
      '#default_value' => $config->get('ecom_api_endpoint'),
      '#description' => $this->t('Addrexx API endpoint'),
    ];
    $form['ecom_api_details']['ecom_api_frontend_key'] = [
      '#title' => $this->t('Addrexx API Frontend endpoint'),
      '#type' => 'password',
      '#default_value' => $config->get('ecom_api_frontend_key'),
      '#description' => $this->t('Addrexx API endpoint %val', [
        '%val' => $config->get('ecom_api_backend_key') ? "Leave if already configured." : '',
      ]),
    ];
    $form['ecom_api_details']['ecom_api_backend_key'] = [
      '#title' => $this->t('Addrexx API Backend endpoint'),
      '#type' => 'password',
      '#default_value' => $config->get('ecom_api_backend_key'),
      '#description' => $this->t('Addrexx API endpoint %val', [
        '%val' => $config->get('ecom_api_backend_key') ? "Leave if already configured." : '',
      ]),
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
    foreach ($form_state->getValues() as $key => $item) {
      if (substr($key, 0, 5) !== "ecom_") {
        continue;
      }
      else {
        $this->config('ecom_addrexx.settings')
          ->set($key, (string) trim($item))
          ->save();
      }
    }
    parent::submitForm($form, $form_state);
  }

}
