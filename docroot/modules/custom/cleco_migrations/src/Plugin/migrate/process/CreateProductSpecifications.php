<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\cleco_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_create_product_specifications"
 * )
 */
class CreateProductSpecifications extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use MigrationHelperTrait;

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */

  protected EntityTypeManager $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Get attributelist, if we have predefined list.
    $get_predefined_attributelist = $this->configuration['get_predefined_attributelist'] ?? 0;
    if ($get_predefined_attributelist) {
      $this->configuration['allowed_attributes'] = $this->getAttributeList();
    }

    $get_excluded_attributelist = $this->configuration['get_excluded_attributelist'] ?? 0;
    $this->configuration['excluded_attributes'] = [];
    if ($get_excluded_attributelist) {
      $this->configuration['excluded_attributes'] = $this->getExcludedAttributeList();
    }

    if (empty($this->configuration['allowed_attributes']) && !array_key_exists('allowed_attributes', $this->configuration)) {
      throw new MigrateException('Skip on value plugin is missing the allowed attributes configuration.');
    }

    $values_array = [];
    $vid = 'product_specifications';
    $langcode = $this->configuration['langcode'] ?? 'en';

    $parent_migration_id = $this->configuration['parent_migration_id'] ?? '';
    $migration_id = $this->configuration['migration_id'] ?? '';
    $processed_list = [];

    $parent_id = NULL;
    $parent_term_id = NULL;

    $parent_specification_value = [];
    if (!empty($value) && is_object($value)) {
      $parent_specification_value = $value->xpath("../../Values");
    }

    $process_data = [];
    if (!empty($value) && is_object($value)) {
      $process_data[0] = $value;
    }

    $process_data[1] = $parent_specification_value[0] ?? [];
    foreach ($process_data as $data_value) {
      if (!empty($data_value)) {
        foreach ($data_value->children() as $child) {
          $parent_label = NULL;
          $parent_term_id = NULL;
          $parent_id = (string) $child->attributes()->AttributeID;

          // Skip if term is excluded.
          $excludedAttribute = $this->validateExcludedAttribute($parent_id);
          if ($excludedAttribute) {
            continue;
          }

          $validAttribute = $this->validateAttributeName($parent_id);
          if (!$validAttribute) {
            continue;
          }

          if (!empty($parent_migration_id)) {
            $parent_term_id = $this->getMigratedTaxonomyTid($parent_id, $parent_migration_id);
          }

          // If parent term is not present, skip the record.
          if (empty($parent_term_id)) {
            continue;
          }
          $parent_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($parent_term_id);
          $parent_name = $parent_term->label();
          $parent_label = str_replace($parent_id . ' |~| ', '', $parent_name);
          if (empty($parent_label)) {
            continue;
          }

          $term = NULL;
          $multi_value = 0;
          $unit_id = '';
          if ($child->getName() === 'MultiValue') {
            $multi_value = 1;
            if (count($child->children()) > 1) {
              foreach ($child->children() as $item) {
                $unit_id = (string) $item->attributes()->UnitID;
                $term = $this->loadOrCreateChildTerm($parent_id, $parent_label, $parent_term_id, $item, $vid, $langcode, $migration_id, $multi_value, $unit_id);
                if (is_object($term)) {
                  $term_id = $term->id();
                  if (isset($processed_list[$term_id])) {
                    continue;
                  }
                  $values_array[] = [
                    'vid' => $vid,
                    'target_id' => $term_id,
                  ];
                }
                $processed_list[$term_id] = 1;
                continue;
              }
              $term = NULL;
            }
            else {
              $unit_id = (string) $child->Value->attributes()->UnitID;
              $term = $this->loadOrCreateChildTerm($parent_id, $parent_label, $parent_term_id, $child->Value, $vid, $langcode, $migration_id, $multi_value, $unit_id);
            }
          }
          else {
            $unit_id = (string) $child->attributes()->UnitID;
            $term = $this->loadOrCreateChildTerm($parent_id, $parent_label, $parent_term_id, $child, $vid, $langcode, $migration_id, $multi_value, $unit_id);
          }
          if (is_object($term)) {
            $term_id = $term->id();
            if (isset($processed_list[$term_id])) {
              continue;
            }
            $values_array[] = [
              'vid' => $vid,
              'target_id' => $term_id,
            ];
            $processed_list[$term_id] = 1;
          }

        }
      }
    }
    $values_array = json_encode($values_array);
    return json_decode($values_array, TRUE);
  }

  /**
   * Creates a term with a parent. Then returns the loaded or created term.
   *
   * @param string $parent_id
   *   ID of the parent term.
   * @param string $parent_label
   *   The name/label of the parent term.
   * @param int $parent_term_id
   *   The term ID of the parent term.
   * @param string $item_label
   *   The name/label of the item you are loading/creating.
   * @param string $vid
   *   The vocabulary ID you are adding this term to.
   * @param string $langcode
   *   The language of the term.
   * @param string $migration_id
   *   Name of the migration ID.
   * @param int $multi
   *   Specifies whether it is part of multivalue or not.
   * @param string $unit
   *   Complete unit value.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term object or NULL.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function loadOrCreateChildTerm($parent_id, $parent_label, $parent_term_id, $item_label, $vid = 'product_specifications', $langcode = 'en', $migration_id = '', $multi = 0, $unit = '') {
    $item_label = trim($item_label, ' ');
    if (empty($item_label)) {
      return '';
    }

    // Prepare hashkey for term search in the migration.
    $child_key = strtolower($parent_id . $langcode . $item_label);
    $hashKey = $this->getHashKey($child_key);

    // Look into migration table and if found return the term.
    $migration_source_row_status = 0;
    if (!empty($migration_id)) {
      $tid = $this->getMigratedTaxonomyTid($hashKey, $migration_id);
      $migration_source_row_status = $this->getMigratedTaxonomyTidStatus($hashKey, $migration_id);
    }

    // Get attribute key from predefined list.
    $attribute_key = $this->getAttributeKey($parent_id);

    if (empty($attribute_key)) {
      $attribute_key = $this->constructAttributeKey($parent_id, $unit);
    }

    // Exceptional case for ATT662382 and unit l/s.
    if ($parent_id == 'ATT662382' && $unit == "l/s") {
      $attribute_key = "air_consumption_at_free_speed_scfm__s";
    }

    if (!empty($tid)) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
      $tid = NULL;
      if (is_object($term)) {
        $tid = $term->id();
        // Update taxonomy if it is marked to update.
        if ($migration_source_row_status) {
          $term->field_specification_attribute_id->setValue($parent_id);
          $term->field_specification_attr_key->setValue($attribute_key);
          $term->save();
          $this->updateMigrationRecord($hashKey, $hashKey, $tid, $migration_id, 0);
        }
      }
    }

    if ($tid && is_object($term)) {
      return $term;
    }

    // Prepare term name pattern.
    $term_name = $parent_label . ' :~: ' . (string) $item_label;
    $full_name = $term_name;
    $term_name = $this->truncateString($term_name);

    // Now look for term and create if not found.
    if ($tid = $this->getTidByName($term_name, $vid, $langcode)) {
      $this->updateMigrationRecord($hashKey, $hashKey, $tid, $migration_id, 0);
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    }
    else {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->create([
        'name' => $term_name,
        'vid' => $vid,
        'field_long_name' => $full_name,
        'langcode' => $langcode,
        'field_specification_attribute_id' => $parent_id,
        'field_specification_attr_key' => $attribute_key,
      ]);
    }

    $term->set('parent', $parent_term_id);
    $term->save();

    // Map the created term in the migration.
    if (is_object($term)) {
      $tid = $term->id();
      $this->updateMigrationRecord($hashKey, $hashKey, $tid, $migration_id, 0);
    }

    if ($tid = $this->getTidByName($term_name, $vid, $langcode)) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    }

    return $term;
  }

  /**
   * Load taxonomy term by name.
   *
   * @param string $name
   *   Name of the taxonomy term.
   * @param string $vocabulary
   *   Name of the vocabulary.
   * @param string $langcode
   *   The language of the term.
   *
   * @return int
   *   Returns taxonomy tid if present.
   */
  protected function getTidByName($name = NULL, $vocabulary = NULL, $langcode = NULL): int {
    $properties = [];

    if (!empty($name)) {
      $properties['name'] = $name;
    }

    if (!empty($vocabulary)) {
      $properties['vid'] = $vocabulary;
    }

    if (!empty($langcode)) {
      $properties['langcode'] = $langcode;
    }
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    return !empty($term) ? $term->id() : 0;
  }

  /**
   * Check for valid attribute ID.
   *
   * @param string $attribute
   *   Name of the attribute ID.
   *
   * @return bool
   *   Returns TRUE if it is listed in allowed_attributes otherwise FALSE.
   */
  protected function validateAttributeName($attribute = NULL) {
    $attributes_to_include = $this->configuration['allowed_attributes'];
    if (array_key_exists($attribute, $attributes_to_include) or in_array($attribute, $attributes_to_include)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get Attribute Key.
   *
   * @param string $attribute
   *   Name of the attribute ID.
   *
   * @return string
   *   Returns Attribute key.
   */
  protected function getAttributeKey($attribute = NULL) {
    $attributes_list = $this->getAttributeKeyList();
    $key = '';
    if (isset($attributes_list[$attribute])) {
      $key = $attributes_list[$attribute];
    }
    return $key;
  }

  /**
   * Check for excluded attribute ID.
   *
   * @param string $attribute
   *   Name of the attribute ID.
   *
   * @return bool
   *   Returns TRUE if it is in exclude list otherwise FALSE.
   */
  protected function validateExcludedAttribute($attribute = NULL) {
    $attributes_to_exclude = $this->configuration['excluded_attributes'];

    if (in_array($attribute, $attributes_to_exclude)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Truncate exceeding characters.
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
    if (mb_strlen($string) > $length) {
      $string = wordwrap($string, $length - mb_strlen($append));
      $string = explode("\n", $string, 2);
      $string = $string[0] . $append;
    }
    return $string;
  }

}
