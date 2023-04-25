<?php

/**
 * @file
 * Contains Drupal\step\Form\StepSettingsForm.
 */
namespace Drupal\step\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\step\Utils\StepHelper;

use Drupal\step\Controller\StepController;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StepSettingsForm extends ConfigFormBase
{
    /**
     * @var mixed
     */
    protected $locale;

    public function getFormId()
    {
        return 'step_settings';
    }

    protected function getEditableConfigNames()
    {
        return ['step.settings'];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * https://api.drupal.org/api/drupal/elements/8.2.x
     * https://api.drupal.org/api/drupal/developer%21topics%21forms_api_reference.html/7.x
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $this->locale = StepHelper::getCurrentSite()['code'];

        // Initialise the config variable.
        // step.settings is the module's configuration name, so this will load the admin settings.
        $config = $this->config('step.settings');

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
         * Locale http://localhost:9200
         */
        $form['step_xml'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => 'step-container'
            ]
        ];

        $form['step_xml']['step_xml_' . $this->locale] = [
            '#type'              => 'managed_file',
            '#name'              => 'step_xml',
            '#title'             => $this->t('STEP XML'),
            '#size'              => '50',
            '#description'       => $this->t('Upload STEP XML.'),
            '#upload_validators' => [
                'file_validate_extensions' => ['xml']
            ],
            '#upload_location'   => 'public://step',
            '#default_value'     => $config->get('step_xml_' . $this->locale)
        ];

        /**
         * Indices
         */
        $esIndices = Drupal::service('step.elastic_search_service')->getElasticsearchIndices();

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
            '#title' => $this->t('Create ElasticSearch indices from the uploaded STEP XML.')
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
         * Create ElasticSearch Indices
         */
        // $form['step_translations'] = [
        //     '#type'       => 'container',
        //     '#attributes' => [
        //         'class' => 'step-container'
        //     ]
        // ];
        // $form['step_translations']['label'] = [
        //     '#type'  => 'label',
        //     '#title' => $this->t('Create/Update English to German translations from the uploaded STEP XML.')
        // ];
        // $form['step_translations']['descr'] = [
        //     '#plain_text' => $this->t('Pellentesque gravida, dui at interdum sodales, urna mauris facilisis metus, et aliquam erat metus a neque.'),
        //     '#prefix'     => '<div>',
        //     '#suffix'     => '</div>'
        // ];
        // $form['step_translations']['form_container']['step_es_translate_submit'] = [
        //     '#type'     => 'submit',
        //     '#value'    => $this->t('Update STEP translations'),
        //     '#validate' => ['::validateFormIndexDocuments'],
        //     '#submit'   => ['::submitFormIndexDocuments']
        // ];

        /**
         * Re-index all Drupal nodes
         */
        $form['step_es_node_create'] = [
            '#type'       => 'container',
            '#attributes' => [
                'class' => 'step-container'
            ]
        ];

        $form['step_es_node_create']['label'] = [
            '#type'  => 'label',
            '#title' => $this->t('Index all Drupal nodes.')
        ];
        $form['step_es_node_create']['descr'] = [
            '#plain_text' => $this->t('This should only be done on the initial import or if you need to rebuild the entire index.'),
            '#prefix'     => '<div>',
            '#suffix'     => '</div>'
        ];

        $form['step_es_node_create']['form_container']['step_es_drupal_create_submit'] = [
            '#type'   => 'submit',
            '#value'  => $this->t('Create Drupal Node Index'),
            '#submit' => ['::submitFormIndexDrupalDocuments']
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
        $config = $this->config('step.settings');

        parent::submitForm($form, $form_state);

        $this->config('step.settings')
             ->set('step_es_hosts', $form_state->getValue('step_es_hosts'))
             ->set('step_xml_' . $this->locale, $form_state->getValue('step_xml_' . $this->locale))
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
        Drupal::service('step.step_service')->indexBulk();
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function submitFormIndexDrupalDocuments(array &$form, FormStateInterface $form_state)
    {
        Drupal::service('step.drupal_node_service')->indexDocuments(true);
    }

    // Validation methods
    // =========================================================================

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     */
    public function validateFormIndexDocuments(array &$form, FormStateInterface $form_state)
    {
        $locale = StepHelper::getCurrentSite()['code'];

        if (empty($form_state->getValue('step_xml_' . $locale))) {
            $error = 'You must upload the initail STEP XML file.' . PHP_EOL;
            $name  = 'step_xml_' . $locale;
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
