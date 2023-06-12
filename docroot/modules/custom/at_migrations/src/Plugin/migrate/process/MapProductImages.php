<?php

namespace Drupal\at_migrations\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\at_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * Map product downloads media.
 *
 * @MigrateProcessPlugin(
 *   id = "at_map_product_images"
 * )
 */
class MapProductImages extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
    // Logfile path logging information.
    if (!isset($this->configuration['notification_logfile'])) {
      $this->configuration['notification_logfile'] = $this->getDefaultLogfile();
    }

    $skip_primary_list = $this->configuration['skip_primary_list'] ?? 0;

    $list = [];
    $processed_list = [];
    $migrated_ids = [];

    // Process Media at Product SKU GROUP level.
    $asset_crossreference = $value->xpath('parent::Product/AssetCrossReference');
    if ($skip_primary_list) {
      $this->processSkipList($processed_list, $asset_crossreference);
    }
    $asset_list = $this->getImageList($asset_crossreference);
    if (!empty($asset_list)) {
      $migrated_ids = $this->getAllMigratedMapId($asset_list, $this->configuration['migration_instance']);
    }
    foreach ($asset_list as $id) {
      if (isset($processed_list[$id])) {
        continue;
      }
      $processed_list[$id] = 1;
      if (!isset($migrated_ids[$id])) {
        $message = "Missing Product Image:: Asset ID: $id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $migrated_ids[$id]];
    }

    // Process media at SKU level.
    $sku_asset_crossreference = $value->xpath('parent::Product/Product/AssetCrossReference');
    if ($skip_primary_list) {
      $this->processSkipList($processed_list, $sku_asset_crossreference);
    }
    $asset_list = $this->getImageList($sku_asset_crossreference);

    if (!empty($asset_list)) {
      $migrated_ids = $this->getAllMigratedMapId($asset_list, $this->configuration['migration_instance']);
    }
    foreach ($asset_list as $id) {
      if (isset($processed_list[$id])) {
        continue;
      }
      $processed_list[$id] = 1;
      if (!isset($migrated_ids[$id])) {
        $message = "Missing Product Image:: Asset ID: $id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $migrated_ids[$id]];
    }
    return $list;
  }

  /**
   * Add skip asset type in the list.
   *
   * @param array $processed_list
   *   Skip item list.
   * @param mixed $asset_crossreference
   *   Asset data.
   */
  public function processSkipList(array &$processed_list, $asset_crossreference) {
    $skip_list = ['Primary Image'];
    foreach ($asset_crossreference as $child) {
      $id = (string) $child->attributes()->AssetID;
      $type = (string) $child->attributes()->Type;
      if (in_array($type, $skip_list)) {
        $processed_list[$id] = 1;
      }
    }
  }

}
