<?php

namespace Drupal\apex_common\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform submission handler.
 *
 * @WebformHandler(
 *   id = "WarrantyAbuserHandler",
 *   label = @Translation("WarrantyAbuserHandler"),
 *   category = @Translation("WarrantyAbuserHandler"),
 *   description = @Translation("Cross check the submissions with abuser list provided in warranty_abusers form and prevent the submission"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class WarrantyAbuserHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $web_form_id = 'warranty_abusers';
    $service = \Drupal::service('apex_common.get_abuser_emails');
    $abuser_emails = $service->getAbuserEmails($web_form_id);

    // Get warranty_replacement_form submission data.
    $data = $webform_submission->getData();
    if (!empty($abuser_emails) && in_array($data['email_address'], $abuser_emails)) {
      $form_state->setErrorByName('element', $this->t('User is blocked. Please contact our live support staff directly at 1.866.907.9716 Monday-Friday 9am-5pm ET.'));
    }
  }

}
