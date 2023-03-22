<?php

namespace  Drupal\apex_common;

/**
 * @file providing the service that generate random number between 1000 to 9999.
 *
 */

class GetAbuserEmails{

    public function get_abuser_emails($web_form_id){
        //Get Warranty Abusers Form submissions
        $query = \Drupal::entityQuery('webform_submission')
            ->condition('webform_id', $web_form_id);
        $result = $query->execute();

        $storage = \Drupal::entityTypeManager()->getStorage('webform_submission');
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
