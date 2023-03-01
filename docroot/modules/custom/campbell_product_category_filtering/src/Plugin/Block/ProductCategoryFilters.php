<?php

namespace Drupal\campbell_product_category_filtering\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Provides a product listing filters block.
 *
 * @Block(
 *   id = "campbell_product_category_filters",
 *   admin_label = @Translation("Product Listing Filters"),
 *   category = @Translation("Campbell")
 * )
 */
class ProductCategoryFilters extends BlockBase {

    /**
   * Form builder will be used via Dependency Injection.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'product_category_filters_form' => $this->formBuilder->getForm('Drupal\campbell_product_category_filtering\Form\ProductCategoryFiltersForm'),
    ];
  }

}
