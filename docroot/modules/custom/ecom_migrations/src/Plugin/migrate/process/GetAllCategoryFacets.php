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
    // Campbell Welded Chain.
    'W1_26966' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Campbell Weldless Chain.
    'W1_26967' => [
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT205',
      'ATT457',
      'ATT198',
    ],
    // Campbell Hobby/Craft & Deco.
    'W1_26998' => [
      'ATT424',
      'ATT274',
      'ATT155',
      'ATT205',
      'ATT159',
      'ATT457',
      'ATT198',
    ],
    // Campbell Assemblies.
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
    // Campbell Forged Fittings.
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
    // Campbell Overhead Lifting.
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
    // Campbell Lifting Clamps.
    'W1_27022' => [
      'ATT395',
      'ATT621',
      'ATT754',
      'ATT222',
      'ATT460',
      'ATT113',
    ],
    // Campbell Blocks.
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
    // Campbell Cable & Wire Rope.
    'W1_27043' => [
      'ATT378',
      'ATT168',
      'ATT457',
      'ATT198',
    ],
    // Campbell Accessories.
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
    // Campbell Pre-Cut Packaged Chain & Cable.
    'W1_27261' => [
      'ATT679',
      'ATT457',
      'ATT198',
    ],
    // Campbell Chain & Cable Cutters.
    'W1_27260' => [
      'ATT425',
      'ATT145',
    ],
    // Crescenttool Cutting - 'W1_719495'.
    'W1_719495' => [
      'ATT496',
      'ATT802893',
      'ATT340',
      'ATT769436',
      'ATT278',
      'ATT686141',
    ],
    // Crescenttool Demolition Tools -'W1_22487'.
    'W1_22487' => [
      'ATT496',
      'ATT802893',
      'ATT584',
      'ATT582',
      'ATT583',
    ],
    // Crescenttool Hex Keys -'W1_706367'.
    'W1_706367' => [
      'ATT496',
      'ATT802893',
      'ATT493',
      'ATT660',
      'ATT592',
      'ATT659',
    ],
    // Crescenttool Measuring -'W1_719524'.
    'W1_719524' => [
      'ATT802893',
      'ATT807193',
      'ATT127',
      'ATT130',
      'ATT592',
      'ATT593',
    ],
    // Crescenttool Pliers -'W1_22486'.
    'W1_22486' => [
      'ATT802893',
      'ATT496',
      'ATT259',
      'ATT226',
      'ATT880',
      'ATT451',
      'ATT115',
      'ATT714720',
    ],
    // Crescenttool Power Tool Accessories -'W1_755886'.
    'W1_755886' => [
      'ATT802893',
      'ATT496',
      'ATT804086',
      'ATT755881',
      'ATT592',
      'ATT278',
    ],
    // Crescenttool Ratchets and Drive Tools -'W1_22482'.
    'W1_22482' => [
      'ATT496',
      'ATT802893',
      'ATT804086',
      'ATT585',
      'ATT589',
      'ATT631',
      'ATT491',
      'ATT586',
      'ATT749756',
      'ATT714694',
      'ATT593',
      'ATT710',
    ],
    // Crescenttool Screwdrivers and Nutdrivers -'W1_22485'.
    'W1_22485' => [
      'ATT496',
      'ATT802893',
      'ATT415',
      'ATT631',
      'ATT806593',
    ],
    // Crescenttool Shaping -'W1_719537'.
    'W1_719537' => [
      'ATT496',
      'ATT802893',
      'ATT934',
      'ATT201',
      'ATT547',
    ],
    // Crescenttool Sockets -'W1_22481'.
    'W1_22481' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973',
      'ATT806802',
    ],
    // Crescenttool Storage -'W1_736078'.
    'W1_736078' => [
      'ATT802893',
    ],
    // Crescenttool Striking and Struck -'W1_706780'.
    'W1_706780' => [
      'ATT496',
      'ATT802893',
      'ATT807126',
      'ATT228',
      'ATT227',
      'ATT345',
    ],
    // Crescenttool Tool Sets -'W1_22484'.
    'W1_22484' => [
      'ATT496',
      'ATT802893',
    ],
    // Crescenttool Trade Tools -'W1_802905'.
    'W1_802905' => [
      'ATT806600',
      'ATT802893',
    ],
    // Crescenttool Wrenches -'W1_22483'.
    'W1_22483' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT491',
      'ATT585',
      'ATT714694',
      'ATT205',
      'ATT739685',
      'ATT739684',
    ],
    // Gearwrench Auto Specialty -'W1_15788'.
    'W1_15788' => [
      'ATT496',
      'ATT802893',
    ],
    // Gearwrench Cutting Tools -'W1_15789'.
    'W1_15789' => [
      'ATT496',
      'ATT802893',
    ],
    // Gearwrench Extraction Tools -'W1_785249'.
    'W1_785249' => [
      'ATT496',
      'ATT493',
      'ATT484',
      'ATT491',
    ],
    // Gearwrench Hex Keys -'W1_802014'.
    'W1_802014' => [
      'ATT496',
      'ATT802893',
      'ATT493',
      'ATT660',
      'ATT592',
      'ATT659',
    ],
    // Gearwrench Impact Products -'W1_15792'.
    'W1_15792' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT804086',
      'ATT491',
      'ATT744972',
      'ATT744973',
    ],
    // Gearwrench Lighting -'W1_727497'.
    'W1_727497' => [
      'ATT802893',
      'ATT714716',
      'ATT714694',
      'ATT592',
    ],
    // Gearwrench Pass Thru™ Tools -'W1_806799'.
    'W1_806799' => [
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
    // Gearwrench Pliers -'W1_15797'.
    'W1_15797' => [
      'ATT802893',
      'ATT496',
      'ATT259',
      'ATT226',
      'ATT880',
      'ATT451',
      'ATT115',
      'ATT714720',
    ],
    // Gearwrench Pry Bars -'W1_15798'.
    'W1_15798' => [
      'ATT496',
      'ATT802893',
      'ATT584',
      'ATT582',
      'ATT583',
    ],
    // Gearwrench Ratchets and Drive Tools -'W1_15793'.
    'W1_15793' => [
      'ATT496',
      'ATT802893',
      'ATT804086',
      'ATT585',
      'ATT589',
      'ATT631',
      'ATT491',
      'ATT586',
      'ATT749756',
      'ATT714694',
      'ATT593',
      'ATT710',
    ],
    // Gearwrench Screwdrivers and Nutdrivers -'W1_15795'.
    'W1_15795' => [
      'ATT496',
      'ATT802893',
      'ATT415',
      'ATT631',
    ],
    // Gearwrench Shop Assist Equipment -'W1_728251'.
    'W1_728251' => [
      'ATT802893',
    ],
    // Gearwrench Sockets -'W1_16113'.
    'W1_16113' => [
      'ATT496',
      'ATT802893',
      'ATT499',
      'ATT493',
      'ATT491',
      'ATT744972',
      'ATT744973',
      'ATT806802',
    ],
    // Gearwrench Striking and Struck -'W1_15799'.
    'W1_15799' => [
      'ATT496',
      'ATT802893',
      'ATT807126',
      'ATT807127',
      'ATT228',
      'ATT227',
      'ATT345',
    ],
    // Gearwrench Tethered Products -'W1_781017'.
    'W1_781017' => [
      'ATT496',
      'ATT802893',
      'ATT804086',
      'ATT783458',
    ],
    // Gearwrench Tool Sets -'W1_736539'.
    'W1_736539' => [
      'ATT496',
      'ATT802893',
    ],
    // Gearwrench Tool Storage -'W1_15791'.
    'W1_15791' => [
      'ATT802893',
      'ATT753947',
    ],
    // Gearwrench Torque Products -'W1_15794'.
    'W1_15794' => [
      'ATT806600',
      'ATT802893',
      'ATT484',
      'ATT585',
      'ATT714694',
      'ATT753929',
    ],
    // Gearwrench Wrenches -'W1_15796'.
    'W1_15796' => [
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
    // Sata Auto Specialty -'W1_846447'.
    'W1_846447' => [
      'ATT497',
      'ATT495',
      'ATT494',
      'ATT948',
      'ATT781',
      'ATT254',
      'ATT176',
      'ATT450',
    ],
    // Sata Clamping -'W1_846444'.
    'W1_846444' => [
      'ATT948',
      'ATT584880',
      'ATT584885',
      'ATT326',
      'ATT584797',
    ],
    // Sata Cutting and Filing -'W1_846459'.
    'W1_846459' => [
      'ATT948',
      'ATT769436',
      'ATT176',
      'ATT781',
      'ATT128',
    ],
    // Sata Hex Keys -'W1_846450'.
    'W1_846450' => [
      'ATT659',
      'ATT660',
      'ATT493',
      'ATT491',
    ],
    // Sata Impact Products -'W1_846451'.
    'W1_846451' => [
      'ATT491',
      'ATT804086',
      'ATT493',
    ],
    // Sata Insulated Tools -'W1_846452'.
    'W1_846452' => [
      'ATT414',
      'ATT948',
    ],
    // Sata Measuring and Layout -'W1_846458'.
    'W1_846458' => [
      'ATT948',
      'ATT128',
      'ATT133',
      'ATT130',
    ],
    // Sata Personal Protective Equipment -'W1_846456'.
    'W1_846456' => [
      'ATT345',
      'ATT948',
      'ATT425',
      'ATT867467',
      'ATT670298',
      'ATT867471',
    ],
    // Sata Pliers -'W1_846454'.
    'W1_846454' => [
      'ATT259',
      'ATT948',
      'ATT497',
    ],
    // Sata Power Tools -'W1_846455'.
    'W1_846455' => [
      'ATT22507',
      'ATT662382',
      'ATT728214',
      'ATT867472',
      'ATT837657',
      'ATT662382',
    ],
    // Sata Ratchets & Drive Tools -'W1_846457'.
    'W1_846457' => [
      'ATT585',
      'ATT484',
    ],
    // Sata Screwdrivers & Bitdrivers -'W1_846460'.
    'W1_846460' => [
      'ATT415',
      'ATT631',
      'ATT948',
      'ATT497',
    ],
    // Sata Shop Equipment -'W1_846461'.
    'W1_846461' => [
      'ATT867473',
      'ATT584933',
      'ATT673955',
      'ATT867475',
    ],
    // Sata Striking and Struck -'W1_846463'.
    'W1_846463' => [
      'ATT345',
      'ATT236',
      'ATT728177',
      'ATT563',
    ],
    // Sata Tool and Socket Sets -'W1_846464'.
    'W1_846464' => [
      'ATT497',
      'ATT484',
      'ATT493',
      'ATT496',
    ],
    // Sata Tool Storage -'W1_846465'.
    'W1_846465' => [
      'ATT345',
      'ATT948',
    ],
    // Sata Torque Products -'W1_846466'.
    'W1_846466' => [
      'ATT678639',
      'ATT714694',
      'ATT585',
      'ATT484',
    ],
    // Sata Wrenches -'W1_846467'.
    'W1_846467' => [
      'ATT491',
      'ATT496',
      'ATT714694',
      'ATT493',
      'ATT749756',
      'ATT867476',
      'ATT948',
      'ATT781',
      'ATT254',
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
    $all_terms_array;

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
