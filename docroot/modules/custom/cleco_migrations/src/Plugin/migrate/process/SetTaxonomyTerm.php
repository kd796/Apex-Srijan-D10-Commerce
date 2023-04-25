<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\cleco_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * Get Classification Parent Name.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_set_taxonomy_term"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: cleco_set_taxonomy_term
 *   source: text
 * @endcode
 */
class SetTaxonomyTerm extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $output = [];
    if (empty($value)) {
      return $output;
    }
    $mutiple = (isset($this->configuration['mutiple'])) ? $this->configuration['mutiple'] : 0;
    $source_migration_id = ($this->configuration['source_migration_id']) ? $this->configuration['source_migration_id'] : '';
    if (empty($source_migration_id)) {
      return $output;
    }
    foreach ($value as $source_tid) {
      if (!empty($source_tid)) {
        $tid = $this->getMigratedTaxonomyTid($source_tid, $source_migration_id);
      }
      if (!empty($tid)) {
        $output = [
          'vid' => 'product_classifications',
          'target_id' => $tid,
        ];
        if (!$mutiple) {
          return $output;
        }
      }
    }
    return $output;
  }

  /**
   * Get migrated taxonomy tid.
   *
   * @param string $source_id1
   *   Test to be processed.
   * @param string $migration_id
   *   Sourceid of the migration.
   *
   * @return int
   *   Returns migrated tid.
   */
  public function getMigratedTaxonomyTid($source_id1, $migration_id) {
    $tid = NULL;
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $query = $this->connection->select($table, 't');
    $query->addField('t', 'destid1');
    $query->condition('t.sourceid1', $source_id1, '=');
    $tid = $query->execute()->fetchField();
    return $tid;
  }

  /**
   * Create taxonomy term.
   *
   * @param array $term_data
   *   Value of the taxonomy.
   * @param array $configuration
   *   Configuration for the processing.
   * @param string $vocabulary_name
   *   Name of the vocabulary.
   *
   * @return int
   *   Returns created taxonomy tid.
   */
  public function createTaxonomyTerm(array $term_data, array $configuration, $vocabulary_name) {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->create([
      'vid' => $vocabulary_name,
    ]);
    foreach ($configuration['create_term'] as $data) {
      if ($data['map'] !== 1) {
        continue;
      }
      $source_field = $data['source'];
      $destination_field = $data['destination'];
      $value = $term_data[$source_field];
      $term->set($destination_field, $value);
    }
    $term->save();
    $tid = $term->id();
    if ($tid) {
      return $tid;
    }
  }

}
