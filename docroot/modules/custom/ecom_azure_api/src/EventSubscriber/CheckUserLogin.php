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
    $route_name = \Drupal::routeMatch()->getRouteName();
    $is_user_whitelisted = $is_user_authenticated = $bypass_user = FALSE;
    if ($ecom_azure_config->get('ecom_address_list')) {
      $is_user_whitelisted = in_array(\Drupal::request()->getClientIp(), $ecom_azure_config->get('ecom_address_list'));
    }
    $is_user_authenticated = \Drupal::service('session')->get('pre_login_success', FALSE);
    $bypass_user = \Drupal::currentUser()->hasPermission('administer ecom azure');

    if (!$is_user_whitelisted && !$is_user_authenticated && !$bypass_user) {
      $redirect_path = Url::fromRoute("ecom_azure_api.network_login")->toString();
      $destination = ['query' => ['destination' => $current_url]];

      if ($current_url !== $redirect_path) {
        $response = new TrustedRedirectResponse(Url::fromRoute("ecom_azure_api.network_login", [], $destination)
          ->toString());
        $event->setResponse($response);
        $event->stopPropagation();
        $build = [
          '#cache' => [
            'max-age' => 0,
          ],
        ];
        $response->addCacheableDependency($build);
      }
    }
    elseif ($is_user_whitelisted || $is_user_authenticated || $bypass_user) {
      if ($route_name == "ecom_azure_api.network_login") {
        $response = new TrustedRedirectResponse(Url::fromRoute("<front>")->toString());
        $event->setResponse($response);
        $event->stopPropagation();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['redirectAnonymous'];
    return $events;
  }

}
