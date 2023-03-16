<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\Plugin\migrate\process\CreateProductSpecifications as ApexProductSpecifications;
use Drupal\taxonomy\Entity\Term;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_create_product_specifications"
 * )
 */
class CreateProductSpecifications extends ApexProductSpecifications {

  /**
   * {@inheritdoc}
   */
  protected function loadOrCreateChildTerm($parent_label, $parent_term_id, $item_label, $vid = 'product_specifications') {
    $full_term_name = $parent_label . ' : ' . (string) $item_label;
    $term_name = $this->truncateString($full_term_name);

    if ($tid = $this->getTidByName($term_name, $vid)) {
      $term = Term::load($tid);
    }
    else {
      $term = Term::create([
        'name' => $term_name,
        'vid' => $vid,
        'field_long_name' => $full_term_name,
      ]);
    }

    $term->set('parent', $parent_term_id);
    $term->save();

    if ($tid = $this->getTidByName($term_name, $vid)) {
      $term = Term::load($tid);
    }

    return $term;
  }

  /**
   * Truncate exceeding lendth data.
   *
   * @param string $string
   *   The string to be truncated.
   * @param int $length
   *   The length to which the string should be truncated.
   * @param string $append
   *   The string to be appended to the end of truncated string.
   *
   * @return string
   *   The truncated string.
   */
  protected function truncateString(string $string, int $length = 255, string $append = "...") {
    $string = trim($string);

    if (strlen($string) > $length) {
      $string = wordwrap($string, $length - strlen($append));
      $string = explode("\n", $string, 2);
      $string = $string[0] . $append;
    }

    return $string;
  }

}
