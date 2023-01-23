<?php

namespace Drupal\atg_cleverreach\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CleverReachSettingsForm extends ConfigFormBase
{

    const FORM_ID = 'atg_cleverreach_settings';

    public function getFormId()
    {
        return self::FORM_ID;
    }

    protected function getEditableConfigNames()
    {
        return [
            'atg_cleverreach.settings'
        ];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('atg_cleverreach.settings');

        $form['cleverreach_client_id'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('CleverReach API Client ID'),
            '#default_value' => $config->get('cleverreach_client_id')
        ];

        $form['cleverreach_client_secret'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('CleverReach API Secret'),
            '#default_value' => $config->get('cleverreach_client_secret')
        ];

        $form['cleverreach_api_endpoint'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('CleverReach API Endpoint'),
            '#default_value' => $config->get('cleverreach_api_endpoint')
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $config = $this->config('atg_cleverreach.settings');

        parent::submitForm($form, $form_state);

        $this->config('atg_cleverreach.settings')
             ->set('cleverreach_client_id', $form_state->getValue('cleverreach_client_id'))
             ->set('cleverreach_client_secret', $form_state->getValue('cleverreach_client_secret'))
             ->set('cleverreach_api_endpoint', $form_state->getValue('cleverreach_api_endpoint'))
             ->save();
    }
}
