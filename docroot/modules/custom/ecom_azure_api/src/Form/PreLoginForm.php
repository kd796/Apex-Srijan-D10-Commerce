<?php

namespace Drupal\ecom_azure_api\Form;

/**
 * @file
 * Contains \Drupal\ecom_azure_api\Form\PreLoginForm.
 */
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class for PreLoginForm form.
 */
class PreLoginForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ecom_pre_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('ATG Network ID'),
      '#required' => TRUE,
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('session')->set('pre_login_success', TRUE);
    $current_path = \Drupal::service('path.current')->getPath();
    $destination = \Drupal::destination()->get();
    if ($current_path !== $destination) {
      $form_state->setRedirect($destination);
    }
  }

}
