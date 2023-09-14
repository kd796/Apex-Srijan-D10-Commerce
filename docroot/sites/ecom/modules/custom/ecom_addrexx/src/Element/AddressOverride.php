<?php

namespace Drupal\ecom_addrexx\Element;

use Drupal\address\Element\Address;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ecom_addrexx\CommonConstants;

/**
 * Override an address form element.
 *
 * @FormElement("address")
 */
class AddressOverride extends Address {

  /**
   * {@inheritDoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    $info['#process'][] = [
      get_class($this),
      'processAddress',
    ];

    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function processAddress(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element = Address::processAddress($element, $form_state, $complete_form);
    $complete_form['#attached']['library'][] = 'ecom_addrexx/autocomplete_ajax';

    foreach ($element as $property => $value) {
      switch ($property) {
        case "given_name":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.autocomplete';
          $element[$property]['#autocomplete_route_parameters'] = ['filter' => 'firstName'];
          break;

        case "family_name":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.autocomplete';
          $element[$property]['#autocomplete_route_parameters'] = ['filter' => 'lastName'];
          break;

        case "locality":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.state_city';
          $element[$property]['#autocomplete_route_parameters'] = [
            'state' => CommonConstants::ADDREXX_ALL,
            'zipcode' => '0',
          ];
          $element[$property]['#weight'] = 4;
          break;

        case "administrative_area":
          $element[$property]['#weight'] = 4;
          break;

        case "postal_code":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.autocomplete';
          $element[$property]['#weight'] = 3;
          $element[$property]['#autocomplete_route_parameters'] = [
            'filter' => 'zip',
            'contextKey' => 'US',
          ];
          break;

        case "address_line1":
        case "address_line2":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.autocomplete';
          $element[$property]['#autocomplete_route_parameters'] = [
            'filter' => 'street',
            'contextKey' => CommonConstants::ADDREXX_ALL,
          ];
          $element[$property]['#weight'] = 5;
          break;
      }
    }

    return $element;
  }

}
