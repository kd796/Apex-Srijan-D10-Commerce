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
 * Map product Status.
 *
 * @MigrateProcessPlugin(
 *   id = "at_map_product_status"
 * )
 */
class MapProductStatus extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
    // Get default path for the logging.
    if (!isset($this->configuration['notification_logfile'])) {
      $this->configuration['notification_logfile'] = $this->getDefaultLogfile();
    }
    $status = 1;
    if (empty($value) or !is_object($value)) {
      $status = 1;
      return $status;
    }

    $status_condition = $this->getActiveStatusCondition();
    foreach ($value->children() as $child) {
      $attribute_id = (string) $child->attributes()->AttributeID;
      $id = (string) $child->attributes()->ID;
      if ($attribute_id != $status_condition['AttributeID']) {
        continue;
      }
      if (empty($id)) {
        continue;
      }

      // List of status.
      $status_list = $status_condition['ID'];
      if (!is_array($status_condition['ID'])) {
        $status_list = (array) $status_condition['ID'];
      }

      // Set status when criteria is not met.
      if (!in_array(strtolower($id), array_map('strtolower', $status_list))) {
        $status = 0;
        break;
      }

      break;
    }
    return $status;
  }

  /**
   * Get active status criteria.
   *
   * @return int
   *   Returns all category tid.
   */
  public function getActiveStatusCondition() {
    $condition = $this->configuration['condition'] ?? [];
    return $condition;
  }

}
