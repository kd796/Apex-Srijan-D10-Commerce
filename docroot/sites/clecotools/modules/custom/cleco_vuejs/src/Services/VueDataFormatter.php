<?php

namespace Drupal\cleco_vuejs\Services;

use Drupal\search_api\Query\ResultSetInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cleco_vuejs\Utils\StepHelper;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Service to format search results as expected by vuejs.
 */
class VueDataFormatter {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * Constructs a solr search service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\path_alias\AliasManagerInterface $path_alias_manager
   *   The path alias manager.
   *
   * @return null
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, AliasManagerInterface $path_alias_manager) {
    $this->entityManager = $entity_manager;
    $this->pathAliasManager = $path_alias_manager;
  }

  protected $itemTypes = [
    'product' => 'products',
    'enhanced_product' => 'products',
    'article' => 'nodes',
    'news_insights' => 'nodes',
    'solutions' => 'nodes',
    'product_downloads' => 'downloads',
  ];

  protected $facetReplacements = [
    'item_type' => 'type',
    'media_product_category' => 'product_category',
  ];

  /**
   * Format the search results as expected by vuejs.
   *
   * @param \Drupal\search_api\Query\ResultSetInterface $results
   *   The results from search_api query.
   *
   * @return array
   *   The formatted data as expected by vuejs.
   */
  public function formatSearchResults(ResultSetInterface $results) {
    $data = [];

    if ($results->getResultCount() === 0) {
      $data['hits']['hits'] = [];
      $data['hits']['total'] = 0;
      return $data;
    }

    $items = [];
    foreach ($results as $result) {
      $resultItemFields = $result->getFields();
      $sku_overview = '';
      $slug = '';
      $title = '';
      $assets = [];
      $highlight = [];

      // Entity type.
      $entity_type = isset($resultItemFields['entity_type']) ? $resultItemFields['entity_type']->getvalues()[0] : '';
      if ($entity_type === 'node') {
        // Nid.
        $nid = isset($resultItemFields['nid']) ? $resultItemFields['nid']->getvalues()[0] : '';

        // If 'field_long_description' is not available take from title field.
        $coupon_heading = isset($resultItemFields['field_long_description']) ? $resultItemFields['field_long_description']->getvalues() : [];
        $title = isset($coupon_heading[0]) ? $coupon_heading[0]->getText() : '';
        if (!$title) {
          $title_ob = isset($resultItemFields['title']) ? $resultItemFields['title']->getvalues() : [];
          $title = isset($title_ob[0]) ? $title_ob[0]->getText() : '';
        }

        // Image.
        $listing_image = isset($resultItemFields['listing_image_name']) ? $resultItemFields['listing_image_name']->getvalues() : [];
        $image_name = isset($listing_image[0]) ? explode('.', $listing_image[0])[0] : '';
        $image_file = isset($resultItemFields['listing_image_url']) ? $resultItemFields['listing_image_url']->getvalues() : [];
        $image_file = $image_file[0] ?? '';

        // Description.
        $sku_overview = isset($resultItemFields['body']) ? $resultItemFields['body']->getvalues() : [];
        $sku_overview = isset($sku_overview[0]) ? $sku_overview[0]->getText() : '';

        // Slug.
        $slug = isset($resultItemFields['field_slug']) ? $resultItemFields['field_slug']->getvalues() : '';

        // Type.
        $type = isset($resultItemFields['type']) ? $resultItemFields['type']->getvalues()[0] : '';

        // Overwrite for enhanced products.
        $enhanced_product_image = isset($resultItemFields['enhanced_product_image']) ? $resultItemFields['enhanced_product_image']->getvalues() : [];
        $image_name = isset($enhanced_product_image[0]) ? explode('.', $enhanced_product_image[0])[0] : $image_name;
        $enhanced_image_file = isset($resultItemFields['enhanced_product_image_url']) ? $resultItemFields['enhanced_product_image_url']->getvalues() : [];
        $image_file = $enhanced_image_file[0] ?? $image_file;
        $enhanced_description = isset($resultItemFields['field_features_copy']) ? $resultItemFields['field_features_copy']->getvalues() : [];
        $sku_overview = isset($enhanced_description[0]) ? $enhanced_description[0]->getText() : $sku_overview;

        // Assets.
        $assets = [
          [
            "type" => "Primary Image",
            "id" => $image_name,
            "source_to_jpg" => $image_file,
          ],
        ];

        // Highlight.
        $highlight = [
          'name' => [
            $title,
          ]
        ];
      }

      if ($entity_type === 'media') {
        // Title.
        $title_ob = isset($resultItemFields['name']) ? $resultItemFields['name']->getvalues() : [];
        $title = isset($title_ob[0]) ? $title_ob[0]->getText() : '';

        // Image.
        $listing_image = isset($resultItemFields['media_listing_image_name']) ? $resultItemFields['media_listing_image_name']->getvalues() : [];
        $image_name = isset($listing_image[0]) ? explode('.', $listing_image[0])[0] : '';
        $image_file = isset($resultItemFields['media_listing_image_url']) ? $resultItemFields['media_listing_image_url']->getvalues()[0] : [];

        // Type.
        $type = isset($resultItemFields['bundle']) ? $resultItemFields['bundle']->getvalues()[0] : '';

        // Asset.
        $asset_file = isset($resultItemFields['media_filename_url']) ? $resultItemFields['media_filename_url']->getvalues()[0] : [];

        // Assets array.
        $assets = [
          "type" => "Primary Image",
          "id" => $image_name,
          "pro_tools_jpg_of_pdf" => $image_file,
          "original_source_file" => $asset_file,
        ];
      }

      $type = $this->itemTypes[$type] ?? $type;
      $item_type = $resultItemFields['item_type']->getvalues();

      // Slug for nodes other than products.
      if ($type === 'nodes' && !$slug) {
        $slug = $this->pathAliasManager->getAliasByPath('/node/'.$nid);
      }

      $items[] = [
        "_type" => $type,
        "_source" => [
          "slug" => $slug,
          "name" => $title,
          "type" => reset($item_type),
          "values" => [
            "sku_overview" => ($type === 'nodes') ? $title : $sku_overview,
          ],
          "assets" => $assets,
        ],
        "highlight" => $highlight,
      ];
    }

    $data['hits']['hits'] = $items;
    $data['hits']['total'] = $results->getResultCount();

    // Handle facets.
    $extra_data = $results->getAllExtraData();
    $data['aggregations'] = $this->handleFacets($extra_data);

    return $data;
  }

  public function formatDownloadCatalog(ResultSetInterface $results) {
    $data = [];
    if ($results->getResultCount() === 0) {
      $data['hits']['hits'] = [];
      $data['hits']['total'] = 0;
      return $data;
    }

    $items = [];

    foreach ($results as $result) {
      $translatedCate = [];
      $resultItemFields = $result->getFields();

      $media_file = isset($resultItemFields['field_media_file']) ? $resultItemFields['field_media_file']->getvalues() : [];
      $media_url = isset($resultItemFields['media_listing_image_url']) ? $resultItemFields['media_listing_image_url']->getvalues()[0] : '';

      $catName = isset($resultItemFields['media_product_category']) ? $resultItemFields['media_product_category']->getvalues() : [];
      foreach ($catName as $categoryName) {
        $translatedCate[] = StepHelper::translate($categoryName);
      }
      $category = implode(', ', $translatedCate);

      if (!empty($media_file)) {
        $download_file = $this->entityManager->getStorage('file')->load($media_file[0]);
        $download_file_url  = $download_file->getFileUri();
        $download_file_name = $download_file->getFilename();
        $download_file_path = file_create_url($download_file_url);
      }

      $listing_img_name = isset($resultItemFields['media_listing_image_name']) ? $resultItemFields['media_listing_image_name']->getvalues() : [];
      $image_name = explode(".", $listing_img_name[0] ?? '');
      if(!empty($download_file)){
        $asset_size = $download_file->getSize();
        $asset_mime_type = $download_file->getMimeType();
      }

      $items[] = [
        "_type" => "downloads",
        "_source" => [
          "id" => $download_file_name,
          "name" => $image_name[0],
          "type" => "Engineering Drawings",
          "product_category" => [$category],
          "values" => [
            "asset_extension" => 'pdf',
            "asset_size" => $asset_size,
            "asset_format" => "PDF (Portable Document Format application)",
            "asset_mime_type" => $asset_mime_type,
          ],
          "assets" => [
            "original_source_file" => $download_file_path,
            "source_to_jpg" => $media_url,
            "pro_tools_jpg_of_pdf" => $media_url,
            "pro_tools_pdf" => $download_file_path,
          ],
        ],
      ];
    }
    $data['hits']['hits'] = $items;
    $data['hits']['total'] = $results->getResultCount();

    // Handle facets.
    $extra_data = $results->getAllExtraData();
    $data['aggregations'] = $this->handleDownloadFacets($extra_data);

    return $data;
  }

  /**
   * Format facet data for vuejs.
   *
   * @param array $extra_data
   *   The extraData array from search_api results.
   *
   * @return array
   *   The formatted facets data.
   */
  protected function handleDownloadFacets(array $extra_data) {
    $data = [];
    if (!isset($extra_data['search_api_solr_response']['facet_counts']['facet_fields'])) {
      return $data;
    }

    $facet_fields = $extra_data['search_api_solr_response']['facet_counts']['facet_fields'];
    foreach ($facet_fields as $facet_name => $facet_field) {
      $buckets = [];
      for ($i = 0; $i < count($facet_field); $i += 2) {
        if ($facet_field[$i + 1] === 0) {
          continue;
        }

        $buckets[] = [
          'key' => StepHelper::translate($facet_field[$i]),
          'doc_count' => $facet_field[$i + 1],
        ];
      }
      if (empty($buckets)) {
        continue;
      }
      if ($facet_name == 'sm_item_type') {
        $data['document_type'] = [
          "doc_count_error_upper_bound" => 0,
          "sum_other_doc_count" => 0,
          'buckets' => $buckets,
        ];
      }
      if ($facet_name == 'sm_media_product_category') {
        $data['product_category'] = [
          "doc_count_error_upper_bound" => 0,
          "sum_other_doc_count" => 0,
          'buckets' => $buckets,
        ];
      }
      if ($facet_name == 'sm_medialang_type') {
        $data['language'] = [
          "doc_count_error_upper_bound" => 0,
          "sum_other_doc_count" => 0,
          'buckets' => $buckets,
        ];
      }
    }

    return $data;
  }

  /**
   * Format facet data for vuejs.
   *
   * @param array $extra_data
   *   The extraData array from search_api results.
   *
   * @return array
   *   The formatted facets data.
   */
  protected function handleFacets(array $extra_data) {
    $data = [];
    if (!isset($extra_data['search_api_solr_response']['facet_counts']['facet_fields'])) {
      return $data;
    }

    $facet_fields = $extra_data['search_api_solr_response']['facet_counts']['facet_fields'];
    foreach ($facet_fields as $facet_name => $facet_field) {
      $buckets = [];

      // We expect the facet name to be same as field name without prefix.
      $facet_name = trim($facet_name, 'sm_');
      $facet_name = trim($facet_name, 'ss_');
      $facet_name = $this->facetReplacements[$facet_name] ?? $facet_name;

      for ($i = 0; $i < count($facet_field); $i += 2) {
        if ($facet_field[$i + 1] === 0) {
          continue;
        }
        $buckets[] = [
          'key' => StepHelper::translate($facet_field[$i]),
          'doc_count' => $facet_field[$i + 1],
        ];
      }
      if (empty($buckets)) {
        continue;
      }

      // If facet data exists, combine count.
      if (isset($data[$facet_name])) {
        $buckets = $this->mergeFacetArrays($data[$facet_name]['buckets'], $buckets);
      }

      $data[$facet_name] = [
        "doc_count_error_upper_bound" => 0,
        "sum_other_doc_count" => 0,
        'buckets' => $buckets,
      ];
    }
    return $data;
  }

  /**
   * Merge 2 facet arrays and their counts.
   *
   * @param array $facetA
   *   The first facet.
   * @param array $facetB
   *   The second facet.
   *
   * @return mixed
   */
  protected function mergeFacetArrays(array $facetA, array $facetB) {
    foreach ($facetA as &$item) {
      $found = array_search($item['key'], array_column($facetB, 'key'));
      if ($found === FALSE) {
        continue;
      }
      $item['doc_count'] += $facetB[$found]['doc_count'];
    }

    return $facetA;
  }

}
