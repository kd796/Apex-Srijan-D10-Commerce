<?php

namespace Drupal\campbell_product_category_filtering\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Campbell Product Category Filtering form.
 */
class ProductCategoryFiltersForm extends FormBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a ContentEntityStorageBase object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \DDrupal\Core\Database\Connection $database
   *   The connection for database.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(RouteMatchInterface $route_match,
  EntityTypeManagerInterface $entity_type_manager,
  Connection $database,
  EntityFieldManagerInterface $entity_field_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'campbell_product_category_filters';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = $this->routeMatch->getParameter('node');
    $available_classification_ids = [];
    $child_terms = [];
    // Get Product Classification ID's.
    if ($node != NULL && $node->hasField('field_product_classifications') && !empty($node->get('field_product_classifications')->getValue())) {
      $classifications = array_column($node->get('field_product_classifications')->getValue(), 'target_id');
      if (empty($classifications)) {
        return $form;
      }

      $product_query = $this->entityTypeManager->getStorage('node')->getQuery();
      $result = $product_query->condition('type', 'product')
        ->condition('field_product_classifications', $classifications[0])
        ->execute();
      $product_nids = array_values($result);

      if (empty($product_nids)) {
        return $form;
      }

      $table_mapping = $this->entityTypeManager->getStorage('node')->getTableMapping();
      $field_product_specifications_table = $table_mapping->getFieldTableName('field_product_specifications');
      $field_product_specifications_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('node')['field_product_specifications'];
      $field_product_specifications_column = $table_mapping->getFieldColumnName($field_product_specifications_storage_definitions, 'target_id');

      $available_attribute_ids = $this->database->select($field_product_specifications_table, 'f')
        ->fields('f', [$field_product_specifications_column])
        ->distinct(TRUE)
        ->condition('bundle', 'product')
        ->condition('entity_id', $product_nids, 'IN')
        ->execute()->fetchCol();

      $field_product_classifications_table = $table_mapping->getFieldTableName('field_product_classifications');
      $field_product_classifications_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('node')['field_product_classifications'];
      $field_product_classifications_column = $table_mapping->getFieldColumnName($field_product_classifications_storage_definitions, 'target_id');

      $available_classification_ids = $this->database->select($field_product_classifications_table, 'f')
        ->fields('f', [$field_product_classifications_column])
        ->distinct(TRUE)
        ->condition('bundle', 'product')
        ->condition('entity_id', $product_nids, 'IN')
        ->execute()->fetchCol();

      $available_attribute_ids = array_unique($available_attribute_ids);
      $available_classification_ids = array_unique($available_classification_ids);

      // Set up Filters
      // Classification/Category Filtering.
      $active_classification_id = $node->field_classification_id->value;

      // Get all children of active classification.
      // I.E. Top level has multiple, Bottom level has none.
      $classification_query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $classification_result = $classification_query->condition('vid', 'product_classifications')
        ->condition('field_classification_id', $active_classification_id);
      $classification_tids = $classification_result->execute();
      $classification_terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($classification_tids);

      foreach ($classification_terms as $classification_term) {
        $child_terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('product_classifications', $classification_term->id(), $max_depth = 1, $load_entities = FALSE);
      }
    }

    if ($child_terms) {
      $selected_child_terms = [];

      foreach ($child_terms as $child_term) {
        $selected_child_terms[] = $child_term->tid;
      }

      $available_classifications = array_intersect($selected_child_terms, $available_classification_ids);

      foreach ($available_classifications as $available_classification) {
        $available_classification_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($available_classification);
        $category_facet_options[$available_classification_term->id()] = $available_classification_term->label();
      }

      $form['category-filter'] = [
        '#type' => 'checkboxes',
        '#options' => $category_facet_options,
        '#title' => $this->t('Category'),
        '#weight' => '0',
        '#required' => FALSE,
        '#attributes' => [
          'class' => [
            'node--type-product-category__category-filter',
          ],
        ],
      ];
    }

    // Prep Attribute Facets.
    $all_selected_attributes_tids = [];

    if ($node->hasField('field_category_facets')) {
      $selected_attributes = array_column($node->get('field_category_facets')
        ->getValue(), 'target_id');
    }

    $selected_attribute_facet_titles = [];
    $selected_attribute_facet_options = [];

    if (!empty($selected_attributes)) {
      foreach ($selected_attributes as $delta => $selected_attribute_id) {
        // Add parent terms to all available terms.
        $all_selected_attributes_tids[$delta][] = $selected_attribute_id;

        // Load parent term.
        $selected_attribute_parent = $this->entityTypeManager->getStorage('taxonomy_term')->load($selected_attribute_id);

        // Get all children of parent term.
        $selected_attribute_children = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('product_specifications', $selected_attribute_id, $max_depth = 1, $load_entities = FALSE);

        // Add children terms to all available terms.
        foreach ($selected_attribute_children as $selected_attribute_child) {
          $all_selected_attributes_tids[$delta][] = $selected_attribute_child->tid;
        };

        foreach ($all_selected_attributes_tids as $key => $all_selected_attributes_tid_array) {
          // Only filter by available attributes.
          $available_attributes = array_intersect($all_selected_attributes_tid_array, $available_attribute_ids);

          foreach ($available_attributes as $available_attribute) {
            /** @var Drupal\taxonomy\Entity\Term $available_attribute_term */
            $available_attribute_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($available_attribute);
            $selected_attribute_term_label = substr($available_attribute_term->label(), strpos($available_attribute_term->label(), ':') + 2);
            $select_attribute_value = $available_attribute_term->label() . ' (' . $available_attribute_term->id() . ')';
            $selected_attribute_facet_options[$key][$select_attribute_value] = $selected_attribute_term_label;
            $selected_attribute_parent = $this->entityTypeManager->getStorage('taxonomy_term')->loadParents($available_attribute_term->id());
            $selected_attribute_parent = reset($selected_attribute_parent);
            $selected_attribute_title = substr($selected_attribute_parent->label(), strpos($selected_attribute_parent->label(), '|') + 2);
            $selected_attribute_facet_titles[$key] = $selected_attribute_title;
          }
        }
      }

      if (!empty($selected_attribute_facet_options)) {
        foreach ($selected_attribute_facet_options as $delta => $selected_attribute_facet_option_array) {
          $form['attribute_filter'][$selected_attribute_facet_titles[$delta]] = [
            '#type' => 'checkboxes',
            '#options' => $selected_attribute_facet_option_array,
            '#title' => $this->t($selected_attribute_facet_titles[$delta]),
            '#weight' => '0',
            '#required' => FALSE,
            '#attributes' => [
              'class' => [
                'node--type-product-category__attribute-filter',
              ],
            ],
          ];
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

}
