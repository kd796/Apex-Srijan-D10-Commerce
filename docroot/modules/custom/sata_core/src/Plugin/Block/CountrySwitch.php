<?php

namespace Drupal\sata_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a country switch block.
 *
 * @Block(
 *   id = "country_switch",
 *   admin_label = @Translation("Country Switch Block"),
 *   category = @Translation("SATA Core")
 * )
 */
class CountrySwitch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $brazil_url = 'https://sataferramentas.com.br';
    $colombia_url = 'https://sata.com.co';
    $emea_url = 'https://satatools.eu';
    $us_url = 'https://satatools.us';

    return [
      'brazil_url' => $brazil_url,
      'colombia_url' => $colombia_url,
      'emea_url' => $emea_url,
      'us_url' => $us_url,
    ];
  }

}
