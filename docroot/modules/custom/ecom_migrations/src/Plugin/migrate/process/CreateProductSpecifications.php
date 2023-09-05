<?php

namespace Drupal\ecom_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\Plugin\migrate\process\CreateProductSpecifications as ApexProductSpecifications;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "ecom_create_product_specifications"
 * )
 */
class CreateProductSpecifications extends ApexProductSpecifications implements ContainerFactoryPluginInterface {

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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
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

    $product_specifications = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'product_specifications',
    ]);

    if (empty($value)) {
      return $values_array;
    }

    // Load units data.
    $units = $value->xpath('/*/UnitList/Unit');
    $unit_list = [];
    foreach ($units as $item) {
      $unit_list[(string) $item->attributes()->ID] = (string) $item->Name;
    }

    foreach ($value->children() as $child) {
      $parent_label = NULL;
      $parent_term_id = NULL;
      $parent_id = (string) $child->attributes()->AttributeID;
      $validAttribute = $this->validateAttributeName($parent_id);

      $unit = '';
      if (isset($child->attributes()->UnitID) && isset($unit_list[(string) $child->attributes()->UnitID])) {
        $unit = ' ' . $unit_list[(string) $child->attributes()->UnitID];
      }

      if (!$validAttribute) {
        continue;
      }

      $attributeWeight = array_search($parent_id, $this->configuration['allowed_attributes']);

      foreach ($product_specifications as $product_specification) {
        if (str_starts_with($product_specification->label(), $parent_id . ' | ')) {
          $parent_label = str_replace($parent_id . ' | ', '', $product_specification->label());
          $parent_term_id = $product_specification->id();
          break;
        }
      }

      if (empty($parent_term_id) || empty($parent_label)) {
        continue;
      }

      $term = NULL;

      if ($child->getName() === 'MultiValue') {
        foreach ($child->children() as $item) {
          if (isset($item->attributes()->UnitID) && isset($unit_list[(string) $item->attributes()->UnitID])) {
            $unit = ' ' . $unit_list[(string) $item->attributes()->UnitID];
          }
          $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $item . $unit);
          if (is_object($term)) {
            $values_array[] = $this->addToValues($vid, $term);
          }
        }
      }
      else {
        $term = $this->loadOrCreateChildTerm($parent_label, $parent_term_id, $child . $unit);
        $values_array[] = $this->addToValues($vid, $term);
      }
    }

    // Sort the specifications by key (weight).
    ksort($values_array);

    $values_array = json_encode($values_array);

    return json_decode($values_array, TRUE);
  }

  /**
   * The Function loadOrCreateChildTerm.
   */
  protected function loadOrCreateChildTerm($parent_label, $parent_term_id, $item_label, $vid = 'product_specifications') {
    $full_term_name = $parent_label . ' : ' . (string) $item_label;
    $full_term_name = strip_tags($full_term_name);
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
   * The function truncateString.
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

  /**
   * Common function to add values to array.
   */
  protected function addToValues($vid, $term) {
    if (is_object($term)) {
      $values_array = [
        'vid' => $vid,
        'target_id' => $term->id(),
      ];
    }
    return $values_array;
  }

}
