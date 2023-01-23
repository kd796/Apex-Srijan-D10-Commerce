<?php

namespace Drupal\examples\Controller;

use Drupal\Core\Controller\ControllerBase;

class ExamplesController extends ControllerBase {
  public function examples($example = null) {
    return [
      '#theme' => 'examples',
      '#title' => $example ? ucwords($example) : 'Examples',
      '#example' => $example,
    ];
  }
}
