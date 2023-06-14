<?php

namespace Drupal\atg_menu_link_field\Entity;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcher;

/**
 * Matcher class to get autocompletion results for entity reference.
 */
class AtgEntityAutocompleteMatcher extends EntityAutocompleteMatcher {

  /**
   * {@inheritDoc}
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {

    if (!isset($selection_settings['view']) || empty($selection_settings)) {
      // For URL Autocomplete.
      $matches = [];

      $options = [
        'target_type' => $target_type,
        'handler' => $selection_handler,
        'handler_settings' => $selection_settings,
      ];
      $handler = $this->selectionManager->getInstance($options);

      if (isset($string)) {
        // Get an array of matching entities.
        $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
        // @modified Results from 10 to 50.
        $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 50);

        foreach ($entity_labels as $values) {
          foreach ($values as $entity_id => $label) {
            $key = "$label ($entity_id)";
            // Strip things like starting/trailing white spaces, line breaks and
            // tags.
            $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
            // Names containing commas or quotes must be wrapped in quotes.
            $key = Tags::encode($key);
            $matches[] = [
              'value' => $key,
              'label' => $label . ' (' . $entity_id . ')',
            ];
          }
        }
      }
    }
    else {
      // For Views reference(View used to select the entities).
      $options = $selection_settings + [
        'target_type' => $target_type,
        'handler' => $selection_handler,
      ];
      $handler = $this->selectionManager->getInstance($options);

      if (isset($string)) {
        // Get an array of matching entities.
        $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
        $match_limit = isset($selection_settings['match_limit']) ? (int) $selection_settings['match_limit'] : 10;
        $entity_labels = $handler->getReferenceableEntities($string, $match_operator, $match_limit);

        // Loop through the entities and convert them into autocomplete output.
        foreach ($entity_labels as $values) {
          foreach ($values as $entity_id => $label) {
            $key = "$label ($entity_id)";
            // Strip things like starting/trailing white spaces, line breaks and
            // tags.
            $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
            // Names containing commas or quotes must be wrapped in quotes.
            $key = Tags::encode($key);
            $matches[] = ['value' => $key, 'label' => $label];
          }
        }
      }
    }

    return $matches;
  }

}
