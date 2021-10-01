<?php

namespace Drupal\apex_tools_instagram_feed\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "apex_tools_instagram_feed_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("Apex Tools Instagram Feed")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
