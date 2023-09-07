<?php

namespace Drupal\ecom_addrexx\Element;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatHelper;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Addressing\AddressFormat\FieldOverrides;
use CommerceGuys\Addressing\Locale;
use Drupal\address\FieldHelper;
use Drupal\address\LabelHelper;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\address\Element\Address;

/**
 * Override an address form element.
 *
 * @FormElement("address")
 */
class AddressOverride extends Address {


  /**
   * @inheritDoc
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

        case "postal_code":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.autocomplete';
          $element[$property]['#autocomplete_route_parameters'] = [
            'filter' => 'zip',
            'contextKey' => 'US',
          ];
          break;

        case "address_line1":
          $element[$property]['#autocomplete_route_name'] = 'ecom_addrexx.autocomplete';
          $element[$property]['#autocomplete_route_parameters'] = [
            'filter' => 'street',
            'contextKey' => 'BOULDER80302',
          ];
          break;
      }
    }

    return $element;
  }

}
