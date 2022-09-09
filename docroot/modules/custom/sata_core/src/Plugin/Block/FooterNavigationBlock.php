<?php

namespace Drupal\sata_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;
use Drupal\Core\Form\FormStateInterface;

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

    $footer_menu = $this->sataCoreBuildMenu('footer');
    $social_menu = $this->sataCoreBuildMenu('social');
    $address_line_1 = \Drupal::state()->get('footer_address_line_1') ?
      ['#plain_text' => \Drupal::state()->get('footer_address_line_1')] : '';
    $address_line_2 = \Drupal::state()->get('footer_address_line_2') ?
      ['#plain_text' => \Drupal::state()->get('footer_address_line_2')] : '';
    $address_line_3 = \Drupal::state()->get('footer_address_line_3') ?
      ['#plain_text' => \Drupal::state()->get('footer_address_line_3')] : '';
    $address_line_4 = \Drupal::state()->get('footer_address_line_4') ?
      ['#plain_text' => \Drupal::state()->get('footer_address_line_4')] : '';
    $address_line_5 = \Drupal::state()->get('footer_address_line_5') ?
      ['#plain_text' => \Drupal::state()->get('footer_address_line_5')] : '';
    $phone = \Drupal::state()->get('footer_phone') ?
      ['#plain_text' => \Drupal::state()->get('footer_phone')] : '';
    $phone_raw = '';
    if ($phone) {
      $phone_raw = preg_replace('~\D~', '', \Drupal::state()->get('footer_phone'));
    }

    return [
      'footer_menu' => $footer_menu,
      'social_menu' => $social_menu,
      'address_line_1' => $address_line_1,
      'address_line_2' => $address_line_2,
      'address_line_3' => $address_line_3,
      'address_line_4' => $address_line_4,
      'address_line_5' => $address_line_5,
      'phone_raw' => $phone_raw,
      'phone' => $phone,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['address_line_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 1'),
      '#description' => $this->t(''),
      '#default_value' => \Drupal::state()->get('footer_address_line_1') ?? '',
    ];

    $form['address_line_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 2'),
      '#description' => $this->t(''),
      '#default_value' => \Drupal::state()->get('footer_address_line_2') ?? '',
    ];

    $form['address_line_3'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 3'),
      '#description' => $this->t(''),
      '#default_value' => \Drupal::state()->get('footer_address_line_3') ?? '',
    ];

    $form['address_line_4'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 4'),
      '#description' => $this->t(''),
      '#default_value' => \Drupal::state()->get('footer_address_line_4') ?? '',
    ];

    $form['address_line_5'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 5'),
      '#description' => $this->t(''),
      '#default_value' => \Drupal::state()->get('footer_address_line_5') ?? '',
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#description' => $this->t(''),
      '#default_value' => \Drupal::state()->get('footer_phone') ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    \Drupal::state()->set('footer_address_line_1', $values['address_line_1']);
    \Drupal::state()->set('footer_address_line_2', $values['address_line_2']);
    \Drupal::state()->set('footer_address_line_3', $values['address_line_3']);
    \Drupal::state()->set('footer_address_line_4', $values['address_line_4']);
    \Drupal::state()->set('footer_address_line_5', $values['address_line_5']);
    \Drupal::state()->set('footer_phone', $values['phone']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Node change rebuilds block.
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      if (is_object($node)) {
        // If node add cachetag.
        return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
      }
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
