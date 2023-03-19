<?php

namespace Drupal\cleco_migrations\Helper\Traits;

/**
 * Trait for migration.
 */
trait MigrationHelperTrait {

  /**
   * Get hash data.
   *
   * @param mixed $data
   *   Data to be processed.
   *
   * @return string
   *   Return hashed data.
   */
  public function getHashKey($data) {
    $value = (!is_array($data)) ? (array) $data : $data;
    return hash('sha256', serialize(array_map('strval', $value)));
  }

  /**
   * Process multiple paragraph.
   *
   * @param int $tid
   *   Test to be processed.
   * @param string $source_id
   *   Sourceid of the migration.
   * @param string $migration_id
   *   Name of the migration of source taxonomy.
   */
  public function mapAddedTaxonomyTerm($tid, $source_id, $migration_id) {
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $this->connection->insert($table)
      ->fields(
        [
          'sourceid1' => $source_id,
          'destid1' => $tid,
          'source_ids_hash' => $this->getHashKey($source_id),
        ]
      )->execute();
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
   * Update hash information.
   *
   * @param string $hash_key
   *   Hash data of the image.
   * @param string $sourceid1
   *   Source id.
   * @param int $destid1
   *   Destination id.
   * @param string $migration_id
   *   Migraion instance.
   * @param string $source_row_status
   *   Migraion source row status.
   */
  public function updateMigrationRecord($hash_key, $sourceid1, $destid1, $migration_id, $source_row_status = 0) {
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    if (empty($table)) {
      return;
    }
    $this->connection->merge($table)
      ->key('source_ids_hash', $hash_key)
      ->fields([
        'source_ids_hash' => $hash_key,
        'sourceid1' => $sourceid1,
        'destid1' => $destid1,
        'source_row_status' => $source_row_status,
      ])
      ->execute();
  }

}
