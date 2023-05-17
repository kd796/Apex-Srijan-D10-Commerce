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
 * Map product model content.
 *
 * @MigrateProcessPlugin(
 *   id = "at_map_product_model"
 * )
 */
class MapProductModel extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

    $model_reference = $value->xpath('parent::Product/Product');
    $list = [];
    foreach ($model_reference as $child) {
      $id = (string) $child->attributes()->ID;
      $nid = $this->getMigratedTaxonomyTid($id, $this->configuration['migration_instance']);
      if (empty($nid)) {
        $message = "Missing Product download for $id";
        echo $message;
        $message .= "\ntime drush mim at_product_model  --uri=apex-tools  --idlist='" . $id . "'";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }
      $list[] = ['target_id' => $nid];
    }
    return $list;
  }

}
