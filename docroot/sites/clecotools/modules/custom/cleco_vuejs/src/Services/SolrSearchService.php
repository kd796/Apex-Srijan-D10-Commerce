<?php

namespace Drupal\cleco_vuejs\Services;

use Drupal;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\cleco_vuejs\Utils\StepHelper;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;


class SolrSearchService
{
    /**
     * ElasticSearch client
     */
    private $client = null;

    /**
     * ElasticSearch Drupal form settings
     */
    private $settings;

    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The language manager service.
     *
     * @var \Drupal\Core\Language\LanguageManagerInterface
     */
    protected $languageManager;

    /**
     * Constructs a solr search service.
     * 
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
     *   The entity manager.
     * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
     *   The language manager service.
     * 
     * @return null
     */
    public function __construct(EntityTypeManagerInterface $entity_manager, LanguageManagerInterface $language_manager)
    {
        $this->entityManager = $entity_manager;
        $this->languageManager = $language_manager;
        $this->settings = Drupal::config('step.settings');

        $hosts = $this->getElasticHosts();

        if (empty($hosts) || empty($hosts[0])) {
            return;
        }

        $this->client = $this->getClient();
    }

    /**
     * @param  string $index
     * @return mixed
     */
    public function getElasticsearchIndices(string $index = '')
    {
        if ($this->client === null) {
            return;
        }

        try {
            $indices = $this->client->indices()->get(['index' => $index ?: StepHelper::getEsIndexName() . '_*']);
        } catch (\Exception $e) {

            return false;
        }

        $response = [];
        foreach ($indices as $indice => $value) {
            // Exclude names that begin with "."
            if (strncmp($indice, '.', 1) !== 0) {
                $response[] = $indice;
            }
        }

        return $response;
    }

    /**
     * @return mixed
     */
    private function getClient(): \Elasticsearch\Client
    {
        $hosts = $this->getElasticHosts();

        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        return $client;
    }

    /**
     * Creates Index
     *   - Predefined JSON configuration for searching, buckets, localization, etc
     *   - Aim to keep the average shard size between a few GB and a max 30GB. It ill reduce
     *     overhead and allow for faster querying. e.g. 200GB 7 shards
     */
    public function createIndex(string $index)
    {
        $basepath = __DIR__ . '/../config/mappings/';

        if (strpos($index, '_products') !== false) {
            $url = $basepath . 'products.json';
        }

        if (strpos($index, '_downloads') !== false) {
            $url = $basepath . 'downloads.json';
        }

        if (strpos($index, '_nodes') !== false) {
            $url = $basepath . 'nodes.json';
        }

        if (strpos($index, '_legacy_documents') !== false) {
            $url = $basepath . 'legacy.json';
        }

        $config = isset($url) ? Json::decode(file_get_contents($url, true), true) : [];

        $params = array_merge(['index' => $index], $config);

        try {
            $response = $this->client->indices()->create($params);
        } catch (\Exception $e) {
            $this->log(print_r(Json::decode($e->getMessage()), true), 'error');
            return Json::encode($e->getMessage());
        }

        return $response;
    }

    /**
     * @param  string $indexName
     * @return mixed
     */
    public function deleteIndex(string $indexName)
    {
        $params = ['index' => $indexName];

        try {
            $response = $this->client->indices()->delete($params);
            drupal_set_message(t('Successfully deleted ElasticSearch Index: ' . $indexName), 'status');
            $this->record('Successfully deleted ElasticSearch Index: ' . $indexName, 'notice', true);
        } catch (\Exception $e) {
            drupal_set_message(t('There was an error trying to delete the ElasticSearch Index: ' . $indexName), 'error');
            $this->log(print_r(Json::decode($e->getMessage()), true), 'error');
        }
    }

    /**
     * @param  $docs
     * @return mixed
     */
    public function indexDocuments(array $docs)
    {
        return $this->bulk($docs);
    }

    /**
     * @param string $indexName
     */
    public function indexExists(string $indexName)
    {
        $params = ['index' => $indexName];
        try {
            return $this->client->indices()->exists($params);
        } catch (\Exception $e) {
            $this->log("$indexName ElasticSearch index does not exist", 'error');
            return Json::decode($e->getMessage());
        }
    }

    /**
     * @param  array $docs
     * @return mixed
     */
    protected function bulk(array $docs)
    {
        // Grab ES Index name from first object.
        $index = $docs[0]->_index;

        if (!$this->indexExists($index)) {
            $this->createIndex($index);
        }

        $params = ['body' => []];

        foreach ($docs as $i => $doc) {
            // Meta-fields
            $params['body'][] = [
                $doc->_action => [
                    '_index' => $doc->_index,
                    '_type'  => $doc->_type,
                    '_id'    => $doc->_id
                ]
            ];

            // Index Document
            if ($doc->_action == 'index') {
                $params['body'][] = $doc->body;
            }

            // Update Document
            if ($doc->_action == 'update') {
                $params['body'][] = $doc->body;
            }

            // Delete Document
            // @todo

            // Every 1000 documents stop and send the bulk request
            if ($i % 1000 == 0) {
                try {
                    $responses = $this->client->bulk($params);
                    // erase the old bulk request
                    $params = ['body' => []];
                    // unset the bulk response when you are done to save memory
                    unset($responses);
                } catch (\Exception $e) {
                    $this->log(print_r(Json::decode($e->getMessage()), true), 'error');
                    return Json::decode($e->getMessage());
                }
            }
        }

        // Send the last batch if it exists
        if (!empty($params['body'])) {
            try {
                $responses = $this->client->bulk($params);
            } catch (\Exception $e) {
                $this->log(print_r(Json::decode($e->getMessage()), true), 'error');
                return Json::decode($e->getMessage());
            }
        }

        $this->record("Successfully indexed {$index}. Total docs {$doc->_action} " . count($docs), 'notice', true);
    }

    /**
     * Get related products for product details template.
     * Twofold query based on 'must' and 'should'. For accuracy some fields must match.
     *
     * Query may need to be massaged over time to get the best results.
     * May need to add boost and different params per filter to get better results.
     *
     * ID of Value References
     * Website_718028 - Assembly Tools
     *  - W1_728276   - Accessories
     *  - W1_718581   - Air Motors
     *  - W1_718038   - Controllers & Software
     *  - W1_718080   - Electric Torque Wrenches
     *  - W1_718040   - Fixtured Spindles
     *  - W1_727160   - Impact Wrenches
     *  - W1_727155   - Nutrunners & Screwdrivers
     *  - W1_727161   - Pulse Tools
     * Website_718104 - Drilling & Riveting
     *  - W1_728278   - Accessories
     *  - W1_718105   - Advanced Drills
     *  - W1_718128   - Cutters
     *  - W2_718131   - Back Spotface Cutters
     *  - W2_718133   - Cutters for Microstop Cages
     *  - W2_718132   - Drill & Countersink Cutters for Automatic Machines
     *  - W1_718108   - Hand Drilling, Countersinking &amp; Spotfacing
     *  - W2_718113   - Microstop Cages
     *  - W1_718116   - Riveting
     * Website_718081 - Material Removal
     *  - W1_728279   -  Accessories
     *  - W1_718082   -  Grinders
     *  - W1_718083   -  Sanders & Polishers
     *  - W1_718084   -  Specialty Tools
     *  - W2_718103   -  Lint Picker
     *  - W2_718100   -  Nibblers
     *  - W2_718097   -  Percussion
     *  - W2_718096   -  Routers
     *  - W2_718099   -  Saws
     *  - W2_718101  -  Shears & Scissors
     *
     * @param array $filters
     */
    public function getRelatedProducts(array $filters, string $name, int $size = 5, $enhanced = false)
    {
        $must         = [];
        $query        = [];
        $productTerms = $this->entityManager->getStorage('taxonomy_term')->loadTree('products', 0, null, true);

        // All possible MUST criteria matched via Classification IDs
        // If a product has a classfication under one of the keys below, a MUST criterion will be applied for the given key
        // e.g. There's 6 classifications where a product_category MUST match
        // e.g. W1_718083 will need to match 3 different criterion
        $criteria = [
            // 'product_category'         => [
            //     'W1_718038',
            //     'W1_718040',
            //     'W1_718105',
            //     'W1_728278',
            //     'W1_728276',
            //     'W1_728279'
            // ],
            // @todo possible too restricting
            'abrasive_capacity'        => [
                'W1_718082',
                'W1_718083'
            ],
            // @todo possible too restricting
            'abrasive_type'            => [
                'W1_718082',
                'W1_718083'
            ],
            'air_inlet_size_inh'       => [
                'Website_718081'
            ],
            'chisel'                   => [
                'W1_718108'
            ],
            'collet_size'              => [
                'W1_718082'
            ],
            'exhaust'                  => [
                'W1_718082',
                'W1_718083'
            ],
            'horsepower_hj'            => [
                'W1_718082'
            ],
            'material'                 => [
                'W1_718082'
            ],
            'nose_insert_style'        => [
                'W2_718113'
            ],
            // @todo possible too restricting
            'orbital_pattern_size_inh' => [
                'W1_718083'
            ],
            // @todo possible too restricting
            'saw_blade_capacity_in'    => [
                'W1_718084'
            ],
            // @todo possible too restricting
            'shank_diamater_in'        => [
                'W1_718084'
            ],
            'speed_rpm'                => [
                'W1_718581'
            ],
            'spindle_size'             => [
                'W1_718082'
            ],
            'tool_termination'         => [
                'Website_718081'
            ],
            'termination'              => [
                'Website_718081'
            ],
            'tool_type'                => [
                'Website_718081'
            ],
            'torque_transducer'        => [
                'W1_718040'
            ]
        ];

        // Build the MUST criteria
        // @note ensure we're not using programmatic buckets since they don't have the CMS field 'field_es_id'
        // Programmatic buckets were combined from multiple filters and they won't have a single Id
        foreach ($productTerms as $product) {
            $id = (string) $product->get('field_es_id')->value;
            // Only build MUST criteria for certain fields based on STEP classification's Id
            if (in_array($id, $filters['product_line_ids'])) {
                foreach ($criteria as $mustKey => $mustIds) {
                    // Build criteria when classification Id exist in one of the criteria indices
                    if (in_array($id, $mustIds)) {
                        $must[] = $mustKey;
                    }
                }
            }
        }

        foreach ($filters as $filter => $value) {
            // Ensure field values are not empty
            if (!empty($filters[$filter])) {
                // classifications are outside of ES filters object
                if ($filter == 'product_category' || $filter == 'product_line') {
                    $field = $filter . '.raw';
                } else {
                    $field = 'filters.' . $filter . '.raw';
                }

                if (in_array($filter, $must)) {
                    $query['must'][] = [
                        'more_like_this' => [
                            'fields'               => [$field],
                            'like'                 => $filters[$filter],
                            'min_term_freq'        => 1,
                            'min_word_length'      => 1,
                            'minimum_should_match' => '100%',
                            'boost_terms'          => '2.0'
                        ]
                    ];
                } else {
                    // Make every classifications MUST
                    // @note test as of 10.25.18
                    if ($filter == 'product_category' || $filter == 'product_line') {
                        $params = [
                            'min_term_freq'        => 1,
                            'min_doc_freq'         => 1,
                            'minimum_should_match' => '90%',
                            'boost'                => '2.0'
                        ];

                        $params = array_merge(
                            $params, [
                            'fields' => [$field],
                            'like'   => $filters[$filter]
                            ]
                        );

                        $query['must'][] = [
                            'more_like_this' => $params
                        ];
                    } else {
                        $params = [
                            'min_term_freq'        => 1,
                            'minimum_should_match' => '50%'
                        ];

                        $params = array_merge(
                            $params, [
                            'fields' => [$field],
                            'like'   => $filters[$filter]
                            ]
                        );

                        $query['should'][] = [
                            'more_like_this' => $params
                        ];
                    }
                }
            }
        }

        $params = [
            'index' => StepHelper::getEsIndexName() . '_products',
            'size'  => (string) $size,
            'body'  => [
                'query' => [
                    'bool' => $query
                ]
            ]
        ];

        try {
            // echo '<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'; die();
            $response = $this->client->search($params);

            if (!empty($response['hits']['hits'])) {
                return Json::encode($response['hits']);
            } else {
                return '';
            }
        } catch (\Exception $e) {

            return '';
        }
    }

    /**
     * @param array $multiQuery
     *   - ['slug', value]
     *   - ['id', value]
     */
    public function getProductCarousel(array $multiQuery)
    {
        $indices = [
            StepHelper::getEsIndexName() . '_products',
            StepHelper::getEsIndexName() . '_models',
            StepHelper::getEsIndexName() . '_downloads'
        ];

        $params = ['index' => $indices];

        foreach ($multiQuery as $match) {
            $params['body']['query']['bool']['should'][] = [
                'terms' => [
                    $match[0] . '.raw' => [$match[1]]
                ]
            ];
        }

        try {

            $response = $this->client->search($params);

            if (!empty($response['hits']['hits'])) {
                return Json::encode($response['hits']['hits']);
            } else {
                return Json::encode(
                    [
                    'status'  => 204,
                    'message' => 'No results found.'
                    ]
                );
            }
        } catch (\Exception $e) {

            return Json::encode(
                [
                'status'  => 204,
                'message' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * @param array $matchQuery
     * @param array $indices
     */
    public function getSingleProduct(array $matchQuery, array $indices = ['acquia_search_index'], bool $assetsOnly = false)
    {
        $params = [
            'index' => $indices ?: StepHelper::getEsIndexName() . '_products',
            'body'  => [
                'query' => [
                    'match' => [
                        $matchQuery[0] => $matchQuery[1]
                    ]
                ]
            ]
        ];
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        $output = [];
        $index = Index::load('acquia_search_index');
        $query = $index->query();
        $parse_mode = \Drupal::service('plugin.manager.search_api.parse_mode')
                      ->createInstance('direct');
        $query->setParseMode($parse_mode);
        $query->addCondition('field_slug', $matchQuery[1]);
        $query->addCondition('langcode', $langcode);
        $results = $query->execute();
        $assets = [];
        foreach ($results as $result11) {
          $resultItemFields = $result11->getFields();
          $models = $resultItemFields['field_product_models']->getvalues();
          $models_details = $this->getProductModelDetails($resultItemFields['field_product_models']->getvalues());
          $field_type = $resultItemFields['type']->getvalues();
          if ($field_type[0] == 'enhanced_product') {
          $sku_group = $resultItemFields['field_sku']->getvalues();
          $t_title = $resultItemFields['title']->getvalues();
          $title = $t_title[0]->getText();
          $slug = $resultItemFields['field_slug']->getvalues();
          $node_id = $resultItemFields['nid']->getvalues();
          $field_360_image = $resultItemFields['field_360_image']->getvalues();
          $field_360_image = $resultItemFields['field_feature_hotspots']->getvalues();
          $field_product_features_cp = $resultItemFields['field_product_features_cp']->getvalues();
          // $field_media = $resultItemFields['field_media']->getvalues();
          $product_category = $resultItemFields['field_product_classifications']->getvalues();
            if(!empty($product_category)) {
                $product_category = end($product_category);
                $term = $this->entityManager->getStorage('taxonomy_term')->load($product_category);
                if(!empty($term)){
                    $product_category_name = $term->get('name')->value;
                }
            }
            else {
                $product_category_name = '';
            }
          $field_downloads = $resultItemFields['field_downloads']->getvalues();
          if (!empty($field_downloads)) {
            foreach ($field_downloads as $productDownload) {
              $downloads_list = $this->entityManager->getStorage('media')->load($productDownload);
              $type = str_replace("_" , " ", $downloads_list->get('field_type')->value);
              $type = ucwords($type);
              if(!empty($type)){
              $type = StepHelper::translate($type);
              }
              $dname = $downloads_list->get('name')->value;
              $file_id = $this->entityManager->getStorage('file')->load($downloads_list->field_media_file->target_id);
              $file_url = $file_id->getFileUri();
              $downloadable = file_create_url($file_url);
              $assets[] = [
                "type" => $type,
                "id" => $dname,
                "original_source_file" => $downloadable,
                "pro_tools_pdf" => $downloadable,
                "website_docs" => $downloadable,
                "source_to_jpg" => $downloadable,
                "pro_tools_jpg_of_pdf" => $downloadable
              ];
            }
        }
        // @todo - normal product page
        // if (!empty($field_media)) {
        //   $image_load = $this->entityManager->getStorage('media')->load($field_media[0]);
        //   $image_file = $this->entityManager->getStorage('file')->load($image_load->field_media_image->target_id);
        //   $image_url  = $image_file->getFileUri();
        //   $image_path = file_create_url($image_url);
        // }
        $output[] = [
          "_type" => $field_type,
          "_source" => [
            "copyPoints" => $field_product_features_cp,
            "slug" => $slug[0],
            "name" => $title,
            "nid" => $node_id,
            "id" => $sku_group[0],
            "type" => $type,
            "product_category" => [$product_category_name],
            "values" => [
              "sku_overview" => "Designed to ensure safety-critical assembly -- ".$slug[0]." -- with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
              "body" => "Designed to ensure safety-critical assembly with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
              "asset_filename" => "DOT_12S1207-02.dxf"
            ],
            "assets" => $assets,
            "models" => $models_details,
          ]
        ];
      }
    }

        try {

            // $response = $this->client->search($params);
            $response['hits']['hits'] = $output;

            if (!empty($response['hits']['hits'])) {
                $source = $response['hits']['hits'][0];

                /*  if ($assetsOnly) {
                    return Json::encode($source['assets']);
                }

                /**
                 * Bandaid to prevent duplicate models
                 */
                /*  $models = [];
                for ($i = 0; $i < count($source['models']); $i++) {
                    if (!in_array($source['models'][$i]['sku'], $models)) {
                        $models[] = $source['models'][$i]['sku'];
                    } else {
                        unset($source_tmp['models'][$i]);
                    }
                }

                $source = $source_tmp; */// to avoid errors looping through array while using unset()

                return Json::encode($source);
            } else {
                return Json::encode(
                    [
                    'status'  => 204,
                    'message' => 'No results found.'
                    ]
                );
            }
        } catch (\Exception $e) {

            return Json::encode(
                [
                'status'  => 204,
                'message' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Queries for multiple products by ID/SKU
     *
     * @param array $matchQuery
     * @param array $indices
     */
    public function getMultipleProducts(array $matchQuery, array $indices = ['acquia_search_index'])
    {
        $params = [
            'index' => $indices ?: StepHelper::getEsIndexName() . '_products',
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            $matchQuery
                        ]
                    ]
                ]
            ]
        ];

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            // $response = $this->client->search($params);
            // print_r('<pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre>'); die();

            return $response;
        } catch (\Exception $e) {

            return Json::encode(
                [
                'status'  => 204,
                'message' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Simple autoComplete for Drupal custom FieldType Products
     *
     * @param array $query
     */
    public function autoCompleteFieldProducts(array $query)
    {
        $params['index'] = $query['index'];
        $params['body']  = [
            'from' => 0,
            'size' => $query['count']
        ];

        $params['body']['query'] = [
            'multi_match' => [
                'query'   => $query['q'],
                'type'    => 'phrase',
                'fields'  => [
                    'name',
                    'id',
                    'slug'
                ],
                'lenient' => true
            ]
        ];

        try {
            $results = $this->client->search($params);

            if (!empty($results['hits']['hits'])) {
                foreach ($results['hits']['hits'] as $hit) {
                    if ($hit['_type'] == 'products') {
                        $value      = $hit['_source']['name'] . ' [ID=' . $hit['_source']['id'] . ']';
                        $response[] = [
                            'value' => $value,
                            'label' => $value
                        ];
                    }
                }
                return new JsonResponse($response);
            } else {
                return new JsonResponse(
                    [[
                    'value' => null,
                    'label' => 'No Matches Found. Please search by Product’s Title, ID, or slug.'
                    ]]
                );
            }
        } catch (\Exception $e) {
            throw new ElasticSearchException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $agg
     * @param string $aggParent
     * @param string $aggNested
     */
    private function nestedAggs(string $key, array $query)
    {
        return [
            'product_filters' => [
                'filter' => [
                    'bool' => [
                        'should' => [
                            [
                                'terms' => [
                                    $key . '.raw' => $query //array_column($query, 'values', 'aggKey')[$key]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Product Catalog Filtering
     *
     * @param  array $query
     *    - [index]       string ElasticSearch Index to search
     *    - [settings]    object ElasticSearch settings
     *    - [query]       object Active Facets
     *    - [queryString] string Search Input
     *
     * Query Types
     *    - Terms or Range Query
     *       - [query]  terms, range
     *       - [aggKey] User defined. This can be anything you want
     *       - [field]  The field in the ES document to query
     *       - [vales]  List of field values to match
     *    - QueryString
     *         - Search field
     * @return array
     * @throws ElasticSearchException
     */
    public function filter(array $query)
    {
        $index   = $query['index'];
        $indices = is_array($index) ? $index : [$index];

        // Make sure indice exist
        if (empty($indices = $this->hasSearchableIndex($indices))) {
            return;
        }

        $should      = $query['should'] ?? [];
        $settings    = $query['settings'] ?? [];
        $queryString = $query['queryString'];
        $query       = $query['query'];

        // ES Paramaters
        $params = [];
        $params['index'] = $indices;
        $params['body']  = [
            'aggs' => []
        ];
        $params['body'] = array_merge($settings, $params['body']);

        // ES Query builder
        $queries = $this->loopQuery($query, true);

        // Query term AND match
        if (!empty($queries['and'])) {
            $params['body']['query']['bool'] = ['must' => []];
            foreach ($queries['and'] as $key => $and) {
                $params['body']['query']['bool']['must'][] = $and;
            }
        }
        // Query term OR match
        if (!empty($queries['or'])) {
            $params['body']['query']['bool']['must'][] = $queries['or'];
        }
        // Query RANGE match
        if (!empty($queries['range'])) {
            $params['body']['query']['bool']['must'][] = $queries['range'];
        }

        // Build aggs from query param
        foreach ($query as $q) {
            $key = $q['aggKey'];

            $params['body']['aggs'][$key] = [
                'terms' => [
                    'field' => $q['field'],
                    'order' => ['_key' => 'asc'],
                    'size'  => 3000
                ]
            ];
        }

        // Querystring
        if (isset($queryString) && !empty($queryString)) {
            if (!empty($should)) {
                $params['body']['query']['bool']['should'] = $should;
            }

            $queryStringParam[] = [
                'multi_match' => [
                    'query'     => urldecode($queryString),
                    'type'      => 'most_fields',
                    'fields'    => [
                        'id',
                        'name',
                        'type',
                        'slug',
                        'product_line',
                        'product_line_ids',
                        'product_category',
                        'product_ref.*',
                        'assets.*',
                        'values.*',
                        'models.*',
                        'models.values.*',
                    ],
                    'fuzziness' => '0',
                    'lenient'   => true,
                    'operator'  => 'and'
                ]
            ];

            if(!empty($queries['and']) || !empty($queries['or']) || !empty($queries['range'])) {
                $params['body']['query']['bool']['must'][] = $queryStringParam[0];
            }
            $params['body']['query']['bool']['should'][] = $queryStringParam[0];
        }

        // Request
        // =========================================================================

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            $response = $this->client->search($params);
            // print_r('<pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre>'); die();
            return $this->cleanFilters($response);
        } catch (\Exception $e) {
            throw new ElasticSearchException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Product Catalog Filtering To Get Correct Filter Count For Each Filter Option For A Product
     *
     * @param  array $query
     *    - [index]       string ElasticSearch Index to search
     *    - [settings]    object ElasticSearch settings
     *    - [query]       object Active Facets
     *    - [queryString] string Search Input
     *
     * Query Types
     *    - Terms or Range Query
     *       - [query]  terms, range
     *       - [aggKey] User defined. This can be anything you want
     *       - [field]  The field in the ES document to query
     *       - [vales]  List of field values to match
     *    - QueryString
     *         - Search field
     * @return array
     * @throws ElasticSearchException
     */
    public function mustFilter(array $query)
    {
        $index   = $query['index'];
        $indices = is_array($index) ? $index : [$index];

        // Make sure indice exist
        if (empty($indices = $this->hasSearchableIndex($indices))) {
            return;
        }

        $should      = $query['should'] ?? [];
        $settings    = $query['settings'] ?? [];
        $queryString = $query['queryString'];
        $query       = $query['query'];

        // ES Paramaters
        $params = [];
        $params['index'] = $indices;
        $params['body']  = [
            'aggs' => []
        ];
        $params['body'] = array_merge($settings, $params['body']);

        // ES Query builder
        $queries = $this->loopQuery($query, true);

        // Query term AND match
        if (!empty($queries['and'])) {
            $params['body']['query']['bool'] = ['must' => []];
            foreach ($queries['and'] as $key => $and) {
                $params['body']['query']['bool']['must'][] = $and;
            }
        }
        // Query term OR match
        if (!empty($queries['or'])) {
            $params['body']['query']['bool']['must'][] = $queries['or'];
        }
        // Query RANGE match
        if (!empty($queries['range'])) {
            $params['body']['query']['bool']['must'][] = $queries['range'];
        }

        // Build aggs from query param
        foreach ($query as $q) {
            $key = $q['aggKey'];

            $params['body']['aggs'][$key] = [
                'terms' => [
                    'field' => $q['field'],
                    'order' => ['_key' => 'asc'],
                    'size'  => 3000
                ]
            ];
        }

        // Querystring
        if (isset($queryString) && !empty($queryString)) {
            if (!empty($should)) {
                $params['body']['query']['bool']['should'] = $should;
            }

            $queryStringParam[] = [
                'multi_match' => [
                    'query'     => urldecode($queryString),
                    'type'      => 'most_fields',
                    'fields'    => [
                        'id',
                        'name',
                        'type',
                        'slug',
                        'product_line',
                        'product_line_ids',
                        'product_category',
                        'product_ref.*',
                        'assets.*',
                        'values.*',
                        'models.*',
                        'models.values.*',
                    ],
                    'fuzziness' => '0',
                    'lenient'   => true,
                    'operator'  => 'and'
                ]
            ];

            $params['body']['query']['bool']['must'][] = $queryStringParam[0];
        }

        // Request
        // =========================================================================

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            $response = $this->client->search($params);
            // print_r('<pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre>'); die();
            return $this->cleanFilters($response);
        } catch (\Exception $e) {
            throw new ElasticSearchException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Filters out untranslated product filters
     */
    private function cleanFilters($response)
    {
        /* if (!empty($response['hits']['hits'])) {
            for ($a = 0; $a < count($response['hits']['hits']); $a++) {
                $source = $response['hits']['hits'][$a]['_source'];
                $currentSite = StepHelper::getCurrentSite();
                $langCode = $currentSite['code'];

                if (array_key_exists('filters', $source) && count($source['filters']) > 0) {
                    foreach ($source['filters'] as $label => $values) {
                        for ($i = 0; $i < count($values); $i++) {
                            $translation = (string) t($values[$i], [], ['langcode' => $langCode]);

                            if ($translation !== $values[$i] && in_array($translation, $values)) {
                                unset($response['hits']['hits'][$a]['_source']['filters'][$label][$i]);
                            }
                        }

                        $response['hits']['hits'][$a]['_source']['filters'][$label] = array_values($response['hits']['hits'][$a]['_source']['filters'][$label]);
                    }
                }
            }
        }*/

        return $response;
    }

    /**
     * Search Elastic and Highlight assumption
     *
     * @param array $query
     *    - [index]        string Prefix for ElasticSearch Index
     *    - [settings]     object ElasticSearch settings
     *    - [query]        object Active Facets
     *    - [queryString]  string Search Input
     */
    public function search(array $query)
    {
        $indices = [
            $query['index'] . '_products',
            $query['index'] . '_models',
            $query['index'] . '_downloads',
            'cleco_legacy_documents',
            $query['index'] . '_nodes'
        ];

        // Make sure indice exist
        if (empty($indices = $this->hasSearchableIndex($indices))) {
            return;
        }

        $settings       = $query['settings'] ?? [];
        $queryString    = $query['queryString'];
        $query          = $query['query'];
        $params['body'] = [];

        $queries = $this->loopQuery($query);

        if (!empty($queries)) {
            // Query term AND match
            if (!empty($queries['and'])) {
                $params['body']['query']['bool'] = [];
                foreach ($queries['and'] as $key => $and) {
                    $params['body']['query']['bool']['must'][] = $and;
                }
            }
            // Query term OR match
            if (!empty($queries['or'])) {
                $params['body']['query']['bool']['must'][] = $queries['or'];
            }
            // Query RANGE match
            if (!empty($queries['range'])) {
                $params['body']['query']['bool']['must'][] = $queries['range'];
            }

            $params['body']['aggs'] = [];
        }

        // Build aggs from query param
        foreach ($query as $q) {
            $key = $q['aggKey'];

            $params['body']['aggs'][$key] = [
                'terms' => [
                    'field' => $q['field'],
                    'order' => ['_key' => 'asc'],
                    'size'  => 3000
                ]
            ];
        }

        if (isset($queryString)) {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query'     => urldecode($queryString),
                    'type'      => 'most_fields',
                    'fields'    => [
                        'id',
                        'name',
                        'type',
                        'slug',
                        'product_ref.*',
                        'models.*',
                        'models.values.*',
                    ],
                    'fuzziness' => '0',
                    'lenient'   => true,
                    'operator'  => 'and'
                ],
            ];

            $params['body']['query']['bool']['should'][] = [
                'multi_match' => [
                    'query'     => urldecode($queryString),
                    'type'      => 'most_fields',
                    'fields'    => [
                        'product_line',
                        'product_line_ids',
                        'product_category',
                        'assets.*',
                        'values.*',
                        'models.*',
                        'models.values.*',
                    ],
                    'fuzziness' => '0',
                    'lenient'   => true,
                    'operator'  => 'and'
                ],
            ];
        }

        $params['body']['highlight'] = [
            'pre_tags'      => '<strong>',
            'post_tags'     => '</strong>',
            'order'         => 'score',
            'fragment_size' => 100,
            'phrase_limit'  => 1,
            'fields'        => [
                'name'                    => [
                    'type' => 'plain'
                ],
                'product_line'            => [
                    'type' => 'plain'
                ],
                'product_category'        => [
                    'type' => 'plain'
                ],
                'filters.*'               => [
                    'type' => 'plain'
                ],
                'id'                      => [
                    'type' => 'plain'
                ],
                'values.asset_extension'  => [
                    'type' => 'plain'
                ],
                'values.field_body_plain' => [
                    'type' => 'plain'
                ],
                'values.field_title'      => [
                    'type' => 'plain'
                ],
                'values.field_body_basic' => [
                    'type' => 'plain'
                ]
            ]
        ];

        // Additional settings
        $params['index'] = $indices;
        $params['body']  = array_merge($settings, $params['body']);

        try {
            // print_r('<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>'); die();
            $response = $this->client->search($params);
            // print_r('<pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre>'); die();
            return $this->cleanFilters($response);
        } catch (\Exception $e) {
            throw new ElasticSearchException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Manual process ran from Drupal settign page to resize STEP images
     */
    public function createImageStyles(int $size = 10)
    {
        $indices = [
            StepHelper::getEsIndexName() . '_products',
            StepHelper::getEsIndexName() . '_models',
            StepHelper::getEsIndexName() . '_downloads',
            'cleco_legacy_documents'
        ];

        // Make sure indice exist
        if (empty($indices = $this->hasSearchableIndex($indices))) {
            return;
        }

        // May need to do batch request with from/size
        $params = [
            'index'  => $indices,
            'scroll' => '1s',
            'size'   => $size
        ];
        $params['body']['query']['match_all'] = new \stdClass();

        try {

            $response = $this->client->search($params);

            // @todo Add other image styles
            while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $product) {
                    if (!empty($product['_source']['assets'])) {
                        foreach ($product['_source']['assets'] as $type => $asset) {
                            if (gettype($asset) == 'array') {
                                // @todo Update
                                if (!empty($asset['source_to_jpg']) && !is_null($asset['source_to_jpg'])) {
                                    Drupal::service('step.image_styles_service')->resizeImg($asset['source_to_jpg']);
                                    Drupal::service('step.image_styles_service')->resizeImg($asset['source_to_jpg'], 'product_zoom_thumb');
                                }
                                if (!empty($asset['pro_tools_jpg_of_pdf']) && !is_null($asset['pro_tools_jpg_of_pdf'])) {
                                    Drupal::service('step.image_styles_service')->resizeImg($asset['pro_tools_jpg_of_pdf']);
                                    Drupal::service('step.image_styles_service')->resizeImg($asset['pro_tools_jpg_of_pdf'], 'product_zoom_thumb');
                                }
                            } else {
                                if ($type == 'pro_tools_jpg_of_pdf' || $type == 'source_to_jpg') {
                                    Drupal::service('step.image_styles_service')->resizeImg($asset);
                                    Drupal::service('step.image_styles_service')->resizeImg($asset, 'product_zoom_thumb');
                                }
                            }
                        }
                    }
                }

                $scroll_id = $response['_scroll_id'];

                // Execute a Scroll request and repeat
                $response = $this->client->scroll(
                    [
                    'scroll_id' => $scroll_id,
                    'scroll'    => '1s'
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->log('Cron ES ImageMagic error: ', 'error');
            $this->log(print_r($e, true), 'error');
        }
    }

    /**
     * Find translated URLs from product ID on product detail pages
     *
     * @param  string $slug
     * @param  array  $languages
     * @return array
     */
    public function getTranslatedRoutes(string $slug, array $languages)
    {
        $translations = [];
        $slugs        = [];
        // Get original product data so we can search by the ID in the other ES indices
        $product = $this->getSingleProduct(['slug', $slug]);

        if (!isset($product->status)) {
            // Search all translated product ES indices
            foreach ($languages as $langCode => $language) {
                $translations[] = [
                   // 'result'   => $this->getSingleProduct(['slug.raw', $slug], [StepHelper::getEsIndexName($langCode) . '_products']),
                    'langCode' => $langCode
                ];
            }

            foreach ($translations as $translation) {
                $results = Json::decode($translation['result']);
                // 'status' key only returned for errors or not results found
                if (!isset($results['status'])) {
                    $slugs[] = [
                        'slug'     => Json::decode($translation['result'])['slug'],
                        'langCode' => $translation['langCode']
                    ];
                }
            }
        }

        return $slugs;
    }

    // Private methods
    // =========================================================================

    /**
     * Ensures an ElasticSearch Index exists before searching
     *
     * @param  array $indices
     * @return mixed
     */
    private function hasSearchableIndex(array $indices)
    {
        // Make sure indice exist
        foreach ((array) $indices as $key => $indice) {
            if (!$this->indexExists($indice)) {
                unset($indices[$key]);
            }
        }

        if (empty($indices)) {
            throw new ElasticSearchException('Sorry, but no results were found at this time.');
        } else {
            return $indices;
        }
    }

    /**
     * Build query for bool
     * AND
     * "query" => [
     *   "bool" => [
     *     "must" => [
     *       "term" => ["field" => "value"]
     *     ],
     *     "must" => [
     *       "term" => ["field" => "value"]
     *     ]
     *   ]
     * ]
     *
     * OR
     * "query" => [
     *   "bool" => [
     *     "must" => [
     *       "terms" => [
     *         "field" => [values"]
     *       ]
     *     ]
     *   ]
     * ]
     *
     * @param  array $query
     * @return mixed
     */
    private function loopQuery(array $query): array
    {
        $range = [];
        $or    = [];
        $and   = [];

        if (empty($query)) {
            return Json::encode(
                [
                'status'  => 400,
                'message' => 'Error: Missing query.'
                ]
            );
        }

        foreach ($query as $group) {
            if (!empty($group['values'])) {
                switch ($group['query']) {
                case 'range':
                    $range[] = [
                        'range' => [
                            $group['field'] => [
                                'lte' => $group['values'][1],
                                'gte' => $group['values'][0]
                            ]
                        ]
                    ];

                    break;
                default:
                    // AND query
                    // $and[] = [
                    //     'terms' => [
                    //         $group['field'] => $this->decodeValues($group['values'])
                    //     ]
                    // ];
                    // Every filter value must match exactly
                    foreach ($this->decodeValues($group['values']) as $value) {
                        $and[] = [
                            'term' => [$group['field'] => $value]
                        ];
                    }
                    // Example of OR terms query
                    // Basically says the terms field needs to match at least one of the values
                    // $or[] = [
                    //     'terms' => [
                    //         $group['field'] => $this->decodeValues($group['values'])
                    //     ]
                    // ];
                }
            }
        }

        return [
            'or'    => $or,
            'and'   => $and,
            'range' => $range
        ];
    }

    /**
     * Decode any special characters that may exist in the URL
     *
     * @param  $values
     * @return mixed
     */
    private function decodeValues($values)
    {
        if (is_array($values)) {
            $newValues = [];
            foreach ($values as $value) {
                $newValues[] = urldecode($value);
            }
            return $newValues;
        } else {
            //return urldecode($values);
            // Explode our string into array
            return $this->explodeMultipleFilters(urldecode($values));
        }
    }

    private function explodeMultipleFilters($values)
    {
        return preg_split('/[_]+/', $values);
    }

    private function getElasticHosts(): array
    {
        return explode(', ', $this->settings->get('step_es_hosts'));
    }

  /**
   * Model data.
   *
   * @return array
   *   returning output array
   */
  public function getProductModelDetails($models) {
    if (!empty($models)) {
      foreach ($models as $model) {

        $model_details = \Drupal::entityTypeManager()
          ->getStorage('node')->load($model);
        $model_spec = $model_details->get('field_model_specification')
          ->referencedEntities();
        $values = [];
        foreach ($model_spec as $key => $spec) {
          $exploded_label = explode(':~:', $spec->label());
          $lable_value = isset($exploded_label[0]) ? trim($exploded_label[0]) : '';
          $keyvalue = preg_replace('/[^\w]+/', '_', $lable_value);
          $keyvalue = preg_replace('/^_+|_+$/', '', $keyvalue);
          $keyvalue = strtolower($keyvalue);
          $valuelable = isset($exploded_label[1]) ? trim($exploded_label[1]) : '';
          $values[$keyvalue] = $valuelable;
        }
        $models_data[] = [
          "sku" => $model_details->getTitle(),
          "name" => $model_details->getTitle(),
          "slug" => strtolower($model_details->getTitle()),
          "values" => $values,
        ];
      }
      return $models_data;
    }

  }

  /**
   * Downloads data.
   *
   * @return array
   *   returning output array
   */
  public function getMediaDownload() {
    $index = Index::load('downloads_index');
    $query = $index->query();
    $results = $query->execute();
    $count = $results->getresultCount();
    foreach ($results as $result11) {
      $product_category_name = [];
      $product_category = [];
      $resultItemFields = $result11->getFields();
      $lang = $resultItemFields['field_language']->getvalues();
      $listing_image = $resultItemFields['field_listing_image']->getvalues();
      if (!empty($listing_image)) {
        $image_load = $this->entityManager->getStorage('media')->load($listing_image[0]);
        $image_file = $this->entityManager->getStorage('file')->load($image_load->field_media_image->target_id);
        $image_url  = $image_file->getFileUri();
        $media_url = file_create_url($image_url);
      }
      $media_file = $resultItemFields['field_media_file']->getvalues();
      if (!empty($media_file)) {
        $download_file = $this->entityManager->getStorage('file')->load($media_file[0]);
        $download_file_url  = $download_file->getFileUri();
        $download_file_name = $download_file->getFilename();
        $download_file_path = file_create_url($download_file_url);
      }
      $product_category = $resultItemFields['field_product_category']->getvalues();
      if(!empty($product_category)) {
      foreach ($product_category as $key => $value) {
        $term = $this->entityManager->getStorage('taxonomy_term')->load($value);
        if(!empty($term)){
        $product_category_name[] = $term->get('name')->value;
        }
      }
      $category = implode(', ', $product_category_name);
    }
    else {
        $category = '';
    }
      $type = $resultItemFields['field_type']->getvalues();
      $name = $resultItemFields['name']->getvalues();
      $name = implode(', ', $name);
      $output[] = [
        "_type" => "downloads",
        "_source" => [
          "id" => $download_file_name,
          "name" => $name,
          "type" => "Engineering Drawings",
          "product_category" => [$category],
          "values" => [
            "asset_extension" => 'pdf',
            "asset_size" => $download_file->getSize(),
            "asset_format" => "PDF (Portable Document Format application)",
            "asset_mime_type" => $download_file->getMimeType(),
          ],
          "assets" => [
            "original_source_file" => $download_file_path,
            "pro_tools_jpg_of_pdf" => "$media_url",
            "pro_tools_pdf" => $download_file_path,
          ],
        ],
      ];
    }
    $json['hits'] = [
      "total" => $count,
      "max_score" => NULL,
      "hits" => $output,
    ];
    return($json);
  }

}
