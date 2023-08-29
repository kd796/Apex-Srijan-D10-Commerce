<?php

namespace Drupal\ecom_azure_api\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber class to manage anonymous redirection.
 */
class CheckUserLogin implements EventSubscriberInterface {

  /**
   * Manage anonymous redirection.
   */
  public function redirectAnonymous(RequestEvent $event) {
    $configFactory = \Drupal::service('config.factory');
    $ecom_azure_config = $configFactory->get('ecom_azure_api.settings') ?: [];
    if (empty($ecom_azure_config)) {
      return;
    }
    $path_alias_manager = \Drupal::service('path_alias.manager');
    $current_path = \Drupal::service('path.current')->getPath();
    $path_alias = $path_alias_manager->getAliasByPath($current_path);
    $current_url = Url::fromUserInput($path_alias)->toString();
    $current_route = \Drupal::routeMatch()->getRouteName();
    $target_route = Url::fromRoute("ecom_azure_api.network_login")->getRouteName();

    $is_user_whitelisted = $is_user_authenticated = $bypass_user = $is_url_whitelisted = FALSE;

    $is_user_authenticated = \Drupal::service('session')->get('pre_login_success', FALSE);
    $bypass_user = \Drupal::currentUser()->hasPermission('administer ecom azure');

    if ($ecom_azure_config->get('ecom_address_list')) {
      $is_user_whitelisted = $this->userIsWhitelisted($ecom_azure_config);
    }
    if ($ecom_azure_config->get('ecom_page_whitelist')) {
      $is_url_whitelisted = in_array($current_url, $ecom_azure_config->get('ecom_page_whitelist'));
    }
    if (!$is_user_whitelisted && !$is_user_authenticated && !$bypass_user && !$is_url_whitelisted &&
     PHP_SAPI != 'cli') {
      $destination = ['query' => ['destination' => $current_url]];

      if ($current_route === $target_route) {
        return;
      }

      $response = new TrustedRedirectResponse(Url::fromRoute($target_route, [], $destination)
        ->toString());
      $response->send();

      return;
    }
    elseif ($is_user_whitelisted || $is_user_authenticated || $bypass_user || $is_url_whitelisted) {
      if ($current_route == $target_route) {
        $response = new TrustedRedirectResponse(Url::fromRoute("<front>")->toString());
        $response->send();
      }
    }
  }

  /**
   * Get if user IP is whitelisted.
   */
  public function userIsWhitelisted($config) {
    // If user IP is not set in the address_list.
    return in_array(\Drupal::request()->getClientIp(), $config->get('ecom_address_list')) ||
     $config->get('ecom_user_ip_whitelisted');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['redirectAnonymous'];
    return $events;
  }

}
