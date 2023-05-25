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
 * Map product classification terms.
 *
 * @MigrateProcessPlugin(
 *   id = "at_map_product_classification"
 * )
 */
class MapProductClassification extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

    $apply_instance_suffix = $this->configuration['apply_instance_suffix'] ?? 0;
    $classification_reference = $value->xpath('parent::Product/ClassificationReference');
    $classification_list = $this->getClassificationList($classification_reference);
    $list = [];
    $default_migration_instance = $this->configuration['migration_instance'];
    foreach ($classification_list as $id) {
      $migrated_tid = NULL;
      $migration_instance = $default_migration_instance;

      // Construct migration instance.
      if ($apply_instance_suffix) {
        $term_prefix = $this->getTermPrefix($id);
        $migration_instance = $this->getMigrationInstance($term_prefix, $default_migration_instance);
      }

      $migrated_tid = $this->getMigratedTaxonomyTid($id, $migration_instance);
      if (empty($migrated_tid)) {
        $message = "Missing mapping for Classification  $id";
        $this->logMessage($this->configuration['notification_logfile'], $message);
        continue;
      }

      $list[] = ['target_id' => $migrated_tid];
    }
    return $list;
  }

  /**
   * Get taxonomy term prefix.
   *
   * @param string $term_id
   *   Term Name.
   * @param string $separator
   *   Term separator.
   *
   * @return mixed
   *   Returns skip if mapping should be skipped otherwise blank.
   */
  public function getTermPrefix($term_id, $separator = "_") {
    $prefix = "";
    $term_list = explode($separator, $term_id);
    if ($term_list[0]) {
      $prefix = strtolower($term_list[0]);
    }
    return $prefix;
  }

  /**
   * Construct migration instance.
   *
   * @param string $term_prefix
   *   Term prefix value.
   * @param string $default_migration_instance
   *   Default migration instnace.
   * @param string $term_prefix_separator
   *   Term prefix separator in the configuration.
   *
   * @return string
   *   Returns name of the migration instance.
   */
  public function getMigrationInstance($term_prefix = "", $default_migration_instance = "", $term_prefix_separator = "suffix_") {
    $migration_instance = $default_migration_instance;
    if (empty($term_prefix)) {
      return $migration_instance;
    }

    // Look for instance prefix presence for instance contruction.
    $migration_instance_prefix = $this->configuration['migration_instance_prefix'] ?? "";
    if (empty($migration_instance_prefix)) {
      return $migration_instance;
    }

    // Look for term prefix and process instance.
    $term_prefix_name = $term_prefix_separator . $term_prefix;
    $config_term_suffix = $this->configuration[$term_prefix_name] ?? "";
    if (empty($config_term_suffix)) {
      return $migration_instance;
    }

    // Create the instance.
    $migration_instance = $migration_instance_prefix . $config_term_suffix;
    return $migration_instance;
  }

}
