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
 *   id = "cleco_set_classification_parent_term"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: cleco_set_classification_parent_term
 *   source: text
 * @endcode
 */
class SetClassificationParentTerm extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
    $tid = 0;
    $source_tid = $value;
    if (empty($source_tid)) {
      return $tid;
    }

    // Check for Skip condition.
    $skipped = $this->getSkipParameter($this->configuration, $row);
    if (!empty($skipped)) {
      return $tid;
    }

    // Check presence of source tid.
    $source_migration_id = ($this->configuration['source_migration_id']) ? $this->configuration['source_migration_id'] : '';
    if (!empty($source_migration_id)) {
      $tid = $this->getMigratedTaxonomyTid($source_tid, $source_migration_id);
    }
    if (!empty($tid)) {
      return $tid;
    }

    // Create taxonomy.
    if (empty($tid) && isset($this->configuration['params']['create_term']) && $this->configuration['params']['create_term'] == 1) {
      $term_data = $this->getTaxonomyTermData($this->configuration, $row);
      $tid = $this->createTaxonomyTerm($term_data, $this->configuration, $this->configuration['vocabulary']);
      $this->mapAddedTaxonomyTerm($tid, $source_tid, $source_migration_id);
    }
    return $tid;
  }

  /**
   * Check for skip parameter.
   *
   * @param mixed $configuration
   *   Configuration data.
   * @param Drupal\migrate\Row $row
   *   Row containing complete info.
   *
   * @return mixed
   *   Returns skip if mapping should be skipped otherwise blank.
   */
  public function getSkipParameter($configuration, Row $row) {
    $skipped = "";
    $check_skip_params = (isset($configuration['params']) && isset($configuration['params']['check_skip_params']))
      ? $configuration['params']['check_skip_params'] : 0;
    if ($check_skip_params) {
      $match_term = (isset($configuration['skip_params']['term'])) ? $configuration['skip_params']['term'] : "";
      $source_field = (isset($configuration['skip_params']['source'])) ? trim($configuration['skip_params']['source']) : "";
      $source_from = (isset($configuration['skip_params']['source_from'])) ? trim($configuration['skip_params']['source_from']) : "";
      $skip_data = "";
      if (!empty($match_term) && !empty($source_field) && !empty($source_from)) {
        $skip_data = ($source_from == "source") ? trim($row->getSourceProperty($source_field))
          : trim($row->getDestinationProperty($source_field));
        if (strtolower($skip_data) == strtolower($match_term)) {
          $skipped = "skip";
        }
      }
    }
    return $skipped;
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

  /**
   * Get taxonomy term data.
   *
   * @param array $config
   *   Configuration data.
   * @param Drupal\migrate\Row $row
   *   Row containing complete info.
   *
   * @return mixed
   *   Returns skip if mapping should be skipped otherwise blank.
   */
  public function getTaxonomyTermData(array $config, Row $row) {
    $output = [];
    foreach ($config['create_term'] as $configuration) {
      $source_field = (isset($configuration['source'])) ? trim($configuration['source']) : "";
      $source_from = (isset($configuration['source_from'])) ? trim($configuration['source_from']) : "";
      $data = ($source_from == "source") ? trim($row->getSourceProperty($source_field))
          : trim($row->getDestinationProperty($source_field));
      $output[$source_field] = $data;
    }
    return $output;
  }

}
