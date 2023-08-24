<?php

namespace Drupal\ecom_migrations\Plugin\migrate\process;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

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
    $activeStatusArr = ['Z2', 'Z3', 'Z5'];

    $pValue_list = [];
    $variation = [];

    foreach ($pValues as $item) {
      $pValue_list[(string) $item->attributes()->AttributeID] = (string) $item[0];
      if ((string) $item->attributes()->AttributeID === 'SAP_SALES_ORG_STATUS') {
        $sapSalesOrgStatus = (string) $item->attributes()->ID;
      }
    }

    $commProduct = $entity_manager->getStorage('commerce_product')
      ->loadByProperties([
        'title' => $sku_value,
      ]);

    if (!empty($commProduct)) {
      $presentProduct = array_values($commProduct);

      if (!empty($pValue_list['CustomerPrice'])) {
        $product_variation = $entity_manager->getStorage('commerce_product_variation')->load($presentProduct[0]->variations[0]->target_id);
        $product_variation->qty_increments = $pValue_list['CasePackQty'] ? $pValue_list['CasePackQty'] : 1;
        $product_variation->price = new Price($pValue_list['CustomerPrice'], 'USD');
        if ((in_array($sapSalesOrgStatus, $activeStatusArr))) {
          $product_variation->status = 1;
          $commProduct[$presentProduct[0]->product_id->value]->set('status', 1);
        }
        else {
          $product_variation->status = 0;
          $commProduct[$presentProduct[0]->product_id->value]->set('status', 0);
        }
        $product_variation->save();
        $commProduct[$presentProduct[0]->product_id->value]->save();
        $all_terms_array[] = [
          'target_id' => $presentProduct[0]->product_id->value,
        ];
        $all_terms_array = json_encode($all_terms_array);
      }
      else {
        $product_variation = $entity_manager->getStorage('commerce_product_variation')->load($presentProduct[0]->variations[0]->target_id);
        $product_variation->qty_increments = $pValue_list['CasePackQty'] ? $pValue_list['CasePackQty'] : 1;
        $product_variation->price = new Price(0, 'USD');
        $product_variation->status = 0;
        $product_variation->save();

        $commProduct[$presentProduct[0]->product_id->value]->set('status', 0);
        $commProduct[$presentProduct[0]->product_id->value]->save();

        $all_terms_array[] = [
          'target_id' => $presentProduct[0]->product_id->value,
        ];
        $all_terms_array = json_encode($all_terms_array);
      }
    }
    else {
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => $sku_value,
        'price' => new Price($pValue_list['CustomerPrice'] ? $pValue_list['CustomerPrice'] : 0, 'USD'),
        'qty_increments' => $pValue_list['CasePackQty'] ? $pValue_list['CasePackQty'] : 1,
      ]);
      if ((in_array($sapSalesOrgStatus, $activeStatusArr))) {
        $variation->status = 1;
      }
      else {
        $variation->status = 0;
      }
      $variation->save();

      if (empty($pValue_list['CustomerPrice'])) {
        $product = Product::create([
          'type' => 'default',
          'title' => t($sku_value),
          'variations' => [$variation],
          'status' => 0,
          'store' => 1,
        ]);
      }
      else {
        $product = Product::create([
          'type' => 'default',
          'title' => t($sku_value),
          'variations' => [$variation],
          'store' => 1,
        ]);
        if ((in_array($sapSalesOrgStatus, $activeStatusArr))) {
          $product->set('status', 1);
        }
        else {
          $product->set('status', 0);
        }
      }
      $product->setStoreIds([1]);
      $product->save();
      $all_terms_array[] = [
        'target_id' => $product->id(),
      ];
      $all_terms_array = json_encode($all_terms_array);
    }
    return json_decode($all_terms_array, TRUE);
  }

}
