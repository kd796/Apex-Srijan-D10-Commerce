<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageNotFoundOnFtpException;
use Drupal\apex_migrations\ImageOperations;
use Drupal\migrate\MigrateExecutableInterface;
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

    if (!empty($value)) {
      $sku_group = $value;
      $alt_text = $value->Name;
      $sku = $row->getSourceIdValues()['remote_sku'];

      foreach ($value->children() as $child) {
        if ($child->getName() === 'Product' && (string) $child->attributes()->ID === $sku) {
          foreach ($child->children() as $item) {
            if ($item->getName() === 'AssetCrossReference' && ((string) $item->attributes()->Type === 'Primary Image')) {
              $assetId = ImageOperations::cleanAssetId((string) $item->attributes()->AssetID);

              if (!empty($assetId)) {
                $media_id = ImageOperations::getImageMediaId($assetId);

                // If we find the file then we need to reference it in the return array.
                if (!empty($media_id)) {
                  return $media_id;
                }
                else {
                  $assets[] = [
                    'sku' => $sku,
                    'imagetype' => 'Product Level',
                    'asset_id' => $assetId,
                  ];
                }
              }
              else {
                $migrate_executable->saveMessage(
                  '[Listing Image] Product Level Asset ID empty for "'
                  . $sku
                );
              }
            }
          }
        }
      }

      if (empty($assets)) {
        foreach ($sku_group->children() as $child) {
          if ($child->getName() === 'AssetCrossReference' && (string) $child->attributes()->Type === 'Primary Image') {
            $assetId = ImageOperations::cleanAssetId((string) $child->attributes()->AssetID);

            if (!empty($assetId)) {
              $media_id = ImageOperations::getImageMediaId($assetId);

              // If we find the file then we need to reference it in the return array.
              if (!empty($media_id)) {
                return $media_id;
              }
              else {
                $assets[] = [
                  'imagetype' => 'SKU Group Level',
                  'asset_id' => $assetId,
                ];
              }
            }
            else {
              $migrate_executable->saveMessage(
                '[Listing Image] SKU Group Level Asset ID empty for "'
                . $sku
              );
            }
          }
        }
      }

      if (!empty($assets)) {
        $store = \Drupal::service('tempstore.private')->get('apex_migrations');

        // Do a preliminary check to see if the image server is reachable.
        if ($store->get('image_server_available') === TRUE) {
          foreach ($assets as $asset) {
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

    return $media_id;
  }

}
