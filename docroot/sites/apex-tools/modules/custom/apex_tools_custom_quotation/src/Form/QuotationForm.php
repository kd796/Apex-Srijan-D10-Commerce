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
    $message .= '<a href=/print/pdf/quotation/'.$quote_id.'>Download Here!</a>';
    $message .= '</body></html>';
    $params = [
      'subject' => 'A New Apex Custom Solutions Lead Enclosed.',
      'body' => $message,
    ];

    $langcode = 'en';
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    $form_state->setRedirect('apex_tools_custom_quotation.quote_submit_page', ['quotation' => $quote_id]);
  }
}
