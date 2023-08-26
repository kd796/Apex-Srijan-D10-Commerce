<?php

namespace Drupal\cleco_vuejs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cleco_vuejs\Utils\StepHelper;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\cleco_vuejs\Services\SolrSearchApiService;
use Drupal\cleco_vuejs\Services\VueDataFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for Product pages.
 */
class RouteController extends ControllerBase {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  public $request;

  /**
   * {@inheritdoc}
   */
  public $slug;

  /**
   * {@inheritdoc}
   */
  public $product;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * RouteController constructor.
   */
  public function __construct(SolrSearchApiService $solr_search_service, VueDataFormatter $vue_data_formatter, Request $request, LanguageManagerInterface $languageManager, EntityTypeManagerInterface $entityTypeManager) {
    $this->solrSearchService = $solr_search_service;
    $this->vueDataFormatter = $vue_data_formatter;
    $this->request = $request;
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * The container object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cleco_vuejs.solr_search_service'),
      $container->get('cleco_vuejs.vue_data_formatter'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Cellcore.
   */
  public function cellCore() {
    return [
      '#theme' => 'landing-pages/cellcore',
    ];
  }

  /**
   * PRODUCT CATALOG.
   *
   * Render array for the product catalog template.
   *
   * @return array
   *   for theme.
   */
  public function productCatalog() {
    return [
      '#theme' => 'products/catalog',
    ];
  }

  /**
   * PRODUCT CATALOG TITLE.
   *
   * Title for the product catalog interface.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Label for catalog.
   */
  public function productCatalogTitle() {
    return $this->t('Tool Catalog');
  }

  /**
   * SINGLE PRODUCT.
   *
   * Render array for the single product template.
   *
   * @return array
   *   Product fields value array.
   */
  public function productSingle() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $slug = $this->request->get('product');
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'langcode'   => $langcode,
        'field_slug' => $slug,
        'status' => 1,
      ]);
    if (!empty($nodes)) {
      $features = [];
      $asset1 = [];
      $asset2 = [];
      $output = [];
      $assets = [];
      foreach ($nodes as $node) {
        $fields = $node->getFields();
        $bundle = $node->bundle();
        $sku_group = (isset($fields['field_sku_group']) && !empty($fields['field_sku_group']->getValue())) ?
          $fields['field_sku_group']->getValue()[0]['value'] : (isset($fields['field_sku']) ? $fields['field_sku']->getValue()[0]['value'] : '');
        $title = '';
        if (!empty($fields['field_long_description']->getValue())) {
          $title = $fields['field_long_description']->getValue()[0]['value'];
        }
        else {
          $title = $fields['title']->getValue()[0]['value'];
        }
        $slug = isset($fields['field_slug']) ? $fields['field_slug']->getValue()[0]['value'] : '';
        $node_id = $node->id();
        $field_product_features_cp = $fields['field_product_features_cp'] ?? '';
        if (!empty($field_product_features_cp)) {
          foreach ($field_product_features_cp as $productFeatures) {
            if (isset($productFeatures->value)) {
              $features[] = $productFeatures->value;
            }
          }
        }
        $field_media = isset($fields['field_media']) ? $fields['field_media'] : '';
        $field_product_images = isset($fields['field_product_images']) ? $fields['field_product_images'] : '';
        $listing_images = array_merge($field_media->getValue(), $field_product_images->getValue());
        if (!empty($listing_images)) {
          foreach ($listing_images as $media) {
            $image_load = $this->entityTypeManager->getStorage('media')->load($media['target_id']);
            $style_product = $this->entityTypeManager->getStorage('image_style')->load('simple_product');
            $image_name = $image_load->get('name')->value;
            if (isset($image_load->field_media_image)) {
              $image_file = $this->entityTypeManager->getStorage('file')->load($image_load->field_media_image->target_id);
              $image_url = $image_file->getFileUri();
              $image_path = $style_product->buildUrl($image_url);
              $image_asset[] = [
                "source_to_jpg" => $image_path,
              ];
              $asset1[] = [
                "type" => "Primary Image",
                "id" => $image_name,
              ];
            }
          }
        }
        $footnotes = $fields['field_footnotes']->getValue();
        $models = $fields['field_product_models']->getValue();
        $models_details = '';
        $filters = [];
        if (!empty($models)) {
          $models_details = $this->getProductModelDetails($fields['field_product_models']->getValue());
          foreach ($models_details as $model_detail) {
            foreach ($model_detail['values'] as $model_key => $model_value) {
              $filters[$model_key][] = $model_value;
            }
          }
        }
        $product_category_name = [];
        $product_categories = $fields['field_product_classifications']->getValue();
        if (!empty($product_categories)) {
          foreach ($product_categories as $product_category) {
            $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($product_category['target_id']);
            if (empty($term) || $term->status->value != 1) {
              continue;
            }
            if (!empty($term)) {
              $product_category_name[] = $term->get('name')->value;
              $term_parent = $term->get('parent')->getValue()[0]['target_id'];
              if (!$term_parent) {
                continue;
              }
              $parent_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_parent);
              $product_category_name[] = $parent_term->get('name')->value;
            }
          }
        }
        $field_downloads = $fields['field_downloads'] ?? $fields['field_downloads'] ?? '';
        if (!empty($field_downloads)) {
          foreach ($field_downloads as $productDownload) {
            $downloads_list = $this->entityTypeManager->getStorage('media')->load($productDownload->get('target_id')->getValue());

            $type_str = '';
            if (isset($downloads_list) && $downloads_list->hasField('field_type')) {
              $field_type = $downloads_list->get('field_type')->getValue();
              if (!empty($field_type[0]['value'])) {
                $type_str = $field_type[0]['value'];
              }
            }
            $type = str_replace("_", " ", $type_str);
            $type = ucwords($type);
            if ($type == 'Flyer Brochure') {
              $type = 'Flyer/Brochure';
            }
            if (!empty($type)) {
              $type = StepHelper::translate($type);
            }
            $dname = $downloads_list->get('name')->value;
            $downloadable = '';
            $thumbnailImg = '';
            if (isset($downloads_list->field_listing_image) && !empty($downloads_list->field_listing_image->getValue())) {
              $thumbnail_id = $this->entityTypeManager->getStorage('media')->load($downloads_list->field_listing_image->getValue()[0]['target_id']);
              $thumbnail_file = $this->entityTypeManager->getStorage('file')->load($thumbnail_id->field_media_image->target_id);
              $thumbnail_url = $thumbnail_file->getFileUri();
              $thumbnailImg = \Drupal::service('file_url_generator')->generateAbsoluteString($thumbnail_url);
            }
            if (isset($downloads_list->field_media_file) && !empty($downloads_list->field_media_file->target_id)) {
              $file_id = $this->entityTypeManager->getStorage('file')->load($downloads_list->field_media_file->target_id);
              $file_url = $file_id->getFileUri();
              $downloadable = \Drupal::service('file_url_generator')->generateAbsoluteString($file_url);
            }
            $asset2[] = [
              "type" => $type,
              "id" => $dname,
              "original_source_file" => $downloadable,
              "pro_tools_pdf" => $downloadable,
              "source_to_jpg" => $thumbnailImg,
              "pro_tools_jpg_of_pdf" => $thumbnailImg,
            ];
          }
        }
      }
      if (!empty($asset1) && !empty($asset2)) {
        $assets = array_merge($asset1, $asset2);
      }
      elseif (!empty($asset1)) {
        $assets = $asset1;
      }
      elseif (!empty($asset2)) {
        $assets = $asset2;
      }
      $product_category_array = [
        "product_category" => $product_category_name,
      ];
      $filters = array_merge($filters, $product_category_array);
      $related_products = [];
      $related_products_data = $this->getRelatedProducts($filters);
      $content = $related_products_data->getContent();
      $data = json_decode($content, TRUE);
      $related_products = $data['hits'];
      // Get first 4 related products.
      $related_products = array_slice($related_products['hits'], 0, 4);
      $output[] = [
        "_type" => $bundle,
        "_source" => [
          "copyPoints" => $features,
          "slug" => $slug,
          "name" => $title,
          "nid" => $node_id,
          "id" => $sku_group,
          "related_products" => $related_products,
          "product_category" => $product_category_name,
          "values" => [
            "sku_overview" => "Designed to ensure safety-critical assembly -- " . $slug . " -- with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
            "body" => "Designed to ensure safety-critical assembly with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
            "asset_filename" => "DOT_12S1207-02.dxf",
            "footnotes" => isset($footnotes[0]) ? $footnotes[0]['value'] : '',
          ],
          "assets" => $assets,
          "models" => $models_details,
          "image_asset" => $image_asset,
        ],
      ];
      $response['hits']['hits'] = $output;

      if (!empty($response['hits']['hits'])) {
        $this->product = $response['hits']['hits'][0];
        $theme = 'products/single';
        if ($this->product['_type'] == 'enhanced_product') {
          $theme = 'products/enhanced';
        }
        return [
          '#theme'   => $theme,
          '#product' => $this->product['_source'],
        ];
      }
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Get Related Products for current product.
   */
  public function getRelatedProducts(array $filters) {
    $searchproducts = $this->solrSearchService->relatedProducts($filters);
    $json = $this->vueDataFormatter->formatSearchResults($searchproducts);

    return new JsonResponse($json, 200);
  }

  /**
   * SINGLE PRODUCT TITLE.
   *
   * Title for the single product template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Single product title.
   */
  public function productSingleTitle() {
    if (!empty($this->productSingle()['#product']['name'])) {
      $pname = (string) $this->productSingle()['#product']['name'];
      return $this->t('@pname', ['@pname' => $pname]);
    }
  }

  /**
   * Model Specification table data.
   *
   * @return array
   *   returning output array
   */
  public function getProductModelDetails($models) {
    if (!empty($models)) {
      foreach ($models as $model) {
        $model_details = \Drupal::entityTypeManager()
          ->getStorage('node')->load($model['target_id']);
        if (!$model_details || !$model_details->isPublished()) {
          continue;
        }
        $sr_number = $model_details->get('field_sr_number')->value;
        $model_spec = $model_details->get('field_model_specification')
          ->referencedEntities();
        $values = [];
        foreach ($model_spec as $key => $spec) {
          if ($spec->status->value != 1) {
            continue;
          }
          $exploded_label = explode(':~:', $spec->label());
          $lable_value = $spec->field_specification_attr_key->value;
          $valuelable = isset($exploded_label[1]) ? trim($exploded_label[1]) : '';
          $values[$lable_value] = $valuelable;
        }
        $models_data[] = [
          "sku" => $model_details->getTitle(),
          "name" => $model_details->getTitle(),
          "slug" => strtolower($model_details->getTitle()),
          "number" => $sr_number,
          "values" => $values,
        ];

      }
      return $models_data;
    }

  }

  /**
   * DOWNLOADS.
   *
   * Render array for the downloads template.
   *
   * @return array
   *   Downloads theme.
   */
  public function downloads() {
    return [
      '#theme' => 'downloads/index',
    ];
  }

  /**
   * DOWNLOADS TITLE.
   *
   * Title for the downlaods template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Downloads title.
   */
  public function downloadsTitle() {
    return $this->t('Downloads');
  }

  /**
   * SEARCH RESULTS.
   *
   * Render array for the search results template.
   *
   * @return array
   *   Cahce age.
   */
  public function searchResults() {
    return [
      '#theme' => 'search/results',
      '#q'     => $this->request->get('q'),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * SEARCH RESULTS TITLE.
   *
   * Title for the search results template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Search result title.
   */
  public function searchResultsTitle() {
    $q = $this->request->get('q');

    if ($q) {
      return $this->t('Search for “@query”', ['@query' => $q]);
    }
    else {
      return $this->t('Search');
    }
  }

}
