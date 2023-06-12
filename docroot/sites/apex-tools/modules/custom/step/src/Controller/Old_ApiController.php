<?php

/**
 * @file
 * Contains \Drupal\step\Controller\ApiController.
 */
namespace Drupal\step\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\step\Exceptions\ElasticSearchException;
use Drupal\step\Utils\StepHelper;
use Drupal\step\Utils\StringHelper;
use Drupal\step\Traits\LoggerTrait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends ControllerBase
{
    use LoggerTrait;

    /**
     * @var mixed
     */
    protected $allowAnonymous = true;

    /**
     * @param Request $request
     * @param Bool $return
     *
     * @return JsonResponse
     */
    public function actionFilterProducts(Request $request, Bool $return = false)
    {
        $params = $this->getParams($request);

        $params['index'] = StepHelper::getEsIndexName() . '_products';

        $params['settings']['sort'] = [
            [
                'values.web_display_sort_order' => [
                    'missing' => '_last'
                ],
                '_script'                       => [
                    'script' => "doc['values.web_display_sort_order'].value",
                    'type'   => 'string',
                    'order'  => 'asc'
                ]
            ]
        ];
        $params['query'] = [];

        $params['query'][] = [
            'query'  => 'terms',
            'aggKey' => 'product_line',
            'field'  => 'product_line.raw',
            'values' => !empty($request->get('product_line')) ? $request->get('product_line') : []
        ];

        $params['query'][] = [
            'query'  => 'terms',
            'aggKey' => 'product_category',
            'field'  => 'product_category.raw',
            'values' => !empty($request->get('product_category')) ? $request->get('product_category') : []
        ];

        foreach (StepHelper::getProductFilters() as $filter) {
            if ($filter['type'] == 'checkboxes') {
                $type   = '.raw';
                $query  = 'terms';
                $values = $request->get($filter['key']);
            } else {
                $type   = '.float';
                $query  = 'range';
                $values = array_pad(explode(',', $request->get($filter['key'])), 2, null);
                $values = array_map(function ($value) {
                    if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
                        return (float) $value;
                    }

                    return null;
                }, $values);
            }

            $params['query'][] = [
                'query'  => $query,
                'aggKey' => $filter['key'],
                'field'  => 'filters.' . $filter['key'] . $type,
                'values' => !empty($request->get($filter['key'])) ? $values : []
            ];
        }

        if ($return) {
            return $params;
        }

        try {
            return new JsonResponse($this->getElasticService()->filter($params), 200);
        } catch (ElasticSearchException $exception) {
            $message = $exception->getMessage();
            if (json_decode($message) && json_last_error() === JSON_ERROR_NONE) {
                $message = json_decode($exception->getMessage());
                $message = $message->error->reason;
                $this->log(print_r($exception->getMessage(), true), 'error');
            }

            return new JsonResponse($message, 500);
        }
    }

    /**
     * @param Request $request
     * @param Bool $return
     * @param Array $query
     *
     * @return JsonResponse
     */
    public function actionFilterDownloads(Request $request, Bool $return = false, Array $query = [])
    {
        $params = $this->getParams($request);

        $params['index'] = [
            StepHelper::getEsIndexName() . '_downloads',
            'cleco_legacy_documents'
        ];
        $params['query'] = [];

        $params['query'][] = [
            'query'  => 'terms',
            'aggKey' => 'document_type',
            'field'  => 'type.raw',
            'values' => !empty($request->get('document_type')) ? $request->get('document_type') : []
        ];

        $params['query'][] = [
            'query'  => 'terms',
            'aggKey' => 'language',
            'field'  => 'values.language.raw',
            'values' => !empty($request->get('language')) ? $request->get('language') : []
        ];

        $params['query'][] = [
            'query'  => 'terms',
            'aggKey' => 'product_category',
            'field'  => 'product_category.raw',
            'values' => !empty($request->get('product_category')) ? $request->get('product_category') : []
        ];

        if (!empty($query)) {
            $params['should'] = array_map(function($a) {
                return [
                    'match' => [
                      'id.raw' => trim($a)
                    ]
                ];
            }, array_values($query));
        }

        // Default sort by site's language
        // $sort     = [];
        $language = Drupal::languageManager()->getCurrentLanguage(Drupal\Core\Language\LanguageInterface::TYPE_CONTENT)->getId();
        // @todo Add all translated site's language codes
        $languages = [
            'en' => 'English',
            'gb' => 'English',
            'de' => 'German',
            'it' => 'Italian',
            'jp' => 'Japanese',
            'kr' => 'Korean',
            'pt' => 'Portuguese',
            'es' => 'Spanish',
            'cn' => 'Chinese',
            'fr' => 'French',
        ];
        $curLangaugeName = $languages[$language] ?? null;

        if ($curLangaugeName != null && empty($request->get('language'))) {
            $params['settings']['sort'] = [
                '_score',
                [
                    '_script'  => [
                        'type' => 'string',
                        'script' => [
                            'lang' => 'painless',
                            'source' => "doc['values.language.raw'].values.indexOf(params.langname) === -1",
                            'params' => [
                                'langname' => $curLangaugeName,
                            ],
                        ],
                        'order' => 'asc',
                    ],
                ],
                ['name.raw' => ['order' => 'asc']],
            ];
        } else {
            $params['settings']['sort'] = [
                '_score',
                [
                    'name.raw' => [
                        'order' => 'asc'
                    ]
                ],
            ];
        }

        if ($return) {
            return $params;
        }

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            return new JsonResponse($this->getElasticService()->filter($params), 200);
        } catch (ElasticSearchException $exception) {
            $message = $exception->getMessage();
            if (json_decode($message) && json_last_error() === JSON_ERROR_NONE) {
                $message = json_decode($exception->getMessage());
                $message = $message->error->reason;
                $this->log(print_r($exception->getMessage(), true), 'error');
            }

            return new JsonResponse($message, 500);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function actionSearchDownloads(Request $request)
    {
        $imageAssets = [
            'Beauty-Glamour Image',
            'Primary Image',
            'Secondary Image',
            'SecondaryImage',
            'Part Shot 1',
            'Part Shot 2',
            'Part Shot 3',
            'Part Shot 4',
            'Part Shot 5',
            'Dimension Diagram',
        ];

        // do initial product lookup
        $productAssets = $this->actionFilterProducts($request);
        $productAssetsData = json_decode($productAssets->getContent(), true);

        $productIDs = [];
        $params = '';

        // pull out all the non-image assets from product search results
        if ($productAssetsData['hits']['total'] && $productAssetsData['hits']['hits']) {
            foreach ($productAssetsData['hits']['hits'] as $hit) {
                foreach ($hit['_source']['assets'] as $asset) {
                    if (array_search(strtolower($asset['type']), array_map('strtolower', $imageAssets)) === false) {
                        $productIDs[] = $asset['id'];
                    }
                }
            }
        }

        $params = $this->actionFilterDownloads($request, true, array_unique($productIDs));

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            return new JsonResponse($this->getElasticService()->filter($params), 200);
        } catch (ElasticSearchException $exception) {
            $message = $exception->getMessage();
            if (json_decode($message) && json_last_error() === JSON_ERROR_NONE) {
                $message = json_decode($exception->getMessage());
                $message = $message->error->reason;
                $this->log(print_r($exception->getMessage(), true), 'error');
            }

            return new JsonResponse($message, 500);
        }
    }

    /**
     * Drupal custom field
     *
     * @param Request $request
     */
    public function actionAutoCompleteFieldProducts(Request $request)
    {
        $params['count'] = $request->get('count') ?? 20;
        $params['q']     = $request->get('q') ?? null;
        $params['index'] = StepHelper::getEsIndexName() . '_products';

        return $this->getElasticService()->autoCompleteFieldProducts($params);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function actionSearch(Request $request)
    {
        $imageAssets = [
            'Beauty-Glamour Image',
            'Primary Image',
            'Secondary Image',
            'SecondaryImage',
            'Part Shot 1',
            'Part Shot 2',
            'Part Shot 3',
            'Part Shot 4',
            'Part Shot 5',
            'Dimension Diagram',
        ];

        // do initial product lookup
        $productAssets = $this->actionFilterProducts($request);
        $productAssetsData = json_decode($productAssets->getContent(), true);

        $productIDs = [];
        $params = '';

        // pull out all the non-image assets from product search results
        if ($productAssetsData['hits']['total'] && $productAssetsData['hits']['hits']) {
            foreach ($productAssetsData['hits']['hits'] as $hit) {
                foreach ($hit['_source']['assets'] as $asset) {
                    if (array_search(strtolower($asset['type']), array_map('strtolower', $imageAssets)) === false) {
                        $productIDs[] = $asset['id'];
                    }
                }
            }
        }

        $params = $this->actionFilterDownloads($request, true, array_unique($productIDs));

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            return new JsonResponse($this->getElasticService()->filter($params), 200);
        } catch (ElasticSearchException $exception) {
            $message = $exception->getMessage();
            if (json_decode($message) && json_last_error() === JSON_ERROR_NONE) {
                $message = json_decode($exception->getMessage());
                $message = $message->error->reason;
                $this->log(print_r($exception->getMessage(), true), 'error');
            }

            return new JsonResponse($message, 500);
        }
    }

    // Old way to search on homepage
    /**
     * @param Request $request
     */
    // public function actionSearch(Request $request)
    // {
    //     $params          = $this->getParams($request);
    //     $params['index'] = StepHelper::getEsIndexName();
    //     $params['query'] = [];

    //     $params['query'][] = [
    //         'query'  => 'terms',
    //         'aggKey' => 'type',
    //         'field'  => 'type.raw',
    //         'values' => !empty($request->get('type')) ? $request->get('type') : []

    //     ];
    //     $params['query'][] = [
    //         'query'  => 'terms',
    //         'aggKey' => 'product_category',
    //         'field'  => 'product_category.raw',
    //         'values' => !empty($request->get('product_category')) ? $request->get('product_category') : []
    //     ];

    //     $params['query'][] = [
    //         'query'  => 'terms',
    //         'aggKey' => 'product_line',
    //         'field'  => 'product_line.raw',
    //         'values' => !empty($request->get('product_line')) ? $request->get('product_line') : []
    //     ];

    //     try {
    //         // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
    //         $response = $this->getElasticService()->search($params);
    //         // print_r('<pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre>'); die();
    //         return new JsonResponse($response, 200);
    //     } catch (ElasticSearchException $exception) {
    //         $message = $exception->getMessage();
    //         if (json_decode($message) && json_last_error() === JSON_ERROR_NONE) {
    //             $message = json_decode($exception->getMessage());
    //             $message = $message->error->reason;
    //             $this->log(print_r($exception->getMessage(), true), 'error');
    //         }

    //         return new JsonResponse($message, 500);
    //     }
    // }

    /**
     * GET PARAMS
     * Return parameters included in the request.
     * Also provides fallback for required `page` and `perPage` parameters
     *
     * @return array
     */
    protected function getParams(Request $request)
    {
        $params['page']     = $request->get('page') ?? 1;
        $params['perPage']  = $request->get('perPage') ?? 24;
        $params['settings'] = [
            'from' => ($params['page'] - 1) * $params['perPage'],
            'size' => $params['perPage']
        ];
        $params['queryString'] = $request->get('q') ?? null;

        return $params;
    }

    /**
     * GET ELASTIC SERVICE
     * Return a reference to ElasticSearchService
     *
     * @return ElasticSearchService
     */
    private function getElasticService()
    {
        return Drupal::service('step.elastic_search_service');
    }
}
