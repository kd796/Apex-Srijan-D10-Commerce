<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_create_product_specifications"
 * )
 */
class CreateProductSpecifications extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */

  protected EntityTypeManager $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['allowed_attributes']) && !array_key_exists('allowed_attributes', $this->configuration)) {
      throw new MigrateException('Skip on value plugin is missing the allowed attributes configuration.');
    }

    $values_array = [];
    $vid = 'product_specifications';
    $langcode = $this->configuration['langcode'] ?? 'en';
    $parent_id = NULL;
    $parent_term_id = NULL;

    $properties = [];

    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }

    if (!empty($langcode)) {
      $properties['langcode'] = $langcode;
    }

    $product_specifications = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties($properties);

    if (!empty($value)) {
      foreach ($value->children() as $child) {
        $parent_label = NULL;
        $parent_term_id = NULL;
        $parent_id = (string) $child->attributes()->AttributeID;
        $validAttribute = $this->validateAttributeName($parent_id);

        if ($validAttribute) {
          foreach ($product_specifications as $product_specification) {
            if (str_contains($product_specification->label(), $parent_id) && str_contains($product_specification->label(), ' |~| ')) {
              $parent_label = str_replace($parent_id . ' |~| ', '', $product_specification->label());
              $parent_term_id = $product_specification->id();
              break;
            }
          }

          if (!empty($parent_term_id) && !empty($parent_label)) {
            $term = NULL;

            if ($child->getName() === 'MultiValue') {
              if (count($child->children()) > 1) {
                foreach ($child->children() as $item) {
                  $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $item, $vid, $langcode);
                }
              }
              else {
                $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child->Value, $vid, $langcode);
              }
            }
            else {
              $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child, $vid, $langcode);
            }

            if (is_object($term)) {
              $values_array[] = [
                'vid' => $vid,
                'target_id' => $term->id(),
              ];
            }
          }
        }
      }

      $values_array = json_encode($values_array);
    }

    return json_decode($values_array, TRUE);
  }

  /**
   * Creates a term with a parent. Then returns the loaded or created term.
   *
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
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term object or NULL.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function loadOrCreateChildTerm($parent_label, $parent_term_id, $item_label, $vid = 'product_specifications', $langcode = 'en') {
    $term_label = trim($item_label, ' ');
    if (empty($term_label)) {
      return '';
    }
    $term_name = $parent_label . ' :~: ' . (string) $item_label;
    $term_name = $this->truncateString($term_name);

    if ($tid = $this->getTidByName($term_name, $vid, $langcode)) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    }
    else {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->create([
        'name' => $term_name,
        'vid' => $vid,
        'langcode' => $langcode,
      ]);
    }

    $term->set('parent', $parent_term_id);
    $term->save();

    if ($tid = $this->getTidByName($term_name, $vid, $langcode)) {
      // $term = Term::load($tid);
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    }

    return $term;
  }

  /**
   * Load term by name.
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
   * Check for valid term.
   */
  protected function validateAttributeName($attribute = NULL) {
    $attributes_to_include = $this->configuration['allowed_attributes'];

    if (in_array($attribute, $attributes_to_include)) {
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
