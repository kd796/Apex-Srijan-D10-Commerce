<?php

namespace Drupal\torque_conversion_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Plugin implementation of the torque conversion calculator block.
 *
 * @Block(
 *   id = "torque_conversion_calculator",
 *   admin_label = @Translation("Torque Conversion Calculator"),
 *   category = @Translation("Custom")
 * )
 */
class TorqueConversionCalculator extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'torque_conversion_calculator',
    ];
  }

}
