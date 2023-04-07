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
    // Search query.
    $search_query = $request->get('q', '');

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
    unset($filter_params['q']);
    unset($filter_params['page']);
    unset($filter_params['perPage']);

    // Perform search.
    $results = $this->solrSearchService->searchProducts($filter_params, $search_query, $per_page, $offset);

    // Format results for vuejs.
    $json = $this->vueDataFormatter->formatProductCatalog($results);

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
    $json = file_get_contents(__DIR__ . '/sample-search.json');
    return new JsonResponse(json_decode($json), 200);
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
    $json = file_get_contents(__DIR__ . '/sample-search.json');
    return new JsonResponse(json_decode($json), 200);
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
    $params['page']    = $request->get('page') ?? 1;
    $params['perPage'] = $request->get('perPage') ?? 24;
    settype($params['page'], 'integer');
    settype($params['perPage'], 'integer');
    $params['settings'] = [
      'from' => ($params['page'] - 1) * $params['perPage'],
      'size' => $params['perPage'],
    ];
    $params['queryString'] = $request->get('q') ?? NULL;

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
