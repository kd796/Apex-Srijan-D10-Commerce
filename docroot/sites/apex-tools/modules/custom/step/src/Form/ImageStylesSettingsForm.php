<?php

/**
 * @file
 * Contains Drupal\step\Form\ImageStylesSettingsForm.
 */
namespace Drupal\step\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\step\Utils\StepHelper;

use Drupal\step\Controller\StepController;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImageStylesSettingsForm extends ConfigFormBase
{
    public function getFormId()
    {
        return 'step_settings_image_styles';
    }

    protected function getEditableConfigNames()
    {
        return ['step.settings.image_styles'];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['details'] = [
            '#markup' => 'Create image styles for STEP products and documents:<ul><li>Thumbnail</li><li>Product Zoom Image</li></ul><br>'
        ];

        $form['btn'] = [
            '#type'   => 'submit',
            '#value'  => $this->t('Create Image Styles'),
            '#submit' => ['::submitForm']
        ];

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
        Drupal::service('step.elastic_search_service')->createImageStyles();
    }
}
