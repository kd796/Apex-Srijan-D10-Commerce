<?php

namespace Drupal\apex_tools_custom_quotation\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

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

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New quotation %label has been created.', $message_arguments));
      $this->logger('apex_tools_custom_quotation')->notice('Created new quotation %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The quotation %label has been updated.', $message_arguments));
      $this->logger('apex_tools_custom_quotation')->notice('Updated new quotation %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.quotation.canonical', ['quotation' => $entity->id()]);
  }

}
