<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

use Drupal\search_api\Plugin\PluginFormTrait;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->setEntityTypeManager($container->get('entity_type.manager'));
    return $processor;
  }

  /**
   * Retrieves the entity type manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager service.
   */
  public function getEntityTypeManager() {
    return $this->entityTypeManager ?: \Drupal::entityTypeManager();
  }

  /**
   * Sets the entity type manager service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   *
   * @return $this
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {

    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $object = $item->getOriginalObject()->getValue();
      $bundle = $object->bundle();
      $node_storage = $this->entityTypeManager->getStorage('node');
      // Remove Inactive models from indexed items.
      if ($bundle == 'product' && $object->hasField('field_product_models')) {
        $models = $object->get('field_product_models')->getValue();
        $value = [];
        if (!empty($models)) {
          foreach ($models as $model) {
            $node = $node_storage->load($model['target_id']);
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
