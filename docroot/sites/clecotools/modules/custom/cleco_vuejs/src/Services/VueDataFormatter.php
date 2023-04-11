<?php

namespace Drupal\cleco_vuejs\Services;

use Drupal\search_api\Query\ResultSetInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cleco_vuejs\Utils\StepHelper;

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
     * Constructs a solr search service.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
     *   The entity manager.
     *
     * @return null
     */
    public function __construct(EntityTypeManagerInterface $entity_manager)
    {
        $this->entityManager = $entity_manager;
    }

  /**
   * Format the search results as expected by vuejs.
   *
   * @param \Drupal\search_api\Query\ResultSetInterface $results
   *   The results from search_api query.
   *
   * @return array
   *   The formatted data as expected by vuejs.
   */
  public function formatProductCatalog(ResultSetInterface $results) {
    $data = [];

    if ($results->getResultCount() === 0) {
      $data['hits']['hits'] = [];
      $data['hits']['total'] = 0;
      return $data;
    }

    $items = [];
    foreach ($results as $result) {
      $resultItemFields = $result->getFields();
      // Title.
      $title_ob = isset($resultItemFields['title']) ? $resultItemFields['title']->getvalues() : [];
      $title = isset($title_ob[0]) ? $title_ob[0]->getText() : '';
      // Image.
      $listing_image = isset($resultItemFields['media_name']) ? $resultItemFields['media_name']->getvalues() : [];
      $image_name = isset($listing_image[0]) ? explode('.', $listing_image[0])[0] : '';
      ;
      // Description.
      $sku_overview = isset($resultItemFields['body']) ? $resultItemFields['body']->getvalues() : [];
      $sku_overview = isset($sku_overview[0]) ? $sku_overview[0]->getText() : '';
      // Slug.
      $slug = isset($resultItemFields['field_slug']) ? $resultItemFields['field_slug']->getvalues() : '';
      // Type.
      $type = $resultItemFields['type']->getvalues();

      $items[] = [
        "_type" => $type,
        "_source" => [
          "slug" => $slug,
          "name" => $title,
          "values" => [
            "sku_overview" => $sku_overview,
          ],
          "assets" => [
            [
              "type" => "Primary Image",
              "id" => $image_name,
            ],
          ],
        ],
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
      $resultItemFields = $result->getFields();
      $media_file = isset($resultItemFields['field_media_file']) ? $resultItemFields['field_media_file']->getvalues() : [];
      $listing_image = isset($resultItemFields['field_listing_image']) ? $resultItemFields['field_listing_image']->getvalues() : [];

      $catName = isset($resultItemFields['download_category_name']) ? $resultItemFields['download_category_name']->getvalues() : [];
      $catName = StepHelper::translate($catName);
      $category = implode(', ', $catName);
      if (!empty($listing_image)) {
        $image_load = $this->entityManager->getStorage('media')->load($listing_image[0]);
        $image_file = $this->entityManager->getStorage('file')->load($image_load->field_media_image->target_id);
        $image_url  = $image_file->getFileUri();
        $media_url = file_create_url($image_url);
      }

      if (!empty($media_file)) {
        $download_file = $this->entityManager->getStorage('file')->load($media_file[0]);
        $download_file_url  = $download_file->getFileUri();
        $download_file_name = $download_file->getFilename();
        $download_file_path = file_create_url($download_file_url);
      }

      $listing_img_name = isset($resultItemFields['listing_img_name']) ? $resultItemFields['listing_img_name']->getvalues() : [];
      $image_name = explode(".", $listing_img_name[0]);

      $items[] = [
        "_type" => "downloads",
        "_source" => [
          "id" => $download_file_name,
          "name" => $image_name[0],
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
      if ($facet_name == 'sm_download_category_name') {
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
      for ($i = 0; $i < count($facet_field); $i += 2) {
        if ($facet_field[$i + 1] === 0) {
          continue;
        }
        $buckets[] = [
          'key' => $facet_field[$i],
          'doc_count' => $facet_field[$i + 1],
        ];
      }
      if (empty($buckets)) {
        continue;
      }
      $data[trim($facet_name, 'sm_')] = [
        "doc_count_error_upper_bound" => 0,
        "sum_other_doc_count" => 0,
        'buckets' => $buckets,
      ];
    }
    return $data;
  }

}
