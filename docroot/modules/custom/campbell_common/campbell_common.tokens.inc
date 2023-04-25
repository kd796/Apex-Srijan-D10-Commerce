<?php

/**
 * @file
 * Implementing custom tokens.
 */

use Drupal\node\Entity\Node;

/**
 * Implements hook_token_info().
 *
 * Considering upto 3rd level category.
 */
function campbell_common_token_info() {
  $info['tokens']['node']['product_classifications_parent'] = [
    'name' => t('Product Classifications Parent'),
    'description' => t('Custom token to provide the top level Product Classification'),
  ];

  $info['tokens']['node']['product_classifications_child_first'] = [
    'name' => t('Product Classifications First Child'),
    'description' => t('Custom token to provide the first child Product Classification'),
  ];

  $info['tokens']['node']['product_classifications_child_second'] = [
    'name' => t('Product Classifications Second Child'),
    'description' => t('Custom token to provide the second child Product Classification'),
  ];

  $info['tokens']['node']['product_classifications_full_string'] = [
    'name' => t('Product Classifications Full String'),
    'description' => t('Custom token to provide the full Product Classification (for metadata)'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function campbell_common_tokens($type, $tokens, array $data = [], array $options = []) {
  $replacements = [];

  if ($type == 'node' && !empty($data['node'])) {
    $node = $data['node'];

    foreach ($tokens as $name => $original) {
      $call = FALSE;

      // Loop through the tokens, only react on our custom tokens.
      switch ($name) {
        case 'product_classifications_parent':
          $call = 'parent_level_1';
          break;

        case 'product_classifications_child_first':
          $call = 'parent_level_2';
          break;

        case 'product_classifications_child_second':
          $call = 'parent_level_3';
          break;

        case 'product_classifications_full_string':
          $call = 'full';
          break;

      }

      if ($call) {

        // Replace the token with the value returned from the function call.
        $replacements[$original] = _campbell_common_return_product_classification($node, $call);
      }
    }
  }
  return $replacements;
}

/**
 * Return taxonomy value at a specific hierarchy level determined by the caller.
 *
 * @param \Drupal\node\Entity\Node $node
 *   The node object to act upon.
 * @param string $call
 *   The call determines which level of the classification hierarchy we want.
 *
 * @return string
 *   Return the name of the Term or a blank string if none is available.
 */
function _campbell_common_return_product_classification(Node $node, $call) {
  $parents = [];

  // Load the classifications on the node.
  $classification_tids = $node->get('field_product_classifications')->getValue();
  $tid_parents = [];

  // Create an array of each term with its parents.
  foreach ($classification_tids as $tid) {
    $tid_parents[$tid['target_id']] = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($tid['target_id']);
  }

  if (!empty($tid_parents)) {

    // Build the full string of terms for use with metadata.
    $full_string = '';

    // The longest array provides the full hierarchy.
    if ($node->getType() == 'product') {
      foreach (max($tid_parents) as $t) {
        $full_string .= $t->getName() . ' ';
      }
      $longest = array_reverse(max($tid_parents));
    }
    elseif ($node->getType() == 'product_category') {
      foreach (min($tid_parents) as $t) {
        $full_string .= $t->getName() . ' ';
      }
      $longest = array_reverse(min($tid_parents));
    }
    $parents['full'] = trim($full_string);
    $i = 1;

    // Loop through and set the term name at each level.
    foreach ($longest as $term) {
      $parents['parent_level_' . $i] = $term->getName();
      $i++;
    }
  }

  // Provide the term name at requested level, empty string if none exists.
  return array_key_exists($call, $parents) ? $parents[$call] : '';
}
