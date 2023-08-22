<?php

namespace Drupal\ecom_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageNotFoundOnFtpException;
use Drupal\apex_migrations\ImageOperations;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use League\Flysystem\FilesystemException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an ecom_get_category_menu_image plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: ecom_get_category_menu_image
 *     source: ecom
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "ecom_get_category_menu_image"
 * )
 */
class GetCategoryMenuImage extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The image operations class.
   *
   * @var \Drupal\apex_migrations\ImageOperations
   */
  protected ImageOperations $imageOps;

  /**
   * The media ID for the primary image.
   *
   * @var int
   */
  protected $mediaId;

  /**
   * The image asset array. Helps with finding and downloading images.
   *
   * @var array
   */
  protected $assets;

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected PrivateTempStoreFactory $tempStore;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tempStore = $temp_store;
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
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      throw new MigrateSkipProcessException();
    }

    $media_id = NULL;
    $this->mediaId = NULL;
    $this->assets = NULL;
    $sku = $row->getSourceIdValues()['remote_sku'] ?? '';
    $alt_text = $value->Name;

    $asset_cross_reference = $value->xpath('AssetCrossReference');
    $this->scanForCategoryImage($asset_cross_reference, 'Product Category');
    // If a media ID was returned then use that.
    if (!empty($this->mediaId)) {
      return $this->mediaId;
    }

    if (empty($this->assets)) {
      throw new MigrateSkipProcessException();
    }

    foreach ($this->assets as $asset) {
      try {
        $media_id = $this->imageOps->getAndSaveImageMedia($asset['asset_id'], $alt_text);
        if ($media_id === FALSE) {
          $migrate_executable->saveMessage(
            '[Listing Image] During import of "'
            . $sku . '" - Unable to load image "'
            . $asset['asset_id']
          );
        }
      }
      catch (ImageNotFoundOnFtpException $e) {
        $migrate_executable->saveMessage(
          '[Listing Image] During import of "'
          . $sku . '" - Unable to find the image on the FTP server for asset: "'
          . $asset['asset_id'] . '.jpg". '
          . $e->getMessage()
        );
      }
      catch (\Exception | FilesystemException $e) {
        $migrate_executable->saveMessage(
          '[Listing Image] During import of "'
          . $sku . '" - Unable to load the video. Error: ' . $e->getMessage()
        );
      }
    }

    if (!empty($media_id)) {
      return $media_id;
    }

    throw new MigrateSkipProcessException();
  }

  /**
   * Scans for and returns the primary image.
   *
   * @param object $asset_item
   *   The AssetCrossReference element.
   * @param string $image_type
   *   The image type identifier.
   */
  protected function scanForCategoryImage($asset_item, string $image_type): void {

    foreach ($asset_item as $asset_item) {
      $type = (string) $asset_item->attributes()->Type;
      if ($type == 'WebMenuImageSmall') {
        $assetId = (string) $asset_item->attributes()->AssetID;
        if (empty($assetId)) {
          return;
        }
        $media_id = ImageOperations::getImageMediaId($assetId);
        // If we find the file then we need to reference it in the return array.
        if (empty($media_id)) {
          $this->assets[] = [
            'imagetype' => $image_type,
            'asset_id' => $assetId,
          ];
          return;
        }
        $this->mediaId = $media_id;
      }
    }
  }

}
