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
    $ecom_azure_config = $configFactory->get('ecom_azure.settings') ?: [];
    if (\Drupal::currentUser()->isAnonymous() && \Drupal::routeMatch()->getRouteName() != 'user.login') {
      if (!in_array(\Drupal::request()->getClientIp(), $ecom_azure_config->get('ecom_address_list')) &&
        \Drupal::service('session')->get('pre_login_success', FALSE) == FALSE) {
        // Check if the pre_login_success flag is set in the session.
        $redirect_path = '/user/login';
        $current_path = \Drupal::service('path.current')->getPath();
        $destination = ['query' => ['destination' => $current_path]];
        if ($current_path !== $redirect_path) {
          $response = new TrustedRedirectResponse(Url::fromRoute('user.login', [], $destination)
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
