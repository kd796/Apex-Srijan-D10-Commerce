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
    $united_states_url = 'https://www.gearwrench.com';
    $australia_url = 'https://www.gearwrench.com.au/';
    $mexico_url = 'https://www.gearwrench.com.mx/';

    return [
      'united_states_url' => $united_states_url,
      'australia_url' => $australia_url,
      'mexico_url' => $mexico_url,
    ];
  }

}
