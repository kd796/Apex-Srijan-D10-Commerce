<?php

namespace Drupal\atg_mailing_list\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\webform\Entity\Webform;

class MailingListSettingsForm extends ConfigFormBase
{

  const FORM_ID = 'atg_mailing_list_settings';
  const SETTINGS = 'atg_mailing_list.settings';

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

    $form['atg_mailing_list_enable'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enable mailing list widget'),
      '#default_value' => 1,
      '#input'         => TRUE,
      '#return_value'  => 1,
      '#description'   => t('Uncheck this box to remove the mailing list widget site-wide.'),
    ];

    $form['atg_mailing_list_title'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Widget Title'),
      '#default_value' => $config->get('atg_mailing_list_title'), // 'Subscribe to Newsletter'
    ];

    $query = \Drupal::entityQuery('webform')
      ->condition('status', 'open');
    $entity_ids = $query->execute();

    $webform_ids = [];
    foreach ($entity_ids as $webid) {
      $webform_ids[] = $webid;
    }

    $options = [];
    $webforms = Webform::loadMultiple($webform_ids);
    foreach ($webforms as $webform) {
      $options[$webform->id()] = $webform->get('title');
    }

    // sort webforms by title
    asort($options);

    $form['atg_mailing_list_form'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Form'),
      '#default_value' => $config->get('atg_mailing_list_form'), // 'email_list',
      '#options'       => $options,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);

    $this->config(self::SETTINGS)
      ->set('atg_mailing_list_enable', $form_state->getValue('atg_mailing_list_enable'))
      ->set('atg_mailing_list_title', $form_state->getValue('atg_mailing_list_title'))
      ->set('atg_mailing_list_form', $form_state->getValue('atg_mailing_list_form'))
      ->save();
  }
}
