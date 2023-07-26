<?php

namespace Drupal\apex_tools_custom_quotation\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Url;

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

    // Get mail id to send the mail.
    $config = \Drupal::config('apex_tools_custom_quotation.csqw_settings');
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($language == 'en') {
      $to = $config->get('lead_mail_us');
    }
    if ($language == 'uk' || $language == 'de') {
      $to = $config->get('lead_mail_uk_de');
    }
    $params['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed; delsp=yes';
    $params['headers']['MIME-Version'] = '1.0';
    $message = 'Please see attached lead information for a new Custom Solution by Apex Fastening.';
    $params = [
      'subject' => 'A New Apex Custom Solutions Lead Enclosed.',
      'body' => $message,
    ];
    // Generate the PDF.
    $print_engine = \Drupal::service('plugin.manager.entity_print.print_engine')->createSelectedInstance('pdf');
    $print_builder = \Drupal::service('entity_print.print_builder');
    $filename = 'CSQW_' . rand() . '.pdf';
    $uri = $print_builder->savePrintable(
      [$entity],
      $print_engine,
      'public',
      $filename,
    );

    $quote_form_file = array(
      'filepath' => $uri,
      'filename' => $filename,
      'filemime' => 'application/pdf'
    );

    $params['attachments'][] = $quote_form_file;
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $language, $params, NULL, $send);

    $s_request = $this->salseforceRequest($entity);

    $file_path = \Drupal::service('file_url_generator')->generateString($uri);
    $redirect_url = Url::fromRoute('apex_tools_custom_quotation.quote_submit_page',
    [
      'quotation' => $file_path,
    ],
    [
      'absolute' => TRUE,
    ]);

    \Drupal::service('request_stack')->getCurrentRequest()->query->set('destination', $redirect_url->toString());

  }

  /**
   * {@inheritdoc}
   */
  public function salseforceRequest($entity) {

    $config = \Drupal::config('apex_tools_custom_quotation.csqw_settings');
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

    $client = \Drupal::httpClient();
    $response = $client->request('POST', $salseforce_url, [
      'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
      'form_params' => $parameters,
      'verify' => FALSE,
    ]);
    $status = $response->getStatusCode();

    return $status;

  }

}
