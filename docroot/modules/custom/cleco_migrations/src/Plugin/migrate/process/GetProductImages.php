<?php

namespace Drupal\cleco_migrations\Plugin\migrate\process;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $media_info = [];
    $level = (isset($this->configuration['level'])) ? $this->configuration['level'] : '';
    switch ($level) {
      case 'asset':
        $media_info = $this->manageAssetMedia($row);
        break;

    }
    return $media_info;
  }

  /**
   * Process Asset level media processing.
   *
   * @return array
   *   Returns media information.
   */
  public function manageAssetMedia(Row $row) {
    $process_pdf = (isset($this->configuration['process_pdf'])) ? $this->configuration['process_pdf'] : 0;
    $process_image = (isset($this->configuration['process_image'])) ? $this->configuration['process_image'] : 0;
    $get_type = (isset($this->configuration['get_type'])) ? $this->configuration['get_type'] : '';
    $media_type = (isset($this->configuration['media_type'])) ? $this->configuration['media_type'] : '';
    $langcode = (isset($this->configuration['langcode'])) ? $this->configuration['langcode'] : '';

    $source = $this->configuration['process_params']['source'];
    $from = $this->configuration['process_params']['from'];
    $compare_bundle = $this->configuration['process_params']['bundle'];
    $current_bundle = ($from == "source") ? trim($row->getSourceProperty($source))
      : trim($row->getDestinationProperty($source));

    $params = $this->configuration['params'];
    $asset_id = $row->getSourceProperty($params['asset_id']);
    $alt_text = $row->getSourceProperty($params['asset_name']);

    if ($current_bundle != $compare_bundle) {
      return [];
    }

    // Process for Media product_downloads for File.
    $fid = NULL;
    if ($process_pdf && $get_type == "fid" && $media_type == "pdf") {
      try {
        $fid = $this->imageOps->getAndSavePdf($asset_id, $alt_text, $langcode);
      }
      catch (ImageNotFoundOnFtpException $e) {
        $message = '[Product PDF Asset] During import of "'
        . '" - Unable to find the PDF on the FTP server for asset: "'
        . $asset_id . '.pdf". ';
        $this->logMessage($this->configuration['notification_logfile'], $message);
      }
      if ($fid) {
        return ['target_id' => $fid];
      }
    }

    // Process for Media product_downloads for Image media .
    $mid = NULL;
    if ($process_pdf && $get_type == "mid" && $media_type == "image") {
      try {
        $mid = $this->imageOps->getAndSaveDownloadsImageMedia($asset_id, $alt_text, $langcode);
      }
      catch (ImageNotFoundOnFtpException $e) {
        $message = '[Product Image Asset] During import of "'
        . '" - Unable to find the image on the FTP server for asset: "'
        . $asset_id . '.jpg". ';
        $this->logMessage($this->configuration['notification_logfile'], $message);
      }
      if ($mid) {
        return ['target_id' => $mid];
      }
    }

    // Process for Media Image file.
    if ($process_image && $get_type == "fid" && $media_type == "image") {
      try {
        $fid = $this->imageOps->getAndSaveImage($asset_id, $alt_text, $langcode);
      }
      catch (ImageNotFoundOnFtpException $e) {
        $message = '[Product Image Asset] During import of "'
        . '" - Unable to find the image on the FTP server for asset: "'
        . $asset_id . '.jpg". ';
        $this->logMessage($this->configuration['notification_logfile'], $message);
      }
      if ($fid) {
        return ['target_id' => $fid, 'alt' => $alt_text];
      }
    }

    return [];

  }

}