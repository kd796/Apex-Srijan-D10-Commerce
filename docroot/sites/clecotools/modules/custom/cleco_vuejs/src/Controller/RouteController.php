<?php

namespace Drupal\cleco_vuejs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cleco_vuejs\Utils\StepHelper;

/**
 *
 */
class RouteController extends ControllerBase {

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
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * RouteController constructor.
   * Set pro
   */
  public function __construct() {
    $this->request = \Drupal::request();
  }

  public function cellCore() {
    return [
      '#theme' => 'landing-pages/cellcore',
    ];
  }

  /**
   * PRODUCT CATALOG
   * Render array for the product catalog template
   *
   * @return array
   */
  public function productCatalog() {
    return [
      '#theme' => 'products/catalog',
    ];
  }

  /**
   * PRODUCT CATALOG TITLE
   * Title for the product catalog interface
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function productCatalogTitle() {
    return $this->t('Product Catalog');
  }

  /**
   * SINGLE PRODUCT
   * Render array for the single product template.
   *
   * @return array
   */
  public function productSingle() {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $slug = $this->request->get('product');
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'langcode'   => $langcode,
        'field_slug' => $slug,
      ]);

    foreach ($nodes as $node) {
      $fields = $node->getFields();
      $bundle = $node->bundle();
      $sku_group = $fields['field_sku_group'] ? $fields['field_sku_group']->getValue()[0]['value'] : $fields['field_sku']->getValue()[0]['value'];
      $title = $fields['title']->getValue()[0]['value'];

      $slug = $fields['field_slug']->getValue()[0]['value'];
      $node_id = $node->id();
      $field_product_features_cp = $fields['field_product_features_cp'];
      if (!empty($field_product_features_cp)) {
        foreach ($field_product_features_cp as $productFeatures) {
          $features[] = $productFeatures->value;
        }
      }
      $field_media = $fields['field_media'];

      if (!empty($field_media)) {
        foreach ($field_media as $media) {
          $image_load = \Drupal::entityTypeManager()->getStorage('media')->load($media->get('target_id')->getValue());
          $image_file = \Drupal::entityTypeManager()->getStorage('file')->load($image_load->field_media_image->target_id);
          $image_url  = $image_file->getFileUri();
          $image_path = file_create_url($image_url);
        }
      }
      $field_downloads = $fields['field_downloads'];
      if (!empty($field_downloads)) {
        foreach ($field_downloads as $productDownload) {
          $downloads_list = \Drupal::entityTypeManager()->getStorage('media')->load($productDownload->get('target_id')->getValue());
          $type = str_replace("_", " ", $downloads_list->get('field_type')->value);
          $type = ucwords($type);
          if (!empty($type)) {
            $type = StepHelper::translate($type);
          }
          $dname = $downloads_list->get('name')->value;
          $file_id = \Drupal::entityTypeManager()->getStorage('file')->load($downloads_list->field_media_file->target_id);
          $file_url = $file_id->getFileUri();
          $downloadable = file_create_url($file_url);
          $assets[] = [
            "type" => $type,
            "id" => $dname,
            "original_source_file" => $downloadable,
            "pro_tools_pdf" => $downloadable,
            "website_docs" => $downloadable,
            "source_to_jpg" => $downloadable,
            "pro_tools_jpg_of_pdf" => $downloadable,
          ];
        }
      }
    }
    $output[] = [
      "_type" => $bundle,
      "_source" => [
        "copyPoints" => $features,
        "slug" => $slug,
        "name" => $title,
        "nid" => $node_id,
        "id" => $sku_group,
        "product_image" => $image_path,
        "product_category" => ["Specialty Tools"],
        "values" => [
          "sku_overview" => "Designed to ensure safety-critical assembly -- " . $slug . " -- with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
          "body" => "Designed to ensure safety-critical assembly with best-in-class accuracy, they are also the fastest cordless assembly tools in its class.",
          "asset_filename" => "DOT_12S1207-02.dxf",
        ],
        "assets" => $assets,
      ],
    ];

    $response['hits']['hits'] = $output;

    if (!empty($response['hits']['hits'])) {
      $this->product = $response['hits']['hits'][0];
    }

    $theme = 'products/single';
    if ($this->product['_type'] == 'enhanced_product') {
      $theme = 'products/enhanced';
    }
    return [
      '#theme'   => $theme,
      '#product' => $this->product['_source'],
    ];
  }

  /**
   * DOWNLOADS
   * Render array for the downloads template.
   *
   * @return array
   */
  public function downloads() {
    return [
      '#theme' => 'downloads/index',
    ];
  }

  /**
   * DOWNLOADS TITLE
   * Title for the downlaods template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function downloadsTitle() {
    return $this->t('Downloads');
  }

  /**
   * SEARCH RESULTS
   * Render array for the search results template.
   *
   * @return array
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
   * SEARCH RESULTS TITLE
   * Title for the search results template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
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
