<?php

namespace Drupal\gearwrench_product_category_filtering\Form;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a Gearwrench Product Category Filtering form.
 */
class ProductCategoryFiltersForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gearwrench_product_listing_filters';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $view = \Drupal::entityTypeManager()
      ->getStorage('view')
      ->load('product_listing')
      ->getExecutable();
    $view_args = [];
    // Get Product Classification ID's.
    if (!empty($node->get('field_product_classifications')->getValue())) {
      $classifications = array_column($node->get('field_product_classifications')->getValue(), 'target_id');
      $view_args[] = implode(',', $classifications);
    }
    $view_display = 'products_by_category';
    $view->initDisplay();
    $view->setDisplay($view_display);
    $view->setArguments($view_args);
    $view->setItemsPerPage(0);
    $view->preExecute();
    $view->execute();
    // Get all available product specifications.
    $view_nids = array_column($view->result, 'nid');
    $table_mapping = \Drupal::entityTypeManager()->getStorage('node')->getTableMapping();
    $field_product_specifications_table = $table_mapping->getFieldTableName('field_product_specifications');
    $field_product_specifications_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node')['field_product_specifications'];
    $field_product_specifications_column = $table_mapping->getFieldColumnName($field_product_specifications_storage_definitions, 'target_id');
    $connection = \Drupal::database();
    $available_attribute_ids = $connection->select($field_product_specifications_table, 'f')
      ->fields('f', [$field_product_specifications_column])
      ->distinct(TRUE)
      ->condition('bundle', 'product')
      ->condition('entity_id', $view_nids, 'IN')
      ->execute()->fetchCol();

    $field_product_classifications_table = $table_mapping->getFieldTableName('field_product_classifications');
    $field_product_classifications_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node')['field_product_classifications'];
    $field_product_classifications_column = $table_mapping->getFieldColumnName($field_product_classifications_storage_definitions, 'target_id');
    $available_classification_ids = $connection->select($field_product_classifications_table, 'f')
      ->fields('f', [$field_product_classifications_column])
      ->distinct(TRUE)
      ->condition('bundle', 'product')
      ->condition('entity_id', $view_nids, 'IN')
      ->execute()->fetchCol();
    $available_attribute_ids = array_unique($available_attribute_ids);
    $available_classification_ids = array_unique($available_classification_ids);

    // Set up Filters
    // Classification/Category Filtering.
    $active_classification_id = $node->field_classification_id->value;
    // Get all children of active classification. I.E. Top level has multiple, Bottom level has none.
    $classification_query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'product_classifications')
      ->condition('field_classification_id', $active_classification_id);
    $classification_tids = $classification_query->execute();
    $classification_terms = Term::loadMultiple($classification_tids);
    foreach ($classification_terms as $classification_term) {
      $child_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('product_classifications', $classification_term->id(), $max_depth = 1, $load_entities = FALSE);
    }
    if ($child_terms) {
      foreach ($child_terms as $child_term) {
        $selected_child_terms[] = $child_term->tid;
      }
      $available_classifications = array_intersect($selected_child_terms, $available_classification_ids);
      foreach ($available_classifications as $available_classification) {
        $available_classification_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($available_classification);
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
            'node--type-product-listing__category-filter',
          ]
        ],
      ];
    }

    // Prep Attribute Facets.
    $all_selected_attributes_tids = [];
    $selected_attributes = array_column($node->get('field_category_facets')
      ->getValue(), 'target_id');
    $selected_attribute_facet_titles = [];
    $selected_attribute_facet_options = [];
    foreach ($selected_attributes as $delta => $selected_attribute_id) {
      // Add parent terms to all available terms.
      $all_selected_attributes_tids[$delta][] = $selected_attribute_id;
      // Load parent term.
      $selected_attribute_parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($selected_attribute_id);
      // Get all children of parent term.
      $selected_attribute_children = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('product_specifications', $selected_attribute_id, $max_depth = 1, $load_entities = FALSE);
      // Add children terms to all available terms.
      foreach ($selected_attribute_children as $selected_attribute_child) {
        $all_selected_attributes_tids[$delta][] = $selected_attribute_child->tid;
      };
      foreach ($all_selected_attributes_tids as $key => $all_selected_attributes_tid_array) {
        // Only filter by available attributes.
        $available_attributes = array_intersect($all_selected_attributes_tid_array, $available_attribute_ids);
        foreach ($available_attributes as $available_attribute) {
          $available_attribute_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($available_attribute);
          $selected_attribute_term_label = substr($available_attribute_term->label(), strpos($available_attribute_term->label(), ':') + 2);
          $selected_attribute_facet_options[$key][$available_attribute_term->id()] = $selected_attribute_term_label;
          $selected_attribute_parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($available_attribute_term->id());
          $selected_attribute_parent = reset($selected_attribute_parent);
          $selected_attribute_title = substr($selected_attribute_parent->label(), strpos($selected_attribute_parent->label(), '|') + 2);
          $selected_attribute_facet_titles[$key] = $selected_attribute_title;
        }
      }
    }
    foreach ($selected_attribute_facet_options as $delta => $selected_attribute_facet_option_array) {
      $form['attribute_filter'][$selected_attribute_facet_titles[$delta]] = [
        '#type' => 'checkboxes',
        '#options' => $selected_attribute_facet_option_array,
        '#title' => $this->t($selected_attribute_facet_titles[$delta]),
        '#weight' => '0',
        '#required' => FALSE,
        '#attributes' => [
          'class' => [
            'node--type-product-listing__attribute-filter',
          ]
        ],
      ];
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
