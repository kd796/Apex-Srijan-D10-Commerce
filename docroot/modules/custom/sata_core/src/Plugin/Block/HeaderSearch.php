<?php

namespace Drupal\sata_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a search block for the header.
 *
 * @Block(
 *   id = "header_search",
 *   admin_label = @Translation("Header Search Block"),
 *   category = @Translation("sata Core")
 * )
 */
class HeaderSearch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /*
     * This doesn't actually do anything but for some reason passing a value
     * here fixes it so that the search icon actually shows up.
     */
    return [
      '#title' => 'Search',
      '#theme' => 'block--header-search',
    ];
  }

}
