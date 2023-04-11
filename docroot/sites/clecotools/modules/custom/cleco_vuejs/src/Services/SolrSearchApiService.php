<?php

namespace Drupal\cleco_vuejs\Services;

use Drupal\cleco_vuejs\Utils\StepHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\search_api\Query\QueryInterface;

/**
 * Solr search api related functions.
 */
class SolrSearchApiService {

  /**
   * The search index name.
   *
   * @var string
   */
  protected const SEARCH_INDEX = 'acquia_search_index';

  /**
   * The search query object.
   *
   * @var \Drupal\search_api\Query\QueryInterface
   */
  protected QueryInterface $query;

  /**
   * The parse mode plugin manager service.
   *
   * @var \Drupal\search_api\ParseMode\ParseModePluginManager
   */
  protected ParseModePluginManager $parseModeManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Construct the SolrSearchApiService object.
   *
   * @param \Drupal\search_api\ParseMode\ParseModePluginManager $parse_mode
   *   The parse mode plugin manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\search_api\SearchApiException
   */
  public function __construct(ParseModePluginManager $parse_mode, LanguageManagerInterface $language_manager) {
    $this->parseModeManager = $parse_mode;
    $this->languageManager = $language_manager;
    $this->initSearch();
  }

  /**
   * Initialize search query.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\search_api\SearchApiException
   */
  protected function initSearch() {
    $index = Index::load(self::SEARCH_INDEX);
    $this->query = $index->query();
    $parse_mode = $this->parseModeManager->createInstance('direct');
    $this->query->setParseMode($parse_mode);
  }

  /**
   * Perform a search using filters and query.
   *
   * @param array $filters
   *   The filters to be added to this search.
   * @param string $search_query
   *   The search query string.
   * @param string $per_page
   *   Number of items to show per page.
   * @param string $offset
   *   Number of items to offset.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   The search query results.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function search(array $filters = [], $search_query = '', $per_page = 12, $offset = 0) {
    if ($search_query) {
      // Search query.
      $this->query->keys($search_query);
    }

    // Items per page and offset used for pagination.
    $this->query->range($offset, $per_page);

    // Filters.
    foreach ($filters as $filter_name => $filter) {
      // We have 2 types of conditions; range and list.
      if (is_array($filter)) {
        $this->query->addCondition($filter_name, $filter, 'IN');
        continue;
      }
      if (!is_string($filter) || !$filter || !str_contains($filter, ',')) {
        // Make sure we only have a string, and it is a range.
        continue;
      }
      $range = explode(',', $filter);
      $this->query->addCondition($filter_name, $range, 'BETWEEN');
    }

    // Facets.
    $terms = StepHelper::getProductFilters();
    ;
    $facet_filters = [];
    $facet_filters[] = 'sm_product_category';
    foreach ($terms as $term) {
      $facet_filters[] = 'sm_' . $term['key'];
    }

    $this->query->setOption('solr_param_facet', 'true');
    $this->query->setOption('solr_param_facet.field', $facet_filters);

    // Language.
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $this->query->addCondition('langcode', $language);

    return $this->query->execute();
  }

  /**
   * Perform a search on products with given parameters.
   *
   * @param array $filters
   *   The filters to be added to this search.
   * @param string $search_query
   *   The search query string.
   * @param string $per_page
   *   Number of items to show per page.
   * @param string $offset
   *   Number of items to offset.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   The search query results.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function searchProducts(array $filters = [], $search_query = '', $per_page = 12, $offset = 0) {
    $this->query->addCondition('type', ['product', 'enhanced_product'], 'IN');
    return $this->search($filters, $search_query, $per_page, $offset);
  }

  /**
   * Perform a search on products with given parameters.
   *
   * @param array $filters
   *   The filters to be added to this search.
   * @param string $search_query
   *   The search query string.
   * @param string $per_page
   *   Number of items to show per page.
   * @param string $offset
   *   Number of items to offset.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   The search query results.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function searchDownloads(array $filters = [], $search_query = '', $per_page = 12, $offset = 0) {

    $this->query->addCondition('bundle', 'product_downloads');

    return $this->searchDownloadFilters($filters, $search_query, $per_page, $offset);
  }


  public function searchDownloadFilters(array $filters = [], $search_query = '', $per_page = 12, $offset = 0) {
    $curSite = StepHelper::getCurrentSite();
    $site_lang = $curSite['code'];
    if ($search_query) {
      $this->query->keys($search_query);
    }

    // Items per page and offset used for pagination.
    $this->query->range($offset, $per_page);

    foreach ($filters as $filter_name => $filter) {
      if ($filter_name == 'document_type') {
        $filter_name = 'item_type';
      }
      if ($filter_name == 'language') {
        $filter_name = 'medialang_type';
      }
      if ($filter_name == 'product_category') {
        $filter_name = 'download_category_name';
        foreach ($filter as $each) {
          $this->query->addCondition($filter_name, $each, '=');
        }
      }
      elseif (is_array($filter)) {
        $this->query->addCondition($filter_name, $filter, 'IN');
        continue;
      }
    }

  $facet_filters = [];

  $facet_filters = ['sm_medialang_type','sm_download_category_name','sm_item_type'];

  foreach ($terms as $term) {
    $facet_filters[] = 'sm_' . $term['key'];
  }

  $this->query->setOption('solr_param_facet', 'true');
  $this->query->setOption('solr_param_facet.field', $facet_filters);

  // Language.
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $this->query->addCondition('site_langcode', $language);

    return $this->query->execute();
  }

}
