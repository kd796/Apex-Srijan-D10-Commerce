<?php

namespace Drupal\step\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Plugin implementation of the 'products_default' widget.
 *
 * @FieldWidget(
 *   id = "products_default",
 *   label = @Translation("Products Default"),
 *   field_types = {
 *     "products"
 *   }
 * )
 */
class ProductsDefaultWidget extends WidgetBase
{
    /**
     * {@inheritdoc}
     */
    public static function defaultSettings()
    {
        return [
            'size' => 255
        ] + parent::defaultSettings();
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @param $form
     * @param $form_state
     * @return mixed
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state)
    {
        $form_state->setRebuild();

        $element = [];

        $element['name'] = [
            '#id'                            => 'es-relationship-product-name-' . $delta,
            '#type'                          => 'textfield',
            '#description'                   => t('Search by product’s name, ID, or slug.'),
            '#default_value'                 => isset($items[$delta]->name) ? $items[$delta]->name : '',
            '#autocomplete_route_name'       => 'actions.step.api.autocomplete.field.products',
            '#autocomplete_route_parameters' => ['count' => 20],
            '#ajax'                          => [
                'callback' => [$this, 'manipulateValues'],
                'event'    => 'autocompleteclose change',
                'progress' => 'throbber',
                'wrapper'  => 'ajax-wrapper-' . $delta
            ]
        ];

        $element['id'] = [
            '#id'            => 'es-relationship-product-id-' . $delta,
            '#type'          => 'textfield',
            '#description'   => t('Read-only field for Product ID.'),
            '#attributes'    => ['readonly' => 'readonly', 'style' => 'background-color: #fbfbfb !important; color: #adadad;'],
            '#default_value' => isset($items[$delta]->id) ? $items[$delta]->id : ''
        ];

        $element['alt_name'] = [
            '#id'            => 'es-relationship-product-alt-name-' . $delta,
            '#type'          => 'textfield',
            '#description'   => t('Add alternate name in favor of product name. Ideal for displaying a product, but using its category name.'),
            '#default_value' => isset($items[$delta]->alt_name) ? $items[$delta]->alt_name : ''
        ];

        $element['alt_url'] = [
            '#id'            => 'es-relationship-product-alt-url-' . $delta,
            '#type'          => 'textfield',
            '#description'   => t('Add alternate url if this should not link to the product. e.g. Link to products page with filter query string.'),
            '#default_value' => isset($items[$delta]->alt_url) ? $items[$delta]->alt_url : '',
            '#maxlength'     => 512
        ];

        return $element;
    }

    /**
     * @param $form
     * @param FormStateInterface $form_state
     * @return mixed
     */
    public function manipulateValues(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();

        // Get the field that triggered the callback
        $triggerdEl = $form_state->getTriggeringElement();
        $parents    = $triggerdEl['#parents'];
        $nameField  = $triggerdEl['#id'];
        $idField    = 'es-relationship-product-id-' . $parents[count($triggerdEl['#parents']) - 2];

        // Break String on [ID=] pattern
        // Name = anything before
        // Id = anything inside =
        $value = $triggerdEl['#value'];
        preg_match('#\[ID=(.*?)\]#', $value, $match);
        $idValue   = isset($match[1]) ? $match[1] : '';
        $nameValue = isset($match[1]) ? trim(explode("[ID=$match[1]]", $value)[0]) : '';

        // Set new values
        $response->addCommand(new InvokeCommand('#' . $idField, 'val', [$idValue]));
        $response->addCommand(new InvokeCommand('#' . $nameField, 'val', [$nameValue]));

        return $response;
    }
}
