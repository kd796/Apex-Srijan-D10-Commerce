<?php

namespace Drupal\apex_migrations\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Provides a APEX PIM dump upload form.
 */
class ProductDownloadServiceForm extends FormBase {

  /**
   * Constructs a PimFileUpdateForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->setConfigFactory($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apex_migrations_product_download_service';
  }

  /**
   * Gets a config field value.
   *
   * @param string $name
   *   The name of the config field.
   *
   * @return array|mixed|null
   *   The config field value or NULL if not found.
   */
  protected function getConfig(string $name) {
    $value = $this->config('apex_migrations.settings')->get($name);
    return $value ?? NULL;
  }

  /**
   * Sets a config field value.
   *
   * @param string $name
   *   The name of the config field.
   * @param string $value
   *   Value to be saved.
   */
  protected function setConfig(string $name, string $value) {
    $this->configFactory->getEditable('apex_migrations.settings')->set($name, $value)->save();
  }

  /**
   * Gets the value from user input, falls back to default value and then config.
   *
   * @param string $name
   *   The name of the config field.
   * @param \Drupal\Core\Form\FormStateInterface|null $form_state
   *   (Optional) The current state of the form.
   * @param string $default
   *   (Optional) The default value.
   *
   * @return array|mixed|null
   *   Return the value of the form element, if it exists, else the default or config value.
   */
  protected function getValueOrConfig(string $name, FormStateInterface $form_state = NULL, string $default = NULL) {
    if (!empty($form_state)) {
      $input = $form_state->getUserInput();

      if (!empty($input[$name])) {
        return $input[$name];
      }
    }

    $value = $this->getConfig($name);

    if ($value === NULL && $default !== NULL) {
      $value = $default;
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $lastDownloadedFilename = $this->getConfig('last_downloaded_file_name');

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $form['recent_download'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('The Most Recently Downloaded File'),
    ];

    $form['recent_download']['last_downloaded_file'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $lastDownloadedFilename . '</p>',
    ];

    $form['sftp_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('SFTP Settings'),
    ];

    $form['sftp_settings']['sftp_host'] = [
      '#type' => 'textfield',
      '#title' => t('sFTP Host'),
      '#maxlength' => 120,
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('sftp_host', $form_state, '199.115.148.13'),
    ];

    $form['sftp_settings']['sftp_username'] = [
      '#type' => 'textfield',
      '#title' => t('sFTP Username'),
      '#maxlength' => 120,
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('sftp_username', $form_state, 'StiboAcquiaHTUS'),
    ];

    $form['sftp_settings']['sftp_password'] = [
      '#type' => 'textfield',
      '#title' => t('sFTP Password'),
      '#maxlength' => 120,
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('sftp_password', $form_state, 'NRXI37rh'),
    ];

    $form['sftp_settings']['sftp_directory'] = [
      '#type' => 'textfield',
      '#title' => t('sFTP Directory'),
      '#maxlength' => 512,
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('sftp_directory', $form_state, '/NA GW/Full'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (!empty($values['sftp_host'])) {
      $this->setConfig('sftp_host', $values['sftp_host']);
    }

    if (!empty($values['sftp_username'])) {
      $this->setConfig('sftp_username', $values['sftp_username']);
    }

    if (!empty($values['sftp_password'])) {
      $this->setConfig('sftp_password', $values['sftp_password']);
    }

    if (!empty($values['sftp_directory'])) {
      $this->setConfig('sftp_directory', $values['sftp_directory']);
    }

    \Drupal::messenger()->addStatus('Settings changed.');
  }

}
