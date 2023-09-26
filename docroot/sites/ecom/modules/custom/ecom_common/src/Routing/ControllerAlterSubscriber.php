<?php

namespace Drupal\ecom_common\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Overrides the SomeController class.
 */
class ControllerAlterSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override the original controller class with your custom controller class.
    if ($route = $collection->get('commerce_add_to_cart_link.page')) {
      $route->setDefault('_controller', '\Drupal\ecom_common\Controller\CartQuantityController::action');
    }
  }
}
