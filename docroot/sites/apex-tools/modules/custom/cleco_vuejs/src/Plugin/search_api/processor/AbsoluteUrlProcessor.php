<?php

namespace Drupal\cleco_vuejs\Plugin\search_api\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\facets\Exception\Exception;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\FieldsProcessorPluginBase;

/**
 * Perform replacements based on regular expressions.
 *
 * @SearchApiProcessor(
 *   id = "cleco_absolute_url_processor",
 *   label = @Translation("Index URLs as absolute URLs."),
 *   description = @Translation("Index URLs as absolute URLs."),
 *   stages = {
 *     "preprocess_index" = -16,
 *     "preprocess_query" = -16,
 *     "postprocess_query" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AbsoluteUrlProcessor extends FieldsProcessorPluginBase {

  /**
   * {@inheritDoc}
   */
  protected function processField(FieldInterface $field) {
    if (!str_ends_with($field->getPropertyPath(), ':url')) {
      return;
    }

    $values = $field->getValues();
    foreach ($values as &$value) {
      try {
        $value = Url::fromUserInput($value)->setAbsolute()->toString();
      }
      catch (Exception $e) {
        // Ignore and do not try to convert the URL.
      }
    }

    $field->setValues($values);
  }

}
