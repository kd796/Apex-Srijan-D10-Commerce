<?php

namespace Drupal\crescenttool_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to launch initial setting of default listing image field for current product content.
 */
class SetDefaultListingImage extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crescenttool_core_set_default_listing_image';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['crescenttool_core_set_default_listing_image.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('crescenttool_core.set_default_listing_image_process');
    parent::submitForm($form, $form_state);
  }

}
