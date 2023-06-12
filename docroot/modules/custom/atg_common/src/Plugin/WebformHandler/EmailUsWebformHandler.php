<?php

namespace Drupal\atg_common\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "local_email",
 *   label = @Translation("Local email"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission to a different email address per content type."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class EmailUsWebformHandler extends EmailWebformHandler {

  /**
   * {@inheritdoc}
   */
  public function sendMessage(WebformSubmissionInterface $webform_submission, array $message) {

    $data = $webform_submission->getData();
    $webform_id = $webform_submission->getWebform()->id();

    // Update email recipient only for Email Us form.
    if ($webform_id == "contact_us") {
      /** @var \Drupal\Core\Entity\EntityStorageInterface $node_storage */
      $storage = \Drupal::entityTypeManager()->getStorage('node');
      if ($data['country'] == 'US') {
        $nodes = $storage->loadByProperties([
          'type' => 'email_us',
          'field_form_type' => $data['select_type'],
          'field_country_code' => $data['country'],
          'field_state_code' => $data['state_province'],
        ]);
      }
      else {
        $nodes = $storage->loadByProperties([
          'type' => 'email_us',
          'field_form_type' => $data['select_type'],
          'field_country_code' => $data['country'],
        ]);
      }

      $node = array_pop($nodes);
      // Check the related mail id else send mail to the site mail address.
      $email = !empty($node) ? $node->get('field_atg_email')->getValue()[0]['value'] : \Drupal::config('system.site')->get('mail');

      $message['to_mail'] = $email;
    }

    return parent::sendMessage($webform_submission, $message);
  }

}
