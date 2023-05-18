<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\FieldsProcessorPluginBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Processor to add parent category and index categories names.
 *
 * @SearchApiProcessor(
 *   id = "cleco_web_sort_order_processor",
 *   label = @Translation("Index web sort order including entities that have empty values."),
 *   description = @Translation("Index web sort order including entities that have empty values."),
 *   stages = {
 *     "preprocess_index" = -16,
 *     "preprocess_query" = -16,
 *     "postprocess_query" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class WebSortOrderProcessor extends FieldsProcessorPluginBase {

  /**
   * {@inheritDoc}
   */
  protected function testField($name, FieldInterface $field) {
    return $name === 'field_web_display_sort_order';
  }

  /**
   * {@inheritDoc}
   */
  protected function processField(FieldInterface $field) {
    $values = $field->getValues();
    $default_order = 99999;

    if (empty($values)) {
      $values[0] = $default_order;
      $field->setValues($values);
      return;
    }

    foreach ($values as &$value) {
      if ($value) {
        continue;
      }
      $value = $default_order;
    }

    $field->setValues($values);
  }

}
