<?php

namespace Drupal\gearwrench_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a country switch block.
 *
 * @Block(
 *   id = "country_switch",
 *   admin_label = @Translation("Country Switch Block"),
 *   category = @Translation("Gearwrench Core")
 * )
 */
class CountrySwitch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $australia_url = 'http://www.gearwrench.com.au/';
    $mexico_url = 'http://www.gearwrench.com.mx/';

    return [
      'australia_url' => $australia_url,
      'mexico_url' => $mexico_url,
    ];
  }

}
