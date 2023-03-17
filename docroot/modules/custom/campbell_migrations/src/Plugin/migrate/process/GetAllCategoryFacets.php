<?php

namespace Drupal\campbell_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManager;

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
    // Welded Chain.
    'W1_26966' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Weldless Chain.
    'W1_26967' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Hobby/Craft & Deco.
    'W1_26998' => [
      'ATT424',
      'ATT274',
      'ATT155',
      'ATT205',
      'ATT159',
      'ATT457',
      'ATT198',
    ],
    // Assemblies.
    'W1_26974' => [
      'TradeSizeFractional',
      'ATT379',
      'ATT744',
      'ATT515',
      'ATT155',
      'ATT781',
      'ATT335',
      'ATT457',
    ],
    // Forged Fittings.
    'W1_27002' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT217',
      'ATT864',
      'ATT797',
      'ATT776',
      'ATT345',
      'ATT214',
      'ATT817',
      'ATT205',
      'ATT770',
      'ATT460',
      'ATT457',
      'ATT458',
      'ATT459',
      'ATT861',
    ],
    // Overhead Lifting.
    'W1_27023' => [
      'TradeSizeFractional',
      'ATT154',
      'ATT865',
      'ATT857',
      'ATT864',
      'ATT425',
      'ATT219',
      'ATT395',
      'ATT274',
      'ATT457',
      'ATT862',
      'ATT861',
      'ATT863',
      'ATT867',
      'ATT868',
      'ATT869',
    ],
    // Lifting Clamps.
    'W1_27022' => [
      'ATT395',
      'ATT621',
      'ATT754',
      'ATT222',
      'ATT460',
      'ATT113',
    ],
    // Blocks.
    'W1_27024' => [
      'ATT137',
      'ATT138',
      'ATT315',
      'ATT212',
      'ATT363',
      'ATT364',
      'ATT274',
      'ATT767',
      'ATT453',
      'ATT288',
      'ATT205',
      'ATT457',
      'ATT460',
    ],
    // Cable & Wire Rope.
    'W1_27043' => [
      'ATT378',
      'ATT168',
      'ATT457',
      'ATT198',
    ],
    // Accessories.
    'W1_27025' => [
      'ATT200',
      'ATT797',
      'ATT746',
      'ATT424',
      'TradeSizeFractional',
      'ATT345',
      'ATT788',
      'ATT395',
      'ATT214',
      'ATT745',
      'ATT817',
      'ATT205',
      'ATT454',
      'ATT364',
      'ATT457',
      'ATT770',
    ],
    // Pre-Cut Packaged Chain & Cable.
    'W1_27261' => [
      'ATT679',
      'ATT457',
      'ATT198',
    ],
    // Chain & Cable Cutters.
    'W1_27260' => [
      'ATT425',
      'ATT145',
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
    $product_specifications = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree(
      'product_specifications',
      0,
      1,
      TRUE
    );

    $facets = $this->mapCategoryToFacetsList($value);
    $all_terms_array = [];

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
