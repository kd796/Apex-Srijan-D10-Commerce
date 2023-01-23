<?php

namespace Drupal\atg_translation\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class TranslatedFrontPageConfigForm extends ConfigFormBase {

    public $key = 'atg_translation.settings';

    public function getFormId() {
        return 'translated_front_page_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildForm($form, $form_state);

        $config = $this->config($this->key);

        $form['front_page'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Front page'),
        ];

        foreach ($this->getLanguages() as $key => $language) {
            /** @var \Drupal\Core\Language\Language $language */

            $configValue = $config->get(sprintf('front_page.%s', $key));
            if ( ! $configValue && $language->isDefault()) {
                $configValue = $this->config('system.site')->get('page.front');
            }

            $form['front_page'][$key] = [
                '#type'          => 'textfield',
                '#title'         => $language->getName(),
                '#default_value' => $configValue,
                '#required'      => TRUE,
                '#description'   => $language->isDefault() ? $this->t('Default language') : null,
            ];
        }

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config($this->key);
        foreach ($this->getLanguages() as $key => $language) {
            $config->set(sprintf('front_page.%s', $key), $form_state->getValue($key));
        }
        $config->save();

        parent::submitForm($form, $form_state);
    }

    protected function getEditableConfigNames() {
        return [
            $this->key,
        ];
    }

    protected function getLanguages() {
        return Drupal::languageManager()->getLanguages();
    }
}
