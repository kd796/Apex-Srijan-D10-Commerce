<?php

namespace Drupal\addtothis_apex_tool\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides an addthis block.
 *
 * @Block(
 *   id = "addtothis_apex_tool_custom",
 *   admin_label = @Translation("AddThis Share"),
 *   category = @Translation("Custom")
 * )
 */
class AddthisBlock extends BlockBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {

    $addthis_content = "<a href='https://www.addthis.com/bookmark.php' class='addthis_button' style='text-decoration:none;' target='_blank'>
    <img src='/sites/apex-tools/files/2023-05/share-grey.png' width='16' height='16' border='0'/>" . $this->t('Share') . "</a>
    ";

    return [
      '#theme' => 'add_to_this_content',
      '#addthis_content' => $addthis_content,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
