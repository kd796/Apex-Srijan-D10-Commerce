<?php

namespace Drupal\cleco_vuejs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cleco_vuejs\Utils\StepHelper;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RouteController constructor.
   */
  public function __construct(Request $request,LanguageManagerInterface $languageManager,EntityTypeManagerInterface $entityTypeManager) {
    $this->request = $request;
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
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
   */
  public function productCatalogTitle() {
    return $this->t('Product Catalog');
  }

  /**
   * SINGLE PRODUCT.
   *
   * Render array for the single product template.
   *
   * @return array
   */
  public function productSingle() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $slug = $this->request->get('product');
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'langcode'   => $langcode,
        'field_slug' => $slug,
    ]);
    if (!empty($nodes)) {
      $features = [];
      $asset1 = [];
      $asset2 = [];
      $output = [];
      foreach ($nodes as $node) {
        $fields = $node->getFields();
        $bundle = $node->bundle();
        $sku_group = isset($fields['field_sku_group']) ? $fields['field_sku_group']->getValue()[0]['value'] : $fields['field_sku']->getValue()[0]['value'];
        $title = $fields['title']->getValue()[0]['value'];
        $slug = isset($fields['field_slug']) ? $fields['field_slug']->getValue()[0]['value'] : '';
        $node_id = $node->id();
        $field_product_features_cp = isset($fields['field_product_features_cp']) ? $fields['field_product_features_cp'] : '';
        if (!empty($field_product_features_cp)) {
          foreach ($field_product_features_cp as $productFeatures) {
            $features[] = $productFeatures->value;
          }
        }
        $field_media = isset($fields['field_media']) ? $fields['field_media'] : '';

        if (!empty($field_media)) {
          foreach ($field_media as $media) {
            $image_load = $this->entityTypeManager->getStorage('media')->load($media->get('target_id')->getValue());
            $image_name = $image_load->get('name')->value;
            $image_file = $this->entityTypeManager->getStorage('file')->load($image_load->field_media_image->target_id);
            $imagename_without_ext = pathinfo($image_name, PATHINFO_FILENAME);
            $image_url = $image_file->getFileUri();
            $image_path = file_create_url($image_url);
            $asset1[] = [
              "type" => "Primary Image",
              "id" => $imagename_without_ext,
              "original_source_file" => $imagename_without_ext . '.tif',
              "source_to_jpg" => $imagename_without_ext,
            ];
          }
        }
        $field_downloads = $fields['field_downloads'] ?? $fields['field_downloads'] ?? '';
        if (!empty($field_downloads)) {
          foreach ($field_downloads as $productDownload) {
            $downloads_list = $this->entityTypeManager->getStorage('media')->load($productDownload->get('target_id')->getValue());

            $type = str_replace("_", " ", $downloads_list->get('field_type')->value);
            $type = ucwords($type);
            if (!empty($type)) {
              $type = StepHelper::translate($type);
            }
            $dname = $downloads_list->get('name')->value;
            if (!empty($downloads_list->field_listing_image->getValue())) {
              $thumbnail_id = $this->entityTypeManager->getStorage('media')->load($downloads_list->field_listing_image->getValue()[0]['target_id']);
              $thumbnail_url = $thumbnail_id->field_media_image->entity->getFileUri();
              $thumbnailImg = file_create_url($thumbnail_url);
            }
            $file_id = $this->entityTypeManager->getStorage('file')->load($downloads_list->field_media_file->target_id);
            $filename = $file_id->get('filename')->value;

            $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);

            $file_url = $file_id->getFileUri();
            $downloadable = file_create_url($file_url);

            $asset2[] = [
              "type" => $type,
              "id" => $filename_without_ext,
              "original_source_file" => $filename,
              "pro_tools_pdf" => $filename,
              "source_to_jpg" => $filename_without_ext,
              "pro_tools_jpg_of_pdf" => $filename_without_ext,
            ];
          }

        }
      }

      $assets = array_merge($asset1, $asset2);
      $output[] = [
        "_type" => $bundle,
        "_source" => [
          "copyPoints" => $features,
          "slug" => $slug,
          "name" => $title,
          "nid" => $node_id,
          "id" => $sku_group,
          "product_image" => isset($image_path) ? $image_path : '',
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
      return new Response($this->t('No results found'), 200, array('Content-Type' => 'text/html'));
    }
  }

  /**
   * DOWNLOADS.
   *
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
   * DOWNLOADS TITLE.
   *
   * Title for the downlaods template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
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
