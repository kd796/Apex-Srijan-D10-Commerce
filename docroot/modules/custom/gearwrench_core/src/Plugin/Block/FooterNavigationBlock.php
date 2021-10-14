<?php

namespace Drupal\gearwrench_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;

/**
 * Provides a 'FooterNavigationBlock' block.
 *
 * @Block(
 *  id = "footer_navigation_block",
 *  admin_label = @Translation("Footer navigation block"),
 * )
 */
class FooterNavigationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $footer_menu = $this->gearwrenchCoreBuildMenu('footer');
    $social_menu = $this->gearwrenchCoreBuildMenu('social');

    return [
      'footer_menu' => $footer_menu,
      'social_menu' => $social_menu,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Node change rebuilds block.
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      // If node add cachetag.
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
    }
    // Return default tags instead.
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Every new route this block will rebuild.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * Utility function to build a menu tree.
   */
  public function gearwrenchCoreBuildMenu($menu_name) {
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(1);
    $parameters->onlyEnabledLinks();
    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    return $menu_tree->build($tree);
  }

}
