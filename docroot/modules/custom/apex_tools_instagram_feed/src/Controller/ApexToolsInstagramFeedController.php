<?php

namespace Drupal\apex_tools_instagram_feed\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Apex Tools Instagram Feed routes.
 */
class ApexToolsInstagramFeedController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $service_provider = \Drupal::service('apex_tools_instagram_feed.service_provider');
    $code = \Drupal::request()->query->get('code');
    if (!empty($code)) {
      $access_token = $service_provider->getAccessToken();
      if (!empty($access_token)) {
        $facebook_page_id = $service_provider->getFaceBookPageId();
        if (!empty($facebook_page_id)) {
          $instagram_account_id = $service_provider->getInstagramAccountId($facebook_page_id);
          if (!empty($instagram_account_id)) {
            $service_provider->getInstagramMediaItems();
          }
        }
      }
    }
    return $this->redirect('apex_tools_instagram_feed.settings_form');
  }

}
