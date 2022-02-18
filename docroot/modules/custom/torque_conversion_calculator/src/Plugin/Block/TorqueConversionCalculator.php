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
    $build = [];
    $build['#attached']['library'][] = 'torque_conversion_calculator/torque_conversion_calculator';
    $build['output'] = [
      '#theme' => 'torque_conversion_calculator',
    ];
    return $build;
  }

}
