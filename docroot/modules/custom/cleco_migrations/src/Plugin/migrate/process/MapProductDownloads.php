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
 *   id = "cleco_map_product_downloads"
 * )
 */
class MapProductDownloads extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

    $asset_crossreference = $value->xpath('parent::Product/AssetCrossReference');
    $list = [];
    foreach ($asset_crossreference as $child) {
      $type = (string) $child->attributes()->Type;
      $asset_id = (string) $child->attributes()->AssetID;
      $mapped_type = $this->allowedDownloadTypes($type);
      if (empty($mapped_type)) {
        continue;
      }
      $mid = $this->getMigratedTaxonomyTid($asset_id, $this->configuration['migration_instance']);
      if (empty($mid)) {
        $message = "\nSyntax: time drush mim cleco_product_media  --uri=clecotools  --idlist='" . $asset_id . "'\n";
        $message .= "Missing mapping for Product Download $asset_id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $mid];
    }
    return $list;
  }

}
