<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

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
 * Adds the item's URL to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "cleco_add_model_specification",
 *   label = @Translation("Product Model Specification"),
 *   description = @Translation("Adds the product's model specification to indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddModelSpecification extends ProcessorPluginBase {

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
      $terms = StepHelper::getProductFilters();
      foreach ($terms as $term) {
        $field_key = $term['key'];
        $definition = [
          'label' => $term['name'],
          'description' => $this->t("Adds the product's model specification: @spec", ['@spec' => $term['name']]),
          'type' => 'string',
          'processor_id' => $this->getPluginId(),
          'is_list' => TRUE,
        ];
        $properties[$field_key] = new ProcessorProperty($definition);
      }
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();
    // Only add fields for 'product' content.
    if (!$entity instanceof Node || $entity->bundle() !== 'product') {
      return;
    }

    $query = $this->getTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', "product_filters");
    $tids = $query->accessCheck(FALSE)->execute();
    $terms = Term::loadMultiple($tids);
    $filters = [];
    $product_filters = StepHelper::productFilters();
    foreach ($terms as $term) {
      $field_id = $term->get('field_es_id')->getValue()[0]['value'];
      $field_key = $term->get('field_es_key')->getValue()[0]['value'];
      if (str_starts_with($field_id, 'N/A')) {
        // Get bucket data from productFilters().
        $field_id = [];
        foreach ($product_filters as $id => $filter) {
          if (!isset($filter['bucket']) || $filter['bucket'] !== $term->getName()) {
            continue;
          }
          $field_id[] = $id;
        }
      }
      if (is_string($field_id)) {
        $field_id = [$field_id];
      }
      $filters[$field_key] = $field_id;
    }

    // Load all modes for this product.
    $models = $entity->get('field_product_models')->getValue();
    $buckets = [];
    foreach ($models as $model_id) {
      // Load product model.
      $model = $this->getTypeManager()->getStorage('node')->load($model_id['target_id']);
      $specifications = $model->get('field_model_specification')->getValue();
      foreach ($specifications as $spec) {
        // Load specifications for this model.
        $spec_term = $this->getTypeManager()->getStorage('taxonomy_term')->load($spec['target_id']);
        if (!$spec_term) {
          continue;
        }
        // Get parent of this specification to get attribute ID.
        $parents = $this->getTypeManager()->getStorage("taxonomy_term")->loadAllParents($spec_term->id());
        $parent_term = end($parents);
        $parent_name = $parent_term->getName();
        $parent_att_id = trim(explode('|~|', $parent_name)[0]);
        $buc = $this->searchById($parent_att_id, $filters);
        if (!$buc) {
          continue;
        }
        $spec_value = trim(explode(':~:', $spec_term->getName())[1]);
        $buckets[$buc][] = $spec_value;
      }
    }

    // Add all applicable field data to the indexed fields.
    $item_fields = $item->getFields();
    foreach ($buckets as $field_name => $value) {
      if (!isset($item_fields[$field_name])) {
        continue;
      }
      $fields = $item->getFields();
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($fields, NULL, $field_name);
      foreach ($fields as $field) {
        foreach ($value as $val) {
          $field->addValue($val);
        }
      }
    }
  }

  /**
   * Helper to search in an array of arrays.
   *
   * @param string $key
   *   The key to search for.
   * @param array $arr
   *   The array where the key will be searched.
   *
   * @return false|string
   *   Return array key if value is found and FALSE otherwise.
   */
  protected function searchById($key, array $arr) {
    foreach ($arr as $k => $item) {
      if (!in_array($key, $item)) {
        continue;
      }
      return $k;
    }
    return FALSE;
  }

}
