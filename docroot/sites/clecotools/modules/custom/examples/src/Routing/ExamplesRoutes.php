<?php

namespace Drupal\examples\Routing;

use Symfony\Component\Routing\Route;

class ExamplesRoutes {
  public function routes() {
    $routes = [];

    $routes['examples'] = new Route('/examples', [
      '_controller' => '\Drupal\examples\Controller\ExamplesController::examples',
    ], [
      '_permission' => 'access content',
    ]);

    $routes['examples.example'] = new Route('/examples/{example}', [
      '_controller' => '\Drupal\examples\Controller\ExamplesController::examples',
    ], [
      '_permission' => 'access content',
    ]);

    return $routes;
  }
}
