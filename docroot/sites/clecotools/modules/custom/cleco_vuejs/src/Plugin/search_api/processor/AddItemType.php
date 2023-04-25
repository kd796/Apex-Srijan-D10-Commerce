<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\cleco_vuejs\Utils\StepHelper;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Adds the item's type to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "cleco_add_item_type",
 *   label = @Translation("Item Type"),
 *   description = @Translation("Adds the item type."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddItemType extends ProcessorPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $processor->setTypeManager($container->get('entity_type.manager'));

    return $processor;
  }

  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function getTypeManager() {
    return $this->entityTypeManager ?: \Drupal::service('entity_type.manager');
  }

  /**
   * Sets the entity type manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $type_manager
   *   The new entity type manager.
   *
   * @return $this
   */
  public function setTypeManager(EntityTypeManagerInterface $type_manager) {
    $this->entityTypeManager = $type_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Item Type'),
        'description' => $this->t("Adds the item type as a field."),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
        'is_list' => TRUE,
      ];
      $properties['item_type'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();
    $value = [];
    // If this is a node.
    if ($entity instanceof Node) {
      $tools = ['product', 'enhanced_product'];
      $articles = ['article', 'news_insights'];
      $solutions = ['solutions'];
      if (in_array($entity->bundle(), $tools)) {
        $value[] = $this->t('Tools');
      }
      if (in_array($entity->bundle(), $articles)) {
        $value[] = $this->t('Article');
      }
      if (in_array($entity->bundle(), $solutions)) {
        $value[] = $this->t('Solution');
      }
    }

    if ($entity instanceof Media) {
      $type = $entity->get('field_type')->getValue();
      $allowed_values = $entity->get('field_type')->getFieldDefinition()->getSetting('allowed_values');
      foreach ($type as $each) {
        if (!isset($each['value']) || !isset($allowed_values[$each['value']])) {
          continue;
        }
        $value[] = $allowed_values[$each['value']];
      }

    }

    // Set value.
    $fields = $item->getFields();
    $fields = $this->getFieldsHelper()
      ->filterForPropertyPath($fields, NULL, 'item_type');
    foreach ($fields as $field) {
      foreach ($value as $val) {
        $field->addValue($val);
      }
    }
  }

}