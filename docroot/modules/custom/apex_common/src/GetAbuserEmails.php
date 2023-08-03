<?php

namespace Drupal\apex_common;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Get all the abusers email by submitting the form id.
 */
class GetAbuserEmails {

  /**
   * Protected entityTypeManager variable.
   *
   * @var entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get the abuser emails.
   */
  public function getAbuserEmails($web_form_id) {
    // Get Warranty Abusers Form submissions.
    $query = $this->entityTypeManager->getStorage('webform_submission')->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('webform_id', $web_form_id);
    $result = $query->execute();

    $storage = $this->entityTypeManager->getStorage('webform_submission');
    $submissions = $storage->loadMultiple($result);
    $abuser_emails = [];
    foreach ($submissions as $submission) {
      foreach ($submission->getData() as $key => $item) {
        if ($key == 'email') {
          $abuser_emails[] = $item;
        }
      }
    }
    return $abuser_emails;
  }

}
