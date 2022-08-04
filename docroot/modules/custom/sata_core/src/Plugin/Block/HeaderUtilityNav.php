<?php

namespace Drupal\sata_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;

/**
 * Provides a utility nav block for the header.
 *
 * @Block(
 *   id = "header_utility_nav",
 *   admin_label = @Translation("Header Utility Nav"),
 *   category = @Translation("sata Core")
 * )
 */
class HeaderUtilityNav extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $header_utility_nav_menu = $this->sataCoreBuildMenu('header-utility-nav');

    return [
      '#theme' => 'block--header-utility-nav',
      'header_utility_nav' => $header_utility_nav_menu,
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
  public function sataCoreBuildMenu($menu_name) {
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
