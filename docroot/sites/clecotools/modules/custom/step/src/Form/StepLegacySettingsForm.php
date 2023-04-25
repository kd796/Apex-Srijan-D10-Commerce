<?php

/**
 * @file
 * Contains Drupal\step\Form\StepLegacySettingsForm.
 */
namespace Drupal\step\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\step\Utils\StepHelper;

use Drupal\step\Controller\StepController;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StepLegacySettingsForm extends ConfigFormBase
{
    public function getFormId()
    {
        return 'step_settings_legacy_documents';
    }

    protected function getEditableConfigNames()
    {
        return ['step.settings.legacy_documents'];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('step.settings.legacy_documents');

        $form = [
            '#attributes' => ['enctype' => 'multipart/form-data']
        ];

        /**
         * Hosts
         */
        $form['step_es_hosts'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => 'step-container'
            ]
        ];

        $form['step_es_hosts']['step_es_hosts'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('ElasticSearch Hosts'),
            '#description'   => $this->t('List of indices created from the STEP XML import.'),
            '#default_value' => $config->get('step_es_hosts')
        ];

        /**
         * XML Upload
         * Local http://localhost:9200
         */
        $form['step_xml'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => 'step-container'
            ]
        ];

        $form['step_xml']['step_legacy_xml'] = [
            '#type'              => 'managed_file',
            '#name'              => 'step_xml',
            '#title'             => $this->t('STEP XML'),
            '#size'              => '50',
            '#description'       => $this->t('Upload STEP Legacy XML.'),
            '#upload_validators' => [
                'file_validate_extensions' => ['xml']
            ],
            '#upload_location'   => 'public://step',
            '#default_value'     => $config->get('step_legacy_xml')
        ];

        /**
         * Indices
         */
        $esIndices = Drupal::service('step.elastic_search_service')->getElasticsearchIndices('cleco_legacy_documents');

        $form['step_es_indices'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => 'step-container'
            ]
        ];

        $form['step_es_indices']['table'] = [
            '#type'    => 'table',
            '#caption' => '',
            '#header'  => [
                $this->t('ElasticSearch Indice')
            ]
        ];

        if (!$esIndices) {
            $form['step_es_indices']['table'][0]['name'] = [
                '#plain_text' => 'Currently no ElasticSearch indices exist.'
            ];
        } else {

            for ($i = 0; $i < count($esIndices); $i++) {
                $form['step_es_indices']['table'][$i]['name'] = [
                    '#plain_text' => $esIndices[$i]
                ];
            }
        }

        /**
         * Create ElasticSearch Indices
         */
        $form['step_es_create'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => 'step-container'
            ]
        ];

        $form['step_es_create']['label'] = [
            '#type'  => 'label',
            '#title' => $this->t('Create ElasticSearch indices from the uploaded Legacy STEP XML.')
        ];
        $form['step_es_create']['descr'] = [
            '#plain_text' => $this->t('This should only be done on the initial import or if you need to rebuild the entire index.'),
            '#prefix'     => '<div>',
            '#suffix'     => '</div>'
        ];

        $form['step_es_create']['form_container']['step_es_create_submit'] = [
            '#type'     => 'submit',
            '#value'    => $this->t('Create STEP Indices'),
            '#validate' => ['::validateFormIndexDocuments'],
            '#submit'   => ['::submitFormIndexDocuments']
        ];

        /**
         * Delete ES Indices
         */
        if ($esIndices) {
            $form['step_es_delete'] = [
                '#type'       => 'container',
                '#attributes' => [
                    'class' => 'step-container'
                ]
            ];

            $form['step_es_delete']['label'] = [
                '#type'       => 'label',
                '#title'      => $this->t('Select the ElasticSearch Index to delete. Action cannot be undone.'),
                '#attributes' => ['style' => 'color: red;']
            ];

            $form['step_es_delete']['container'] = [
                '#type'       => 'container',
                '#attributes' => [
                    'class' => 'step-es-delete'
                ]
            ];

            $form['step_es_delete']['container']['step_es_delete_select'] = [
                '#type'    => 'select',
                '#options' => [
                    '' => $this->t('Select ES Index')
                ]
            ];

            for ($i = 0; $i < count($esIndices); $i++) {
                $form['step_es_delete']['container']['step_es_delete_select']['#options'][$esIndices[$i]] = $esIndices[$i];
            }

            $form['step_es_delete']['container']['step_es_delete_submit'] = [
                '#type'     => 'submit',
                '#value'    => $this->t('Delete Index'),
                '#validate' => ['::validateFormDeleteIndex'],
                '#submit'   => ['::submitFormDeleteIndex']
            ];
        }

        return parent::buildForm($form, $form_state);
    }

    // Submit methods
    // =========================================================================

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $config = $this->config('step.settings.legacy_documents');

        parent::submitForm($form, $form_state);

        $config
            ->set('step_es_hosts', $form_state->getValue('step_es_hosts'))
            ->set('step_legacy_xml', $form_state->getValue('step_legacy_xml'))
            ->set('step_es_indices', $form_state->getValue('step_es_indices'))
            ->save();
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function submitFormDeleteIndex(array &$form, FormStateInterface $form_state)
    {
        $index = $form_state->getValue('step_es_delete_select');

        Drupal::service('step.elastic_search_service')->deleteIndex($index);
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function submitFormIndexDocuments(array &$form, FormStateInterface $form_state)
    {
        Drupal::service('step.step_legacy_service')->indexBulk();
    }

    // Validation methods
    // =========================================================================

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function validateFormIndexDocuments(array &$form, FormStateInterface $form_state)
    {
        if (empty($form_state->getValue('step_legacy_xml'))) {
            $error = 'You must upload the initail Legacy XML file.' . PHP_EOL;
            $name  = 'step_legacy_xml';
        }

        if (empty($form_state->getValue('step_es_hosts'))) {
            $error = 'You must enter a ES host endpoint.';
            $name  = 'step_es_hosts';
        }

        if (isset($error)) {
            $form_state->setErrorByName($name, $this->t($error));
        }
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function validateFormDeleteIndex(array &$form, FormStateInterface $form_state)
    {
        if (empty($form_state->getValue('step_es_delete_select'))) {
            $form_state->setErrorByName('step_es_delete_select', $this->t('You must select an Index to delete.'));
        }
    }
}
