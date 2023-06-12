<?php

namespace Drupal\helpers\Plugin\views\row;

use Drupal\views\Plugin\views\row\RowPluginBase;

/**
 * Row handler plugin for displaying search results.
 *
 * @ViewsRow(
 *   id = "tab_view",
 *   title = @Translation("Tab"),
 *   help = @Translation("Provides a tab plugin to display."),
 *   theme = "views_view_tab",
 *   display_types = {"normal"}
 * )
 */
class TabRow extends RowPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    return [
      '#theme'   => $this->themeFunctions(),
      '#view'    => $this->view,
      '#options' => $this->options,
      '#row'     => $row,
    ];
  }

}
