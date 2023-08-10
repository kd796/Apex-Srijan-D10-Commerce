<?php

namespace Drupal\ecom_migrations\Plugin\migrate\process;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_price\Price;

/**
 * Create Commerce products.
 *
 * @MigrateProcessPlugin(
 *   id = "ecom_create_commerce_product_entity"
 * )
 */
class CreateCommerceProduct extends ProcessPluginBase {
  
  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $pValues = $value->xpath('parent::Product/Values/Value');
    $entity_manager = \Drupal::entityTypeManager();
    $sku_value = $row->getSourceProperty('remote_sku');

    foreach ($pValues as $item) {
      $pValue_list[(string) $item->attributes()->AttributeID] = (string) $item[0];
    }

    $commProduct = $entity_manager->getStorage('commerce_product')
    ->loadByProperties([
      'title' => $sku_value,
    ]);

    if (!empty($commProduct)) {
      $presentProduct = array_values($commProduct);
      $product_variation = $entity_manager->getStorage('commerce_product_variation')->load($presentProduct[0]->variations[0]->target_id);
      $product_variation->qty_increments = $pValue_list['CasePackQty'];
      $product_variation->price = new Price($pValue_list['CustomerPrice'], 'USD');
      $product_variation->save();

      $all_terms_array[] = [
        'target_id' => $presentProduct[0]->product_id->value,
      ];
      $all_terms_array = json_encode($all_terms_array);
    }
    else {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => $sku_value,
        'price' => new Price($pValue_list['CustomerPrice'], 'USD'),
        'qty_increments' => $pValue_list['CasePackQty'],
      ]);
      $variation->save();

      $product = Product::create([
        'type' => 'default',
        'title' => t($sku_value),
        'variations' => [$variation],
      ]);
      $product->save();

      $all_terms_array[] = [
        'target_id' => $product->id(),
      ];
      $all_terms_array = json_encode($all_terms_array);
    }

    return json_decode($all_terms_array, TRUE);
  }
}
