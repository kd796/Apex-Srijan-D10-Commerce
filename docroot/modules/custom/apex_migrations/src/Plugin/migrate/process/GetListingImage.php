<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageNotFoundOnFtpException;
use Drupal\apex_migrations\ImageOperations;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use League\Flysystem\FilesystemException;

/**
 * Provides an apex_get_listing_image plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: apex_get_listing_image
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_listing_image"
 * )
 */
class GetListingImage extends ProcessPluginBase {

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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->imageOps = new ImageOperations();

    // Prep Directory.
    ImageOperations::prepImageDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $assets = [];
    $media_id = NULL;
    $alt_text = NULL;
    $sku = NULL;
    $this->mediaId = NULL;
    $this->assets = NULL;

    // #value is the Product.
    if (!empty($value)) {
      $sku = $row->getSourceIdValues()['remote_sku'];
      $alt_text = $value->Name;

      $asset_cross_reference = $value->xpath('parent::Product/AssetCrossReference');
      $this->scanForPrimaryImage($asset_cross_reference, 'Product Level');

      // If a media ID was returned then use that.
      if (!empty($this->mediaId)) {
        return $this->mediaId;
      }

      if (empty($this->assets)) {
        $product = $value->xpath('parent::Product');

        if (!empty($product[0])) {
          $product = $product[0];
          $parentProductAssetCrossReference = $product->xpath('parent::Product/AssetCrossReference');

          if (!empty($parentProductAssetCrossReference)) {
            $this->scanForPrimaryImage($parentProductAssetCrossReference, 'SKU Group Level');

            // If a media ID was returned then use that.
            if (!empty($this->mediaId)) {
              return $this->mediaId;
            }
          }
        }
      }

      if (!empty($this->assets)) {
        $store = \Drupal::service('tempstore.private')->get('apex_migrations');

        // Do a preliminary check to see if the image server is reachable.
        if ($store->get('image_server_available') === TRUE) {
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
        }
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
   * @param array $asset_list
   *   The array of found AssetCrossReference elements.
   * @param string $image_type
   *   The image type identifier.
   */
  protected function scanForPrimaryImage(array $asset_list, string $image_type): void {
    if (!empty($asset_list)) {
      foreach ($asset_list as $asset_item) {
        $type = (string) $asset_item->attributes()->Type;

        if ($type == 'Primary Image') {
          $assetId = (string) $asset_item->attributes()->AssetID;

          if (!empty($assetId)) {
            $media_id = ImageOperations::getImageMediaId($assetId);

            // If we find the file then we need to reference it in the return array.
            if (!empty($media_id)) {
              $this->mediaId = $media_id;
            }
            else {
              $this->assets[] = [
                'imagetype' => $image_type,
                'asset_id' => $assetId,
              ];
            }
          }
        }
      }
    }
  }

}
