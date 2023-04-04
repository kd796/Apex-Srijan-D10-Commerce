<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get ID Attribute.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_set_downloads_languages"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: cleco_set_downloads_languages
 *   source: text
 * @endcode
 */
class SetDownloadsLanguages extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $languages = [];

    $language_name = '';
    $attribute = $this->configuration['attribute'];
    if (empty($value)) {
      return $languages;
    }

    foreach ($value->children() as $child) {
      if ($child->attributes()->AttributeID == $attribute) {
        if (count($child->children()) > 1) {
          foreach ($child->children() as $item) {
            $language_name = (string) $item[0];
            $language = $this->getAllowedLanguage($language_name);
            if (!empty($language)) {
              $languages[] = $language;
            }
          }
        }
        else {
          $language_name = (string) $child->Value[0];
          $language = $this->getAllowedLanguage($language_name);
          if (!empty($language)) {
            $languages[] = $language;
          }
        }
      }
    }
    return $languages;
  }

  /**
   * Get Valid languages for the product download..
   *
   * @return string
   *   Returns allowed language.
   */
  public function getAllowedLanguage($language) {
    $allowed_language = '';
    $mapped_list = [
      'spanish' => 'spanish',
      'slovakian' => 'slovakian',
      'russian' => 'russian',
      'romanian' => 'romanian',
      'portuguese' => 'portuguese',
      'polish' => 'polish',
      'korean' => 'korean',
      'japanese' => 'japanese',
      'italian' => 'italian',
      'hungarian' => 'hungarian',
      'german' => 'german',
      'french' => 'french',
      'english' => 'english',
      'dutch' => 'dutch',
      'danish' => 'danish',
      'czech' => 'czech',
      'chinese' => 'chinese',
    ];

    if (array_key_exists(strtolower($language), $mapped_list)) {
      $allowed_language = $mapped_list[strtolower($language)];
    }
    return $allowed_language;
  }

}
