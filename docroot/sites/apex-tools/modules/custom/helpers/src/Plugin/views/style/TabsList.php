<?php

namespace Drupal\helpers\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item in a tabs list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "tabs_list",
 *   title = @Translation("Tabs List"),
 *   help = @Translation("Displays rows as tabs list."),
 *   theme = "views_view_tabs_list",
 *   display_types = {"normal"}
 * )
 */
class TabsList extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesFields = true;

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = false;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesGrouping = false;
}
