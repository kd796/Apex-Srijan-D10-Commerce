<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\cleco_migrations\ImageNotFoundOnFtpException;
use Drupal\cleco_migrations\ImageOperations;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\cleco_migrations\Helper\Traits\MigrationHelperTrait;

/**
 * Provides a cleco_get_product_images plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: cleco_get_product_images
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "cleco_get_product_images"
 * )
 */
class GetProductImages extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use MigrationHelperTrait;

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The image operations class.
   *
   * @var \Drupal\cleco_migrations\ImageOperations
   */
  protected ImageOperations $imageOps;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * The temp store object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private PrivateTempStoreFactory $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection, PrivateTempStoreFactory $temp_store_factory, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->tempStoreFactory = $temp_store_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->imageOps = new ImageOperations();

    // Prep Directory.
    ImageOperations::prepImageDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $media_info = [];

    // Get Asset Type.
    $user_type_id = '';
    if (is_object($value)) {
      $asset_info = $value->xpath('parent::Asset');
      if (isset($asset_info[0])) {
        $user_type_id = (string) $asset_info[0]->attributes()->UserTypeID;
      }
    }

    $original_extension = $this->getAssetExtension($value);
    $extension = $this->getFileExtensionMapped($original_extension);
    $level = (isset($this->configuration['level'])) ? $this->configuration['level'] : '';
    switch ($level) {
      case 'asset':
        $media_info = $this->manageAssetMedia($row, $extension, $original_extension, $user_type_id);
        break;

    }
    return $media_info;
  }

  /**
   * Process Asset level media processing.
   *
   * @param Drupal\migrate\Row $row
   *   Object containing asset information.
   * @param string $extension
   *   Asset extension.
   * @param string $original_extension
   *   Asset original extension.
   * @param string $user_type_id
   *   Asset UserTypeId.
   *
   * @return array
   *   Returns media information.
   */
  public function manageAssetMedia(Row $row, $extension = '', $original_extension = '', $user_type_id = '') {
    $process_pdf = (isset($this->configuration['process_pdf'])) ? $this->configuration['process_pdf'] : 0;
    $process_image = (isset($this->configuration['process_image'])) ? $this->configuration['process_image'] : 0;
    $get_type = (isset($this->configuration['get_type'])) ? $this->configuration['get_type'] : '';
    $media_type = (isset($this->configuration['media_type'])) ? $this->configuration['media_type'] : '';
    $langcode = (isset($this->configuration['langcode'])) ? $this->configuration['langcode'] : '';
    $process_map_media_icon = $this->configuration['process_map_media_icon'] ?? 0;
    $map_media_config = $this->configuration['map_media_config'] ?? [];
    $map_media_migration_id = $this->configuration['map_media_migration_id'] ?? 'cleco_product_media';

    $source = $this->configuration['process_params']['source'];
    $from = $this->configuration['process_params']['from'];
    $compare_bundle = $this->configuration['process_params']['bundle'];
    $current_bundle = ($from == "source") ? trim($row->getSourceProperty($source))
      : trim($row->getDestinationProperty($source));

    $params = $this->configuration['params'];
    $asset_id = $row->getSourceProperty($params['asset_id']) ?? "";
    $alt_text = $row->getSourceProperty($params['asset_name']) ?? $asset_id;

    if (empty($asset_id)) {
      return [];
    }
    if (empty($alt_text)) {
      $alt_text = "";
    }
    if ($current_bundle != $compare_bundle) {
      return [];
    }

    // Process for Media product_downloads for File.
    $fid = NULL;
    if ($process_pdf && $get_type == "fid" && $media_type == "pdf") {
      try {
        $fid = $this->imageOps->getAndSavePdf($asset_id, $alt_text, $langcode, $extension);
      }
      catch (ImageNotFoundOnFtpException $e) {
        $message = "Missing Product Downloads File:: Type: $user_type_id :: Extension: $extension :: Filename: $asset_id.$extension";
        $this->logMessage($this->configuration['notification_logfile'], $message);
      }
      catch (\Exception | FilesystemException $e) {
        $message = "Server Problem Product Downloads:: Type: $user_type_id :: Extension: $extension : Filename: $asset_id.$extension";
        $this->logMessage($this->configuration['notification_logfile'], $message);
      }
      if ($fid) {
        return ['target_id' => $fid];
      }
    }

    // Process for Media product_downloads for Image media .
    $mid = NULL;
    $pdf_media_extension = "jpg";

    if ($process_pdf && $get_type == "mid" && $media_type == "image") {
      // Process for mapped asset media configuration.
      $original_asset_id = $asset_id;
      $process_with_mapped_asset = 0;
      $parent_asset_id = '';
      if ($process_map_media_icon) {
        if (isset($map_media_config[$user_type_id])) {
          $process_with_mapped_asset = 1;
          $parent_asset_id = $map_media_config[$user_type_id];
        }
      }

      try {
        // Get it from map table if asset mapping is enabled.
        $hashKey = '';
        $source_id = '';
        if ($process_with_mapped_asset) {
          $source_id = strtolower($parent_asset_id . $langcode . 'media');
          $hashKey = $this->getHashKey($source_id);
          $mid = $this->getMigratedTaxonomyTid($source_id, $map_media_migration_id);
          if ($mid) {
            $message = "Product Download Asset Media Mapped :: Original ID: $parent_asset_id ($mid):: Current ID: $original_asset_id :: Type: $user_type_id";
            $this->logMessage('', $message, 'notification');
          }
          $asset_id = $parent_asset_id;
        }

        if (empty($mid)) {
          $mid = $this->imageOps->getAndSaveDownloadsImageMedia($asset_id, $alt_text, $langcode, $pdf_media_extension);
          // Update the map table for future reference.
          if ($process_with_mapped_asset && !empty($mid)) {
            $message = "Product Download Asset Media Mapped/Updated :: Original ID: $parent_asset_id ($mid):: Current ID: $original_asset_id :: Type: $user_type_id";
            $this->logMessage('', $message, 'notification');
            $this->updateMigrationRecord($hashKey, $source_id, $mid, $map_media_migration_id, 0);
          }
        }
      }
      catch (ImageNotFoundOnFtpException $e) {
        if ($process_with_mapped_asset) {
          $message = "Missing Product Downloads Media Mapped :: Original ID: $parent_asset_id :: Current ID: $original_asset_id :: Type: $user_type_id";
          $this->logMessage('', $message, 'notification');
        }
        else {
          $message = "Missing Product Downloads Media:: Type: $user_type_id :: Extension: $pdf_media_extension :: Filename: $asset_id.$pdf_media_extension";
          $this->logMessage($this->configuration['notification_logfile'], $message);
        }
      }
      catch (\Exception | FilesystemException $e) {
        if ($process_with_mapped_asset) {
          $message = "Server Problem Product Downloads Media Mapped :: Original ID: $parent_asset_id :: Current ID: $original_asset_id :: Type: $user_type_id";
          $this->logMessage('', $message, 'notification');
        }
        else {
          $message = "Server Problem Product Downloads Media:: Type: $user_type_id :: Extension: $pdf_media_extension :: Filename: $asset_id.$pdf_media_extension";
          $this->logMessage($this->configuration['notification_logfile'], $message);
        }
      }
      if ($mid) {
        return ['target_id' => $mid];
      }
    }

    // Process for Media Image file.
    if ($process_image && $get_type == "fid" && $media_type == "image") {
      try {
        $fid = $this->imageOps->getAndSaveImage($asset_id, $alt_text, $langcode, $extension);
      }
      catch (ImageNotFoundOnFtpException $e) {
        try {
          if ($extension != $original_extension) {
            $message = "Missing Media File (original):: Type: $user_type_id :: Extension: $original_extension :: Filename: $asset_id.$extension";
            $this->logMessage($this->configuration['notification_logfile'], $message);
          }
          if ($extension == $original_extension) {
            $message = "Missing Media File (original):: Type: $user_type_id :: Extension: $extension :: Filename: $asset_id.$extension";
            $this->logMessage($this->configuration['notification_logfile'], $message);
          }
          if ($extension != "jpg") {
            // Log message for asset missing and try with jpg extension.
            $message = "Missing Media File (original):: Type: $user_type_id :: Extension: $extension :: Filename: $asset_id.$extension";
            $this->logMessage($this->configuration['notification_logfile'], $message);
            $fid = $this->imageOps->getAndSaveImage($asset_id, $alt_text, $langcode, "jpg");
          }
        }
        catch (ImageNotFoundOnFtpException $e) {
          $message = "Missing Media File (alternate):: Type: $user_type_id :: Extension: $extension :: Filename: $asset_id.jpg";
          $this->logMessage($this->configuration['notification_logfile'], $message);
        }
        catch (\Exception | FilesystemException $e) {
          $message = "Server Problem Media File (alternate):: Type: $user_type_id :: Extension: $extension :: Filename: $asset_id.jpg";
          $this->logMessage($this->configuration['notification_logfile'], $message);
        }
      }
      catch (\Exception | FilesystemException $e) {
        $message = "Server Problem Media File:: Type: $user_type_id :: Extension: $extension :: Filename: $asset_id.jpg";
        $this->logMessage($this->configuration['notification_logfile'], $message);
      }
      if ($fid) {
        return ['target_id' => $fid, 'alt' => $alt_text];
      }
    }

    return [];

  }

  /**
   * Get Asset extension.
   *
   * @param mixed $value
   *   Information containing various attributes.
   *
   * @return string
   *   Return extension of the asset.
   */
  public function getAssetExtension($value) {
    $extension = "";
    $compare_extension_id = "asset.extension";
    if (is_object($value)) {
      foreach ($value as $child) {
        $id = (string) $child->attributes()->AttributeID;
        if ($id != $compare_extension_id) {
          continue;
        }
        $extension = (string) $child;
      }
    }
    return $extension;
  }

}
