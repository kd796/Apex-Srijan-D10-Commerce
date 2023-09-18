<?php

namespace Drupal\ecom_common\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomRouteSubscriber implements EventSubscriberInterface {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructor for CustomRouteSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match service.
   */
  public function __construct(AccountInterface $currentUser, RouteMatchInterface $routeMatch) {
    $this->currentUser = $currentUser;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => 'checkAuthStatus',
    ];
  }

  public function checkAuthStatus(ResponseEvent $event) {
    if ($this->currentUser->isAuthenticated() && $this->routeMatch->getRouteName() == 'entity.user.canonical') {
      $uid = $this->currentUser->id();
      $response = new RedirectResponse('/user/' . $uid . '/account-dashboard', 302);
      $event->setResponse($response);
      $event->stopPropagation();
    }
  }
}
