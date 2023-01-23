<?php

namespace Drupal\customsearch\Controller;

use Drupal\search\Controller\SearchController;
use Drupal\search\SearchPageInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomSearchController extends SearchController {

  /**
   * {@inheritdoc}
   */
  public function view(Request $request, SearchPageInterface $entity) {
    $keys = trim($request->query->get('keys'));
    $build = parent::view($request, $entity);

    return [
      '#theme' => 'customsearch',
      '#search' => $build,
      '#total_results' => isset($GLOBALS['pager_total_items']) ? $GLOBALS['pager_total_items'][0] : null,
      '#search_query' => $keys,
    ];
  }
}
