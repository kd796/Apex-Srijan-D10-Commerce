<?php

namespace Drupal\apex_tools_custom_quotation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Class CsqwSettingsForm.
*
* @package Drupal\apex_tools_custom_quotation\Form
*/
class CsqwSettingsForm extends FormBase {
    
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
    return 'csqw_admin_settings_form';
  }
    
  /**
   * {@inheritdoc}
   */
  protected function getConfig(string $name) {
    $value = $this->config('apex_tools_custom_quotation.csqw_settings')->get($name);
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
    $this->configFactory->getEditable('apex_tools_custom_quotation.csqw_settings')->set($name, $value)->save();
  }

  /**
   * {@inheritdoc}
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
    $config = $this->config('apex_tools_custom_quotation.csqw_settings');

    $form['csqwsettings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('CSQW Settings'),
    ];
    $form['csqwsettings']['salesforce_oid'] = [
      '#type' => 'textfield',
      '#title' => t('OID:'),
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('salesforce_oid', $form_state, ''),
    ];
    $form['csqwsettings']['salesforce_return_url'] = [
      '#type' => 'textfield',
      '#title' => t('Return URL:'),
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('salesforce_return_url', $form_state, ''),
    ];
    $form['csqwsettings']['salesforce_debugEmail'] = [
      '#type' => 'email',
      '#title' => t('Debug Email ID:'),
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('salesforce_debugEmail', $form_state, ''),
    ];
    $form['csqwsettings']['salesforce_url'] = [
      '#type' => 'textfield',
      '#title' => t('Salseforce URL:'),
      '#required' => TRUE,
      '#value' => $this->getValueOrConfig('salesforce_url', $form_state, ''),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    
    return $form;
  }
    
  /**
    * {@inheritdoc}
    */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    
    if (!empty($values['salesforce_oid'])) {
      $this->setConfig('salesforce_oid', $values['salesforce_oid']);
    }
    if (!empty($values['salesforce_return_url'])) {
      $this->setConfig('salesforce_return_url', $values['salesforce_return_url']);
    }
    if (!empty($values['salesforce_debugEmail'])) {
      $this->setConfig('salesforce_debugEmail', $values['salesforce_debugEmail']);
    }
    if (!empty($values['salesforce_url'])) {
      $this->setConfig('salesforce_url', $values['salesforce_url']);
    }
    
    \Drupal::messenger()->addStatus('Settings saved.');
  }
    
}