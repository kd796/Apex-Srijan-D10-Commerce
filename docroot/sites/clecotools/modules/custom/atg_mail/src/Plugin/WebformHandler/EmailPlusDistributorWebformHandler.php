<?php

namespace Drupal\atg_mail\Plugin\WebformHandler;

use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "email_plus_distributor",
 *   label = @Translation("Email (Plus Distributor)"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission via email, appending the distributor's email to the recipients, if the submitted distributor has an email address associated with it."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class EmailPlusDistributorWebformHandler extends EmailWebformHandler
{
    /**
     * Set "To Email" in Webform Handlers to a default email address
     */
    public function sendMessage(WebformSubmissionInterface $webform_submission, array $message)
    {
        $distributor_id = $webform_submission->getElementData('distributor_id');
        if ($distributor_id) {
            $distributor = Node::load($distributor_id);
            if ($distributor) {
                $emails = $distributor->get('field_email')->getValue();
                foreach ($emails as $address) {
                    if (filter_var($address['value'], FILTER_VALIDATE_EMAIL)) {
                        $email[] = $address['value'];
                    }
                }
                if (isset($email)) {
                    $message['to_mail'] .= sprintf(', %s', implode(', ', $email));
                }
            }
        }

        return parent::sendMessage($webform_submission, $message);
    }
}
