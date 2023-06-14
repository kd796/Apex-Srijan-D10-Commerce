<?php

namespace Drupal\step\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;

/**
 * Define 'products' field.
 *
 * @FieldType(
 *   id = "products",
 *   label = @Translation("Products"),
 *   description = @Translation("A field to load products form ElasticSearch/"),
 *   default_formatter = "products_default",
 *   default_widget = "products_default"
 * )
 */
class ProductsItem extends FieldItemBase
{
    /**
     * {@inheritdoc}
     */
    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return [
            'columns' => [
                'name'     => [
                    'type'   => 'varchar',
                    'length' => 255
                ],
                'alt_name' => [
                    'type'   => 'varchar',
                    'length' => 255
                ],
                'alt_url'  => [
                    'type'   => 'varchar',
                    'length' => 512
                ],
                'id'       => [
                    'type'   => 'varchar',
                    'length' => 255
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
        $properties         = [];
        $properties['name'] = DataDefinition::create('string')
            ->setLabel(t('Product Title'))
            ->setRequired(true);

        $properties['id'] = DataDefinition::create('string')
            ->setLabel(t('Product ID'))
            ->setRequired(true);

        $properties['alt_name'] = DataDefinition::create('string')
            ->setLabel(t('Product Alternate Name'));

        $properties['alt_url'] = DataDefinition::create('string')
            ->setLabel(t('Alternate Name'));

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->get('name')->getValue()) && empty($this->get('id')->getValue());
    }
}
