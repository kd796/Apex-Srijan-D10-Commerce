<?php

namespace Drupal\helpers\Plugin\Field\FieldWidget;

trait FloatLabelTrait {
  protected function isAdmin() {
    $route = \Drupal::routeMatch()->getRouteObject();

    return \Drupal::service('router.admin_context')->isAdminRoute($route);
  }
}
