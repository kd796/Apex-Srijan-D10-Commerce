<?php

namespace Drupal\cleco_vuejs\Services;

use Drupal\search_api\Query\ResultSetInterface;

/**
 * Service to format search results as expected by vuejs.
 */
class VueDataFormatter {

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
