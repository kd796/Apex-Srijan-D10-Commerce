<?php

namespace Drupal\ecom_migrations\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get All Product Classifications.
 *
 * @MigrateProcessPlugin(
 *   id = "get_all_category_facets"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: get_all_category_facets
 *   source: text
 * @endcode
 */
class GetAllCategoryFacets extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The mapping array.
   *
   * @var array|\string[][]
   */
  public static array $mapping = [
    // Chain - 'W1_15988'.
    'W1_15988' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Storage -'W1_886552'.
    'W1_886552' => [
      'ATT802893',
    ],
    // Shop Equipment -'W1_15990'.
    'W1_15990' => [
      'ATT867473',
      'ATT584933',
      'ATT673955',
      'ATT867475',
    ],
    // Hand Tools -'W1_15987'.
    'W1_15987' => [
      'ATT496',
      'ATT493',
      'ATT484',
      'ATT491',
    ],
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  EntityTypeManager $entity_type_manager) {
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
    $product_specifications = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree(
      'product_specifications',
      0,
      1,
      TRUE
    );
    $facets = $this->mapCategoryToFacetsList($value);
    // $all_terms_array = '';
    if (empty($facets) || empty($product_specifications)) {
      return json_decode($all_terms_array, TRUE);
    }

    foreach ($product_specifications as $spec) {
      $source_id = explode(' | ', $spec->label())[0];

      if (!in_array($source_id, $facets)) {
        continue;
      }

      $all_terms_array[] = [
        'vid' => 'product_specifications',
        'target_id' => $spec->id(),
      ];
    }

    $all_terms_array = json_encode($all_terms_array);

    return json_decode($all_terms_array, TRUE);
  }

  /**
   * Check for list of facets.
   */
  protected function mapCategoryToFacetsList($category_remote_id) {
    $mapping = self::$mapping;

    if (isset($mapping[$category_remote_id])) {
      return $mapping[$category_remote_id];
    }

    return [];
  }

}
