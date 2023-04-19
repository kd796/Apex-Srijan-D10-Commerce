<?php

namespace Drupal\campbell_common;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twigextention for url type checking.
 */
class CampbellUtilityTwigExtension extends AbstractExtension {

  /**
   * Creates twig function for URL type checking.
   */
  public function getFunctions() {
    return [
      new TwigFunction('is_external_url', [$this, 'isExternalUrl']),
    ];
  }

  /**
   * Used to check the entered url is external or internal.
   */
  public function isExternalUrl($url) {

    return preg_match('/http(s?)\:\/\//i', $url) ? 1 : 0;
  }

}
