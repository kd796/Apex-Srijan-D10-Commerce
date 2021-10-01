<?php

namespace Drupal\apex_tools_instagram_feed\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a social feed block.
 *
 * @Block(
 *   id = "apex_tools_instagram_feed_social_feed",
 *   admin_label = @Translation("Social Feed"),
 *   category = @Translation("ATG")
 * )
 */
class SocialFeedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $social_feed = views_embed_view('social_feed', 'feed_embed');
    return [
      'social_feed' => $social_feed
    ];
  }

}
