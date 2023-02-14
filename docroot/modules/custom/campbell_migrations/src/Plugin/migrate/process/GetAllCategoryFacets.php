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
      'ATT539',
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT249',
      'ATT250',
      'ATT205',
      'ATT457',
      'ATT198',
      'ATT545',
      'ATT546',
      'ATT447',
      'ATT442',
      'MUSAICON',
    ],
    // Weldless Chain.
    'W1_26967' => [
      'ATT539',
      'TradeSizeFractional',
      'ATT424',
      'ATT274',
      'ATT405',
      'ATT249',
      'ATT250',
      'ATT205',
      'ATT457',
      'ATT198',
      'ATT545',
      'ATT546',
      'ATT447',
      'ATT442',
      'MUSAICON',
    ],
    // Hobby/Craft & Deco.
    'W1_26998' => [
      'ATT539',
      'ATT424',
      'ATT274',
      'ATT155',
      'ATT249',
      'ATT250',
      'ATT205',
      'ATT159',
      'ATT457',
      'ATT198',
      'ATT739',
      'ATT546',
      'ATT447',
      'MUSAICON',
    ],
    // Assemblies.
    'W1_26974' => [
      'ATT539',
      'TradeSizeFractional',
      'ATT379',
      'ATT679',
      'ATT744',
      'ATT515',
      'ATT155',
      'ATT781',
      'ATT335',
      'ATT923',
      'ATT440',
      'ATT447',
      'ATT457',
      'MUSAICON',
    ],
    // Forged Fittings.
    'W1_27002' => [
      'ATT539',
      'TradeSizeFractional',
      'ATT424',
      'ATT217',
      'ATT864',
      'ATT797',
      'ATT802',
      'ATT803',
      'ATT805',
      'ATT798',
      'ATT291',
      'TorqueValue',
      'ATT776',
      'ATT345',
      'ATT214',
      'ATT817',
      'ATT858',
      'ATT859',
      'Quik-AlloySlingHooks',
      'Quik-AlloySlingHookwithLatch',
      'ATT205',
      'ATT770',
      'ATT460',
      'ATT457',
      'ATT458',
      'ATT459',
      'ATT777',
      'ATT778',
      'ATT790',
      'ATT789',
      'ATT336',
      'ATT861',
      'ATT804',
      'ATT807',
      'ATT808',
      'ATT809',
      'ATT748',
      'ATT749',
      'ATT750',
      'ATT751',
      'ATT688',
      'ATT774',
      'ATT775',
      'ATT791',
      'ATT758',
      'ATT792',
      'ATT793',
      'ATT810',
      'ATT799',
      'ATT757',
      'ATT800',
      'ATT811',
      'ATT794',
      'ATT795',
      'ATT801',
      'ATT440',
      'MUSAICON',
    ],
    // Overhead Lifting.
    'W1_27023' => [
      'ATT539',
      'TradeSizeFractional',
      'ATT154',
      'ATT865',
      'ATT857',
      'ATT864',
      'ATT425',
      'ATT219',
      'ATT395',
      'ATT274',
      'ATT782',
      'ATT916',
      'ATT917',
      'ATT457',
      'ATT862',
      'ATT861',
      'ATT863',
      'ATT867',
      'ATT868',
      'ATT869',
      'ATT870',
      'ATT871',
      'ATT872',
      'ATT873',
      'ATT874',
      'ATT875',
      'ATT748',
      'ATT749',
      'ATT750',
      'ATT751',
      'ATT688',
      'ATT853',
      'ATT854',
      'ATT877',
      'ATT774',
      'ATT775',
      'ATT791',
      'ATT878',
      'ATT855',
      'ATT856',
      'ATT758',
      'ATT794',
      'ATT806',
      'ATT876',
      'ATT801',
      'ATT440',
      'MUSAICON',
    ],
    // Lifting Clamps.
    'W1_27022' => [
      'ATT539',
      'ATT395',
      'ATT621',
      'ATT330',
      'ATT754',
      'ATT222',
      'ATT225',
      'ATT460',
      'ATT918',
      'ATT781',
      'ATT326',
      'ATT327',
      'ATT123',
      'ATT245',
      'ATT121',
      'ATT286',
      'ATT113',
      'ATT157',
      'ATT748',
      'ATT749',
      'ATT750',
      'ATT751',
      'ATT688',
      'ATT774',
      'ATT775',
      'ATT791',
      'ATT937',
      'ATT758',
      'ATT757',
      'ATT800',
      'ATT440',
      'MUSAICON',
    ],
    // Blocks.
    'W1_27024' => [
      'ATT539',
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
      'ATT325',
      'ATT773',
      'ATT362',
      'ATT205',
      'ATT376',
      'ATT763',
      'ATT336',
      'ATT143',
      'ATT766',
      'ATT764',
      'ATT457',
      'ATT460',
      'ATT440',
      'MUSAICON',
    ],
    // Cable & Wire Rope.
    'W1_27043' => [
      'ATT539',
      'ATT378',
      'ATT168',
      'ATT457',
      'ATT198',
      'ATT546',
      'ATT447',
      'MUSAICON',
    ],
    // Accessories.
    'W1_27025' => [
      'ATT539',
      'ATT200',
      'ATT797',
      'ATT746',
      'ATT424',
      'TradeSizeFractional',
      'ATT345',
      'ATT140',
      'ATT139',
      'ATT788',
      'ATT395',
      'ATT214',
      'ATT745',
      'ATT817',
      'ATT182',
      'ATT247',
      'ATT753',
      'ATT205',
      'ATT454',
      'ATT336',
      'ATT364',
      'ATT376',
      'ATT780',
      'ATT245',
      'ATT320',
      'ATT781',
      'ATT908',
      'ATT326',
      'ATT752',
      'ATT249',
      'ATT250',
      'ATT400',
      'ATT329',
      'ATT779',
      'ATT335',
      'ATT349',
      'ATT748',
      'ATT749',
      'ATT750',
      'ATT751',
      'ATT688',
      'ATT774',
      'ATT782',
      'ATT783',
      'ATT784',
      'ATT785',
      'ATT786',
      'ATT457',
      'ATT770',
      'ATT187',
      'ATT146',
      'ATT147',
      'ATT440',
      'MUSAICON',
    ],
    // Pre-Cut Packaged Chain & Cable.
    'W1_27261' => [
      'ATT539',
      'ATT679',
      'ATT457',
      'ATT198',
      'ATT447',
      'MUSAICON',
    ],
    // Chain & Cable Cutters.
    'W1_27260' => [
      'ATT539',
      'ATT425',
      'ATT145',
      'ATT440',
      'MUSAICON',
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
