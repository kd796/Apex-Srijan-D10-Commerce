<?php

namespace Drupal\step\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "products_default",
 *   label = @Translation("Products Default"),
 *   field_types = {
 *     "products"
 *   }
 * )
 */
class ProductsDefaultFormatter extends FormatterBase
{
    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = [];

        foreach ($items as $delta => $item) {
            $elements[$delta] = [
                '#id' => $item->id,
                '#name' => $item->name,
                '#alt_name' => $item->alt_name,
                '#alt_url' => $item->alt_url,
            ];
        }

        return $elements;
    }
}
