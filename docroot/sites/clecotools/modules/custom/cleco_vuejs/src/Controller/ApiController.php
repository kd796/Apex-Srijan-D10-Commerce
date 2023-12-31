<?php

namespace Drupal\cleco_vuejs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\cleco_vuejs\Services\SolrSearchApiService;
use Drupal\cleco_vuejs\Services\VueDataFormatter;

/**
 * Controller for search_api related routes.
 */
class ApiController extends ControllerBase {
  /**
   * The solr search service.
   *
   * @var \Drupal\cleco_vuejs\Controller\SolrSearchApiService
   */
  protected SolrSearchApiService $solrSearchService;

  /**
   * The vuejs data formatter service.
   *
   * @var \Drupal\cleco_vuejs\Services\VueDataFormatter
   */
  protected VueDataFormatter $vueDataFormatter;

  /**
   * Construct ApiController object.
   *
   * @param \Drupal\cleco_vuejs\Services\SolrSearchApiService $solr_search_service
   *   The solr search service.
   * @param \Drupal\cleco_vuejs\Services\VueDataFormatter $vue_data_formatter
   *   The vuejs data formatter service.
   */
  public function __construct(SolrSearchApiService $solr_search_service, VueDataFormatter $vue_data_formatter) {
    $this->solrSearchService = $solr_search_service;
    $this->vueDataFormatter = $vue_data_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cleco_vuejs.solr_search_service'),
      $container->get('cleco_vuejs.vue_data_formatter')
    );
  }

  /**
   * Product catalog callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The result json data.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function actionFilterProducts(Request $request) {
    $params = $this->getParams($request);

    // Perform search.
    $results = $this->solrSearchService->searchProducts($params['filters'], $params['queryString'], $params['perPage'], $params['settings']['from']);

    // Format results for vuejs.
    $json = $this->vueDataFormatter->formatSearchResults($results);

    return new JsonResponse($json, 200);
  }

  /**
   * Callback for downloads page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response data.
   */
  public function actionFilterDownloads(Request $request) {
    // Search query.
    // Pagination params.
    $page = $request->get('page');
    if (!is_numeric($page)) {
      $page = 1;
    }

    $per_page = $request->get('perPage');
    if (!is_numeric($per_page)) {
      $per_page = 24;
    }
    $offset = ($page - 1) * $per_page;

    // Consider all other params apart from above as filters.
    $filter_params = $request->query->all();
    $queryString = $filter_params['q'];

   $results = $this->solrSearchService->searchDownloads($filter_params, $queryString, $per_page, $offset);
    // Format results for vuejs.
    $json = $this->vueDataFormatter->formatDownloadCatalog($results);

    return new JsonResponse($json, 200);

  }

  /**
   * Search download action.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Accepting request parameter.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returning json response.
   */
  public function actionSearchDownloads(Request $request) {
    $json = $this->getElasticService()->getMediaDownload();
    return new JsonResponse($json, 200);
  }

  /**
   * Drupal custom field.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   */
  public function actionAutoCompleteFieldProducts(Request $request) {

  }

  /**
   * The search page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   */
  public function actionSearch(Request $request) {
    $params = $this->getParams($request);

    // Perform search.
    $results = $this->solrSearchService->searchAll($params['filters'], $params['queryString'], $params['perPage'], $params['settings']['from']);

    // Format results for vuejs.
    $json = $this->vueDataFormatter->formatSearchResults($results);

    return new JsonResponse($json, 200);
  }

  /**
   * GET PARAMS.
   *
   * Return parameters included in the request.
   * Also provides fallback for required `page` and `perPage` parameters.
   *
   * @return array
   *   The parameter array.
   */
  protected function getParams(Request $request) {
    // Pagination params.
    $params['page']    = $request->get('page') ?? 1;
    $params['perPage'] = $request->get('perPage') ?? 24;
    settype($params['page'], 'integer');
    settype($params['perPage'], 'integer');

    $params['settings'] = [
      'from' => ($params['page'] - 1) * $params['perPage'],
      'size' => $params['perPage'],
    ];

    // Search query.
    $params['queryString'] = $request->get('q') ?? NULL;

    // Consider all other params apart from above as filters.
    $filter_params = $request->query->all();
    unset($filter_params['q']);
    unset($filter_params['page']);
    unset($filter_params['perPage']);
    $params['filters'] = $filter_params;

    return $params;
  }

  /**
   * GET ELASTIC SERVICE - Return a reference to ElasticSearchService.
   *
   * @return array
   *   Returning service.
   */
  private function getElasticService() {
    return \Drupal::service('step.elastic_search_service');
  }

}
