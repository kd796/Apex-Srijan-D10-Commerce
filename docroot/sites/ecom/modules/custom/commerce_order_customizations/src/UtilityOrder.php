<?php

namespace Drupal\commerce_order_customizations;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides utility functions for order export-import.
 */
class UtilityOrder {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The SubdivisionRepository class.
   *
   * @var \CommerceGuys\Addressing\Subdivision\SubdivisionRepository
   */
  protected SubdivisionRepository $subdivisionRepository;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->subdivisionRepository = new SubdivisionRepository();
  }

  /**
   * Return product price.
   */
  public function getItemPrice($oderItemObj) {

    return $oderItemObj->getPurchasedEntity()->getPrice()->getNumber();
  }

  /**
   * Return product sku.
   */
  public function getItemSku($oderItemObj) {

    return $oderItemObj->getPurchasedEntity()->getSku();
  }

  /**
   * Item quantity.
   */
  public function getitemQuantity($oderItemObj) {

    return $oderItemObj->getQuantity();
  }

  /**
   * Order raw total.
   */
  public function getrawTotal($oderItemObj) {

    return (float) $this->getItemPrice($oderItemObj) * (float) $this->getitemQuantity($oderItemObj);
  }

  /**
   * Get the name of the product.
   */
  public function getName($oderItemObj) {
    $product_node = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'product',
      'title' => $oderItemObj->getPurchasedEntity()->getSku(),
    ]);
    $product_node = array_values($product_node);
    return $product_node[0]->field_long_description->value;
  }

  /**
   * Get shipping amount.
   */
  public function getShippingAmmount($orderObj) {

    foreach ($orderObj->getAdjustments() as $adjustment) {
      if ($adjustment->getType() == 'shipping') {
        return $adjustment->getAmount()->getNumber();
      }
    }
  }

  /**
   * Get tax amount.
   */
  public function getTaxAmmount($orderObj) {

    foreach ($orderObj->getAdjustments() as $adjustment) {
      if ($adjustment->getType() == 'custom_adjustment') {
        return $adjustment->getAmount()->getNumber();
      }
    }
  }

  /**
   * Get order total.
   */
  public function getOrderTotal($orderObj) {
    return $orderObj->getTotalPrice()->getNumber();
  }

  /**
   * Get Billing name.
   */
  public function getBillingName($profile) {
    $address = $profile->get('address')->first();
    $billing_name = $address->get('given_name')->getCastedValue() . ' ' . $address->get('family_name')->getCastedValue();
    return $billing_name;
  }

  /**
   * Get City.
   */
  public function getCity($profile) {
    return $profile->get('address')->first()->get('locality')->getCastedValue();
  }

  /**
   * Get State code.
   */
  public function getStateCode($profile) {
    return $profile->get('address')->first()->get('administrative_area')->getCastedValue();
  }

  /**
   * Get State name.
   */
  public function getStateName($profile) {
    $subdivisionRepository = new SubdivisionRepository();
    $state_code = $profile->get('address')->first()->get('administrative_area')->getCastedValue();
    $states_us = $subdivisionRepository->getAll(['US']);
    return $states_us[$state_code]->getName();
  }

  /**
   * Get City.
   */
  public function getPostalCode($profile) {
    return $profile->get('address')->first()->get('postal_code')->getCastedValue();
  }

  /**
   * Get StreetOne.
   */
  public function getAddressLineOne($profile) {

    return $profile->get('address')->first()->get('address_line1')->getCastedValue();
  }

  /**
   * Get StreetTwo.
   */
  public function getAddressLineTwo($profile) {
    return $profile->get('address')->first()->get('address_line2')->getCastedValue();
  }

  /**
   * Get company.
   */
  public function getCompany($profile) {
    return $profile->get('address')->first()->get('organization')->getCastedValue();
  }

  /**
   * Get county.
   */
  public function getCounty($profile) {
    return $profile->get('field_county')->value;
  }

}
