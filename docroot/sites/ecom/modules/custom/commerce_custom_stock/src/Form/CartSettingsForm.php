<?php

namespace Drupal\commerce_custom_stock\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the admin form for editing cart settings.
 */
class CartSettingsForm extends ConfigFormBase {

  /**
   * Constructs a AdminSettingsForm object.
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
    return 'commerce_cart_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'commerce_custom_stock.cart.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_custom_stock.cart.settings');

    $form['mininum_cart_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum cart price'),
      '#description' => $this->t('If specified, the cart should have minimum cart price applied.'),
      '#default_value' => $config->get('mininum_cart_value'),
    ];

    $form['currency_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency code'),
      '#description' => $this->t('Specify the currency code which showed with minimum price number.'),
      '#default_value' => $config->get('currency_code'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('commerce_custom_stock.cart.settings')
      ->set('mininum_cart_value', $form_state->getValue('mininum_cart_value'))
      ->set('currency_code', $form_state->getValue('currency_code'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
