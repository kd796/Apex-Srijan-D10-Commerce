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

    // Check if the URL is an asset URL based on file extension.
    $isAssetUrl = preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|eot|ttf)$/', strtok($current_url, '?'));

    if ($isAssetUrl) {
      return;
    }

    $is_user_whitelisted = $is_user_authenticated = $bypass_user = $is_url_whitelisted = FALSE;

    $is_user_authenticated = \Drupal::service('session')->get('pre_login_success', FALSE);
    $bypass_user = \Drupal::currentUser()->hasPermission('administer ecom azure');

    // Check if IP is whitelisted.
    if ($ecom_azure_config->get('ecom_address_list')) {
      $is_user_whitelisted = $this->userIsWhitelisted($ecom_azure_config);
    }
    // Check if page is whitelisted.
    $whitelisted_pages = $ecom_azure_config->get('ecom_page_whitelist');
    if ($whitelisted_pages) {
      foreach ($whitelisted_pages as $p_url) {
        $is_url_whitelisted = strpos($current_url, $p_url) !== FALSE;
        if ($is_url_whitelisted) {
          break;
        }
      }
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
