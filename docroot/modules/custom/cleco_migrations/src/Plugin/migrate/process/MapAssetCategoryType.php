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
 * Check if term exists and creates a new one if doesn't.
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_map_asset_category_type"
 * )
 */
class MapAssetCategoryType extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
    $asset_crossreference = $value->xpath('parent::Product/AssetCrossReference');
    $classification_reference = $value->xpath('parent::Product/ClassificationReference');

    $list = $this->getClassificationList($classification_reference);
    $migrated_classification_tid = [];
    if (!empty($list)) {
      $migrated_classification_tid = $this->getAllMigratedTaxonomyTid($list, $this->configuration['classification_instance']);
    }

    foreach ($asset_crossreference as $child) {
      $type = (string) $child->attributes()->Type;
      $asset_id = (string) $child->attributes()->AssetID;
      $mapped_type = $this->allowedDownloadTypes($type);

      if (empty($mapped_type)) {
        continue;
      }
      $mid = $this->getMigratedTaxonomyTid($asset_id, $this->configuration['product_download_instance']);
      if (empty($mid)) {
        continue;
      }

      $changed = 0;
      $media = $this->entityTypeManager->getStorage('media')->load($mid);

      // Process for Type and category for product downloads only.
      $bundle_info = $media->bundle->getValue();
      if ($bundle_info[0]['target_id'] != "product_downloads") {
        continue;
      }

      if (empty($media->field_type->value)) {
        $media->field_type->setValue($mapped_type);
        $changed = 1;
      }
      $category_list = $media->field_product_category->getValue();
      $existing_category_list = $this->getCategoryTids($category_list);

      foreach ($migrated_classification_tid as $curret_tid) {
        if (!in_array($curret_tid, $existing_category_list)) {
          $category_list[] = ['target_id' => $curret_tid];
          $existing_category_list[] = $curret_tid;
          $changed = 1;
        }
      }

      // Save media for any changes required.
      if ($changed) {
        $media->field_product_category->setValue($category_list);
        $media->save();
      }

    }
  }

  /**
   * Get category tid.
   *
   * @return array
   *   Returns all category tid.
   */
  public function getCategoryTids($category_list) {
    $list = [];
    foreach ($category_list as $category) {
      if (isset($category['target_id']) && !empty($category['target_id'])) {
        $list[] = $category['target_id'];
      }
    }
    return $list;
  }

  /**
   * Get classification list.
   *
   * @return array
   *   Returns all classification tid.
   */
  public function getClassificationList($classification_reference) {
    $list = [];
    $match_type_list = $this->configuration['classification_type'];
    foreach ($classification_reference as $child) {
      $id = (string) $child->attributes()->ClassificationID;
      $type = (string) $child->attributes()->Type;
      if (in_array($type, $match_type_list)) {
        $list[] = $id;
      }
    }
    return $list;
  }

}
