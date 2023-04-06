<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\migrate\ProcessPluginBase;
use Drupal\cleco_migrations\ImageNotFoundOnFtpException;
use Drupal\cleco_migrations\ImageOperations;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\cleco_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * Map product downloads media.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_map_product_downloads"
 * )
 */
class MapProductDownloads extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use MigrationHelperTrait;

  /**
   * The image operations class.
   *
   * @var \Drupal\cleco_migrations\ImageOperations
   */
  protected ImageOperations $imageOps;

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
   * The temp store object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private PrivateTempStoreFactory $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, Connection $connection, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
    $this->imageOps = new ImageOperations();

    // Prep Directory.
    ImageOperations::prepImageDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('tempstore.private'),
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Logfile path logging information.
    if (!isset($this->configuration['notification_logfile'])) {
      $this->configuration['notification_logfile'] = $this->getDefaultLogfile();
    }
    $create_media = $this->configuration['create_media'] ?? 0;
    $langcode = $this->configuration['langcode'] ?? '';

    $processed_list = [];
    $list = [];

    // Process for SKU GROUP Level Download Assets.
    $asset_crossreference = $value->xpath('parent::Product/AssetCrossReference');
    foreach ($asset_crossreference as $child) {
      $type = (string) $child->attributes()->Type;
      $asset_id = (string) $child->attributes()->AssetID;
      $mapped_type = $this->allowedDownloadTypes($type);
      if (isset($processed_list[$asset_id])) {
        continue;
      }
      if (empty($mapped_type)) {
        continue;
      }
      $processed_list[$asset_id] = 1;
      $mid = $this->getMigratedTaxonomyTid($asset_id, $this->configuration['migration_instance']);
      if (empty($mid)) {
        $message = "\nSyntax: time drush mim cleco_product_media  --uri=clecotools  --idlist='" . $asset_id . "'\n";
        echo $message;
        $message .= "Missing mapping for Product Download $asset_id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $mid];
    }

    return $list;
  }

  /**
   * Create product download media.
   *
   * @param string $asset_id
   *   Asset ID.
   * @param string $langcode
   *   Asset language code.
   *
   * @return array
   *   Returns media information.
   */
  public function createProductDownloadMedia($asset_id, $langcode) {
    // Create file for download media.
    try {
      $fid = $this->imageOps->getAndSavePdf($asset_id, $alt_text, $langcode);
    }
    catch (ImageNotFoundOnFtpException $e) {
      $message = '[Product PDF Asset] During import of "'
        . '" - Unable to find the PDF on the FTP server for asset: "'
        . $asset_id . '.pdf". ';
      $this->logMessage($this->configuration['notification_logfile'], $message);
    }

    // Create image media for download media.
    try {
      $mid = $this->imageOps->getAndSaveDownloadsImageMedia($asset_id, $alt_text, $langcode);
    }
    catch (ImageNotFoundOnFtpException $e) {
      $message = '[Product Image Asset] During import of "'
      . '" - Unable to find the image on the FTP server for asset: "'
      . $asset_id . '.jpg". ';
      $this->logMessage($this->configuration['notification_logfile'], $message);
    }
    // Create Download media and attach fid & mid and map to migration table.
    return [];
  }

}
