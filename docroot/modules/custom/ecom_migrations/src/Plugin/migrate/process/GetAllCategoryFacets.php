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
    // Chain
    // Accessories.
    'W2_22448' => [
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
    // Assemblies.
    'W2_886499' => [
      'TradeSizeFractional',
      'ATT379',
      'ATT744',
      'ATT515',
      'ATT155',
      'ATT781',
      'ATT335',
      'ATT457',
    ],
    // Blocks.
    'W2_22450' => [
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
    // Cable.
    'W2_722598' => [
      'ATT378',
      'ATT168',
      'ATT457',
      'ATT198',
    ],
    // Forged Fittings.
    'W2_886509' => [
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
    // Hobby, Craft & Decorator.
    'W2_886507' => [
      'ATT424',
      'ATT274',
      'ATT155',
      'ATT205',
      'ATT159',
      'ATT457',
      'ATT198',
    ],
    // Lifting Clamps.
    'W2_22452' => [
      'ATT395',
      'ATT621',
      'ATT754',
      'ATT222',
      'ATT460',
      'ATT113',
    ],
    // Overhead Lifting.
    'W2_886508' => [
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
    // Pre-Cut Packaged & Cable.
    'W2_723005' => [
      'ATT679',
      'ATT457',
      'ATT198',
    ],
    // Welded Chain.
    'W2_886504' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Weldless Chain.
    'W2_886505' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Hand Tools
    // Cutting & Threading.
    'W2_16008' => [
      'ATT496',
      'ATT802893',
      'ATT340',
      'ATT769436',
      'ATT176',
      'ATT781',
      'ATT948',
      'ATT128',
      'ATT278',
      'ATT934',
      'ATT201',
      'ATT547',
      'ATT686141',
    ],
    // Extraction Tools.
    'W2_785253' => [
      'ATT496',
      'ATT493',
      'ATT484',
      'ATT491',
    ],
    // Hex Keys.
    'W2_16003' => [
      'ATT496',
      'ATT802893',
      'ATT493',
      'ATT660',
      'ATT592',
      'ATT491',
      'ATT659',
    ],
    // Impact Products.
    'W2_16002' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT804086',
      'ATT491',
      'ATT744972',
      'ATT744973',
    ],
    // Measuring & Layout.
    'W2_16010' => [
      'ATT802893',
      'ATT807193',
      'ATT127',
      'ATT130',
      'ATT128',
      'ATT133',
      'ATT130',
      'ATT948',
      'ATT592',
      'ATT593',
    ],
    // Pass-Thru™ Tools.
    'W2_886492' => [
      'ATT496',
      'ATT802893',
      'ATT804086',
      'ATT484',
      'ATT499',
      'ATT493',
      'ATT744972',
      'ATT744973',
      'ATT806802',
    ],
    // Pliers.
    'W2_16007' => [
      'ATT802893',
      'ATT496',
      'ATT259',
      'ATT226',
      'ATT880',
      'ATT451',
      'ATT115',
      'ATT714720',
    ],
    // Power Tool Accessories.
    'W2_755890' => [
      'ATT802893',
      'ATT496',
      'ATT804086',
      'ATT755881',
      'ATT592',
      'ATT278',
    ],
    // Pry Bars & Demolition.
    'W2_16012' => [
      'ATT496',
      'ATT802893',
      'ATT584',
      'ATT582',
      'ATT583',
    ],
    // Pulling & Clamping.
    'W2_16013' => [
      'ATT948',
      'ATT584880',
      'ATT584885',
      'ATT326',
      'ATT584797',
    ],
    // Ratchets & Drive Tools.
    'W2_722357' => [
      'ATT496',
      'ATT802893',
      'ATT804086',
      'ATT585',
      'ATT589',
      'ATT631',
      'ATT491',
      'ATT586',
      'ATT714694',
      'ATT593',
    ],
    // Screwdrivers & Nut Drivers.
    'W2_16006' => [
      'ATT496',
      'ATT802893',
      'ATT415',
      'ATT631',
      'ATT806593',
    ],
    // Sockets.
    'W2_722294' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973',
      'ATT806802',
    ],
    // Striking & Struck.
    'W2_16011' => [
      'ATT496',
      'ATT802893',
      'ATT807126',
      'ATT807127',
      'ATT228',
      'ATT345',
      'ATT236',
    ],
    // Tethered Products.
    'W2_783464' => [
      'ATT496',
      'ATT802893',
      'ATT804086',
      'ATT783458',
    ],
    // Tool Sets.
    'W2_16016' => [
      'ATT496',
      'ATT802893',
    ],
    // Torque Products.
    'W2_16005' => [
      'ATT802893',
      'ATT484',
      'ATT585',
      'ATT714694',
      'ATT753929',
    ],
    // Trade Tools.
    'W2_722455' => [
      'ATT496',
      'ATT802893',
    ],
    // Wrenches.
    'W2_16004' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT491',
      'ATT585',
      'ATT749756',
      'ATT714694',
      'ATT205',
      'ATT739685',
      'ATT739684',
    ],
    // Shop Equipment
    // Air Tools.
    'W2_886554' => [
      'ATT22507',
      'ATT662382',
      'ATT728214',
      'ATT867472',
      'ATT837657',
      'ATT662382',
    ],
    // Creepers, Stools & Rolling Seats.
    'W2_886555' => [
      'ATT802893',
      'ATT584933',
    ],
    // Jacks.
    'W2_886556' => [
      'ATT802893',
      'ATT867473',
      'ATT584933',
      'ATT673955',
      'ATT867475',
    ],
    // Lighting.
    'W2_886288' => [
      'ATT802893',
      'ATT714716',
      'ATT714694',
      'ATT592',
    ],
    // Safety Equipment.
    'W2_886557' => [
      'ATT345',
      'ATT948',
      'ATT425',
      'ATT867467',
      'ATT670298',
      'ATT867471',
    ],
    // Shop Supplies.
    'W2_722651' => [
      'ATT802893',
      'ATT584933',
    ],
    // Vises.
    'W2_886645' => [
      'ATT948',
      'ATT584880',
      'ATT584885',
      'ATT326',
      'ATT584797',
    ],
    // Portable Storage.
    'W2_22459' => [
      'ATT802893',
      'ATT753947',
    ],
    // Storage Organization.
    'W2_22460' => [
      'ATT802893',
      'ATT753947',
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
