<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\cleco_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * Map product downloads media.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_map_product_images"
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

    $list = [];
    $processed_list = [];
    $migrated_ids = [];

    // Process Media at Product SKU GROUP level.
    $asset_crossreference = $value->xpath('parent::Product/AssetCrossReference');
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
        $message = "\nSyntax: time drush mim cleco_product_media  --uri=clecotools  --idlist='" . $id . "'\n";
        $message .= "Missing mapping for Product Image $id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $migrated_ids[$id]];
    }

    // Process media at SKU level.
    $sku_asset_crossreference = $value->xpath('parent::Product/Product/AssetCrossReference');
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
        $message = "\nSyntax: time drush mim cleco_product_media  --uri=clecotools  --idlist='" . $id . "'\n";
        $message .= "Missing mapping for Product Image $id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $migrated_ids[$id]];
    }

    return $list;
  }

}
