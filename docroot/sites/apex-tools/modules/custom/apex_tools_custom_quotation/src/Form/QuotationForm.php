<?php

namespace Drupal\apex_tools_custom_quotation\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Form controller for the quotation entity edit forms.
 */
class QuotationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();
    $quote_id = $this->entity->label();

    $message_arguments = ['%label' => $quote_id];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New quotation %label has been created.', $message_arguments));
      $this->logger('apex_tools_custom_quotation')->notice('Created new quotation %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The quotation %label has been updated.', $message_arguments));
      $this->logger('apex_tools_custom_quotation')->notice('Updated new quotation %label.', $logger_arguments);
    }

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'apex_tools_custom_quotation';
    $key = 'cust_solution_mail';
    $to = 'supriya.deshpande@srijan.net';

    $params['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed; delsp=yes';
    $params['headers']['MIME-Version'] = '1.0';
    $message = '<html><body>';
    $message .= '<p style="font-size:11px;">Please see attached lead information for a new Custom Solution by Apex Fastening.</p>';
    $message .= '<a href=/print/view/pdf/csqw/csqw_block?view_args[]='.$quote_id.'>Download Here!</a>';
    $message .= '</body></html>';
    $params = [
      'subject' => 'A New Apex Custom Solutions Lead Enclosed.',
      'body' => $message,
    ];

    $langcode = 'en';
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    $s_request = $this->salseforceRequest($entity);

    $form_state->setRedirect('apex_tools_custom_quotation.quote_submit_page', ['quotation' => $quote_id]);
  }


  /**
   * {@inheritdoc}
   */
  public function salseforceRequest($entity) {

    $config = \Drupal::config('csqw_config.admin_settings');
    $salseforce_url = $config->get('salesforce_url');

    $parameters = [
      'oid' => $config->get('salesforce_oid'),
      'debug' => 1,
      'debugEmail' => $config->get('salesforce_debugEmail'),
      'first_name' => $entity->get('first_name')->getValue()[0]['value'],
      'last_name' => $entity->get('last_name')->getValue()[0]['value'],
      'email' => $entity->get('email_address')->getValue()[0]['value'],
      'company' => $entity->get('company_name')->getValue()[0]['value'],
      'city' => $entity->get('field_city')->getValue()[0]['value'],
      'state' => $entity->get('field_state_text')->getValue()[0]['value'],
    ];

    foreach ($parameters as $key => $value) {
      $params[] = stripslashes($key)."=".stripslashes($value);
     }

     $query_string = join("&", $params);


  // create a new cURL resource
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $salseforce_url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);

   //Set some settings that make it all work :)
  curl_setopt($ch, CURLOPT_HEADER, FALSE);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

  //Execute SalesForce web to lead PHP cURL
  $result = curl_exec($ch);

  //close cURL connection
  curl_close($ch);

  return;

  }
}
