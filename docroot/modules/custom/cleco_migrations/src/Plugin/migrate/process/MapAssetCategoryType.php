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
    $langcode = $this->configuration['langcode'] ?? '';

    $processed_list = [];
    // Process at SKU Gorup.
    foreach ($asset_crossreference as $child) {
      $type = (string) $child->attributes()->Type;
      $asset_id = (string) $child->attributes()->AssetID;
      $mapped_type = $this->allowedDownloadTypes($type);

      if (isset($processed_list[$asset_id])) {
        continue;
      }

      if (empty($mapped_type)) {
        continue;
      }
      $processed_list[$asset_id] = 1;
      $mid = $this->getMigratedTaxonomyTid($asset_id, $this->configuration['product_download_instance']);
      if (empty($mid)) {
        continue;
      }

      $media = $this->entityTypeManager->getStorage('media')->load($mid);

      // Process for Type and category for product downloads only.
      $bundle_info = $media->bundle->getValue();
      if ($bundle_info[0]['target_id'] != "product_downloads") {
        continue;
      }

      // Set Type and categories.
      $classification_tids = [];
      if ($mid) {
        $classification_tids = $this->getAllProductCategories($mid, $langcode);
      }
      $media->field_type->setValue($mapped_type);
      $media->field_product_category->setValue($classification_tids);
      $media->save();
    }

    // Process at SKU Level download assets.
    $sku_asset_crossreference = $value->xpath('parent::Product/Product/AssetCrossReference');
    foreach ($sku_asset_crossreference as $child) {
      $type = (string) $child->attributes()->Type;
      $asset_id = (string) $child->attributes()->AssetID;
      $mapped_type = $this->allowedDownloadTypes($type);

      if (isset($processed_list[$asset_id])) {
        continue;
      }

      if (empty($mapped_type)) {
        continue;
      }
      $processed_list[$asset_id] = 1;
      $mid = $this->getMigratedTaxonomyTid($asset_id, $this->configuration['product_download_instance']);
      if (empty($mid)) {
        continue;
      }

      $media = $this->entityTypeManager->getStorage('media')->load($mid);

      // Process for Type and category for product downloads only.
      $bundle_info = $media->bundle->getValue();
      if ($bundle_info[0]['target_id'] != "product_downloads") {
        continue;
      }

      // Set Type and categories.
      $classification_tids = [];
      if ($mid) {
        $classification_tids = $this->getAllProductCategories($mid, $langcode);
      }
      $media->field_type->setValue($mapped_type);
      $media->field_product_category->setValue($classification_tids);
      $media->save();
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
