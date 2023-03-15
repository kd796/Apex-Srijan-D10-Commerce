<?php

/**
 * @file
 * Contains \Drupal\cleco_vuejs\Controller\ApiController.
 */
namespace Drupal\cleco_vuejs\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

class ApiController extends ControllerBase
{
    /**
     * @var mixed
     */
    protected $allowAnonymous = true;

    /**
     * @param Request $request
     * @param Bool    $return
     *
     * @return JsonResponse
     */
    public function actionFilterProducts(Request $request, Bool $return = false)
    {   
        // change the index name which is 'sitewide'
        /* $index = \Drupal\search_api\Entity\Index::load('enhance_product_index');
        $query = $index->query();
        $query->keys('text to search');
        $params = $this->getParams($request);

        $results = $query->execute();
        $results = $results->getResultItems();
        $output = [];

        foreach ($results as $result) {
        $name = $result->getField('title')->getValues();
        $type = $result->getField('type')->getValues();
        $body = $result->getField('body')->getValues();

        $output[] = [
        "_type" => $type[0]->getText(),
        "_source" => [
          "slug" => "celltek-cordless-electric-right-angle",
          "name" => $name[0]->getText(),
          "type" => "Engineering Drawings",
          "product_category" => ["Specialty Tools", "Specialty Tools"],
          "values" => [
            "sku_overview" => "Designed to ensure safety-critical assembly with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
            "body" => "Designed to ensure safety-critical assembly with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
            "asset_filename" => "DOT_12S1207-02.dxf"
          ],
          "assets" => [
            [
              "type" => "Primary Image",
              "id" => "CLE_CTBAW153_FRNT_MAIN"
            ]
          ]
        ]
        ];

        }*/
        //$search_api_index = Index::load('acquia_search_index')->query()->execute();

        $index = Index::load('acquia_search_index');
        $query = $index->query();
        $parse_mode = \Drupal::service('plugin.manager.search_api.parse_mode')
                      ->createInstance('direct');
        $query->setParseMode($parse_mode);
        $query->addCondition('type', 'enhanced_product');
        $results = $query->execute();
        
        foreach ($results as $result11) {
            $resultItemFields = $result11->getFields();
            $type1[] = $resultItemFields['type']->getvalues();
            $sku = $resultItemFields['field_sku']->getvalues();
            $type1 = $resultItemFields['type']->getvalues();
            $title1 = $resultItemFields['title']->getvalues();
            $title = $title1[0]->getText();
            $slug = $resultItemFields['field_slug']->getvalues();
            $ss_type = $resultItemFields['field_slug']->getvalues();

            $output[] = [
            "_type" => $type1,
            "_source" => [
            "slug" => $slug,
            "name" => $title,
            "type" => "Engineering Drawings",
            "product_category" => ["Specialty Tools", "Specialty Tools"],
            "values" => [
            "sku_overview" => "Designed to ensure safety-critical assembly -- ".$slug[0]." -- with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
            "body" => "Designed to ensure safety-critical assembly with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
            "asset_filename" => "DOT_12S1207-02.dxf"
            ],
            "assets" => [
            [
            "type" => "Primary Image",
            "id" => "CLE_CTBAW153_FRNT_MAIN"
            ]
            ]
            ]
            ];
        }

        $json['hits']['hits'] = $output;

        return new JsonResponse($json, 200);

        //$json = file_get_contents(__DIR__ .'/sample-search.json');
        //return new JsonResponse(json_decode($json), 200);

    }

    /**
     * @param Request $request
     * @param Bool    $return
     * @param Array   $query
     *
     * @return JsonResponse
     */
    public function actionFilterDownloads(Request $request, Bool $return = false, Array $query = [])
    {
        $json = file_get_contents(__DIR__ .'/sample-search.json');
        return new JsonResponse(json_decode($json), 200);   
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function actionSearchDownloads(Request $request)
    {
        $json = file_get_contents(__DIR__ .'/sample-search.json');
        return new JsonResponse(json_decode($json), 200);
    }

    /**
     * Drupal custom field
     *
     * @param Request $request
     */
    public function actionAutoCompleteFieldProducts(Request $request)
    {
      
    }

    /**
     * @param Request $request
     */
    public function actionSearch(Request $request)
    {
        $json = file_get_contents(__DIR__ .'/sample-search.json');
        return new JsonResponse(json_decode($json), 200);
    }

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
        settype($params['page'], 'integer');
        settype($params['perPage'], 'integer');
        $params['settings'] = [
        'from' => ($params['page'] - 1) * $params['perPage'],
        'size' => $params['perPage']
        ];
        $params['queryString'] = $request->get('q') ?? null;

        return $params;
    }
}
