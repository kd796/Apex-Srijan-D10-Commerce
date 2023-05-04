<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\FieldsProcessorPluginBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Processor to add parent category and index categories names.
 *
 * @SearchApiProcessor(
 *   id = "cleco_add_parent_category_processor",
 *   label = @Translation("Index parent category along with secondary categories."),
 *   description = @Translation("Index parent category along with secondary categories."),
 *   stages = {
 *     "preprocess_index" = -16,
 *     "preprocess_query" = -16,
 *     "postprocess_query" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddParentCategoryProcessor extends FieldsProcessorPluginBase {

  /**
   * {@inheritDoc}
   */
  protected function processField(FieldInterface $field) {
    $target_fields = [
      'product_category',
      'media_product_category',
    ];

    if (!in_array($field->getFieldIdentifier(), $target_fields)) {
      return;
    }

    $values = $field->getValues();
    if (empty($values)) {
      return;
    }

    $terms = Term::loadMultiple($values);
    foreach ($values as &$value) {
      if (!is_numeric($value) || !isset($terms[$value])) {
        continue;
      }
      $term = $terms[$value];
      $value = $term->getName();
      $parent = $term->get('parent')->getValue()[0]['target_id'];
      if (!$parent) {
        continue;
      }

      $parent = Term::load($parent);
      if (!$parent) {
        continue;
      }

      $values[] = $parent->getName();
    }

    $field->setValues(array_unique($values));
  }

}
