<?php

namespace Drupal\commerce_custom_stock\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a SFTP Connection details form.
 */
class InventoryFTPForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['commerce_custom_stock.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_custom_stock_inventory_ftp';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('commerce_custom_stock.settings');

    $form['stftp_host'] = [
      '#title' => $this->t('SFTP Host Name'),
      '#type' => 'textfield',
      '#default_value' => $config->get('stftp_host'),
    ];

    $form['stftp_user'] = [
      '#title' => $this->t('SFTP User Name'),
      '#type' => 'password',
      '#description' => $this->t("User Name for SFTP Connection.
       %s", [
         '%s' => $config->get('stftp_user') ?
         'Resubmitting the form will override existing value of this field.' : '',
       ]),
      '#default_value' => $config->get('stftp_user'),
    ];

    $form['stftp_password'] = [
      '#title' => $this->t('SFTP Password'),
      '#type' => 'password',
      '#description' => $this->t("Password for SFTP Connection.
       %s", [
         '%s' => $config->get('stftp_password') ?
         'Resubmitting the form will override existing value of this field.' : '',
       ]),
      '#default_value' => $config->get('stftp_user'),
    ];

    $form['stftp_port'] = [
      '#title' => $this->t('SFTP Port Number'),
      '#type' => 'textfield',
      '#description' =>
      $this->t("By default port value is 22."),
      '#default_value' => $config->get('stftp_port'),
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('stftp_host'))) {
      $form_state->setErrorByName('stftp_host', $this->t('FTP Host field is required.'));
    }

    if (empty($form_state->getValue('stftp_user'))) {
      $form_state->setErrorByName('stftp_user', $this->t('FTP User field is required.'));
    }

    if (empty($form_state->getValue('stftp_password'))) {
      $form_state->setErrorByName('stftp_password', $this->t('FTP Password field is required.'));
    }

    if (empty($form_state->getValue('stftp_port'))) {
      $form_state->setErrorByName('stftp_port', $this->t('FTP Port field is required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('commerce_custom_stock.settings')
      ->set('stftp_host', $form_state->getValue('stftp_host'))
      ->set('stftp_user', $form_state->getValue('stftp_user'))
      ->set('stftp_password', $form_state->getValue('stftp_password'))
      ->set('stftp_port', $form_state->getValue('stftp_port'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
