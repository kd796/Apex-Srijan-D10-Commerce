<?php

namespace Drupal\commerce_custom_stock;

/**
 * Class BatchService for Batch Process.
 */
class BatchService {

  /**
   * Batch process callback.
   *
   * @param array $product_chunk
   *   Sku of product.
   * @param object $context
   *   Context for operations.
   */
  public static function updateProduct($product_chunk, &$context = []) {
    foreach ($product_chunk as $sku => $qty) {
      $product_node_arr = \Drupal::entityTypeManager()->getStorage('node')
        ->loadByProperties([
          'type' => 'product',
          'title' => $sku,
          'status' => '1',
        ]);
      if (!empty($product_node_arr)) {
        $product_node_arr = array_values($product_node_arr);
        $node = $product_node_arr[0];
        if ($node->field_commerce_product != NULL) {
          $prod_variation_obj = $node->field_commerce_product->entity->variations->entity;
        }
        if ($prod_variation_obj) {
          $prod_variation_obj->field_stock->value = $qty;
          $prod_variation_obj->save();
        }
      }
      else {
        \Drupal::logger('commerce_custom_stock')->notice("Product '$sku' is unpublished or not present");
      }
      $context['results'][] = $sku;
    }
  }

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public static function updateFinished($success, $results, $operations) {
    if ($success) {
      $count = count($results);
      \Drupal::logger('commerce_custom_stock')->notice("Processed  '{$count}' products successfully.");
    }
    else {
      \Drupal::logger('commerce_custom_stock')->error('Product update failed');
    }
  }

}
