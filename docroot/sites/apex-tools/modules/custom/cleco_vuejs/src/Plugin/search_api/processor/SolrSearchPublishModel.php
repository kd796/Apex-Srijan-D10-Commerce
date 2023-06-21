<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

use Drupal\search_api\Plugin\PluginFormTrait;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Excludes entities marked as 'excluded' from being indexes.
 *
 * @SearchApiProcessor(
 *   id = "search_api_exclude_unpublish_model_from_index",
 *   label = @Translation("Search API Exclude Unpublish Models From Index."),
 *   description = @Translation("Excludes inactive models from being indexed."),
 *   stages = {
 *     "alter_items" = -50
 *   }
 * )
 */
class SolrSearchPublishModel extends ProcessorPluginBase {

  use PluginFormTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    return $processor;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {

    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $object = $item->getOriginalObject()->getValue();
      $bundle = $object->bundle();

      // Remove Inactive models from indexed items.
      if ($bundle == 'product' && $object->hasField('field_product_models')) {
        $models = $object->get('field_product_models')->getValue();
        $value = [];
        if (!empty($models)) {
          foreach ($models as $model) {
            $node = \Drupal::entityTypeManager()->getStorage('node')->load($model['target_id']);
            if ($node->isPublished()) {
              $value[]['target_id'] = $node->id();
            }
          }
        }
        $object->set('field_product_models', $value);
      }
    }
  }

}
