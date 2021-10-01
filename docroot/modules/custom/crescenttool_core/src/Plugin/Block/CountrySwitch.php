<?php

namespace Drupal\crescenttool_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a country switch block block.
 *
 * @Block(
 *   id = "country_switch",
 *   admin_label = @Translation("Country Switch Block"),
 *   category = @Translation("Crescent Tool Core")
 * )
 */
class CountrySwitch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $australia_url = 'http://www.crescenttool.com.au/';
    $mexico_url = 'http://www.crescenttool.com.mx/';
    $eu_url = 'http://www.crescenttool.eu/';

    return [
      'australia_url' => $australia_url,
      'mexico_url' => $mexico_url,
      'eu_url' => $eu_url,
    ];
  }

}
