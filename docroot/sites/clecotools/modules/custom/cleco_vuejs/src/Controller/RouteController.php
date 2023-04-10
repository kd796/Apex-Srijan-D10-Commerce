<?php

namespace Drupal\cleco_vuejs\Controller;

use Drupal\Core\Controller\ControllerBase;

class RouteController extends ControllerBase
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request 
     */
    public $request;

    /**
     * @var string 
     */
    public $slug;

    /**
     * @var array|null 
     */
    public $product;

    /**
     * RouteController constructor.
     * Set pro
     */
    public function __construct()
    {
        $this->request = \Drupal::request();
        $this->slug    = $this->request->get('product');
        if ($this->slug) {
            $product = \Drupal::service('step.elastic_search_service')
                ->getSingleProduct(['slug.raw', $this->slug]);
            if ($product) {
                $this->product = json_decode($product, true);
            }
        }
    }

    public function cellCore()
    {
        return [
            '#theme' => 'landing-pages/cellcore'
        ];
    }

    /**
     * PRODUCT CATALOG
     * Render array for the product catalog template
     *
     * @return array
     */
    public function productCatalog()
    {
        return [
            '#theme' => 'products/catalog'
        ];
    }

    /**
     * PRODUCT CATALOG TITLE
     * Title for the product catalog interface
     *
     * @return \Drupal\Core\StringTranslation\TranslatableMarkup
     */
    public function productCatalogTitle()
    {
        return $this->t('Product Catalog');
    }

    /**
     * SINGLE PRODUCT
     * Render array for the single product template
     *
     * @return array
     */
    public function productSingle()
    {
        $theme = 'products/single';
            if ($this->product['_type'][0] == 'enhanced_product') {
                $theme = 'products/enhanced';
            }
        return [
            '#theme'   => $theme,
            '#product' => $this->product['_source']
        ];
    }

    /**
     * SINGLE PRODUCT TITLE
     * Title for the single product template pulled from the URL slug
     *
     * @return \Drupal\Core\StringTranslation\TranslatableMarkup
     */
    public function productSingleTitle()
    {
        return $this->t($this->product['_source']['values']['coupon_headline'] ?? $this->product['_source']['name']);
    }

    /**
     * DOWNLOADS
     * Render array for the downloads template
     *
     * @return array
     */
    public function downloads()
    {
        return [
            '#theme' => 'downloads/index'
        ];
    }

    /**
     * DOWNLOADS TITLE
     * Title for the downlaods template.
     *
     * @return \Drupal\Core\StringTranslation\TranslatableMarkup
     */
    public function downloadsTitle()
    {
        return $this->t('Downloads');
    }

    /**
     * SEARCH RESULTS
     * Render array for the search results template.
     *
     * @return array
     */
    public function searchResults()
    {
        return [
            '#theme' => 'search/results',
            '#q'     => $this->request->get('q'),
            '#cache' => [
                'max-age' => 0
            ],
        ];
    }

    /**
     * SEARCH RESULTS TITLE
     * Title for the search results template.
     *
     * @return \Drupal\Core\StringTranslation\TranslatableMarkup
     */
    public function searchResultsTitle()
    {
        $q = $this->request->get('q');

        if ($q) {
            return $this->t('Search for “@query”', ['@query' => $q]);
        } else {
            return $this->t('Search');
        }
    }
}
