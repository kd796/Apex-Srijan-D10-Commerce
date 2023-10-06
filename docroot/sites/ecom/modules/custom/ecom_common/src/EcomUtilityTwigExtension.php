<?php

namespace Drupal\ecom_common;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twigextention for product details.
 */
class EcomUtilityTwigExtension extends AbstractExtension {

  /**
   * Creates twig function for getting product name and image.
   */
  public function getFunctions() {
    return [
      new TwigFunction('get_product_name', [$this, 'getProductName']),
    ];
  }

  /**
   * Returnd product name.
   */
  public function getProductName($sku) {

    $product_node = \Drupal::entityTypeManager()->getStorage('node')
      ->loadByProperties([
        'type' => 'product',
        'title' => $sku,
      ]);
    $product_node = array_values($product_node);
    $product_name = $product_node[0]->get('field_long_description')->value;
    $product_listing_image_uri = $this->getImageUri('field_media', $product_node[0], 'field_media_image');
    $image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load('tiny');
    $url = $image_style->buildUrl($product_listing_image_uri);
    $product_name_image_arr = [
      'name' => $product_name,
      'image_url' => $url,
    ];
    return $product_name_image_arr;

  }

  /**
   * Get image uri.
   */
  public function getImageUri($field_name, $entity, $media_field) {
    $image_uri = $entity->$field_name->entity->$media_field->entity->getFileUri();
    $url = !empty($image_uri) ? \Drupal::service('file_url_generator')->generateAbsoluteString($image_uri) : NULL;
    return $url;
  }

}
