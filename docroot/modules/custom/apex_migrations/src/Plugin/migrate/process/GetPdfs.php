<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\PdfNotFoundOnFtpException;
use Drupal\apex_migrations\PdfOperations;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use League\Flysystem\FilesystemException;

/**
 * Provides a apex_get_pdfs plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: apex_get_pdfs
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_pdfs"
 * )
 */
class GetPdfs extends ProcessPluginBase {

  /**
   * The PDF operations class.
   *
   * @var \Drupal\apex_migrations\PdfOperations
   */
  protected PdfOperations $pdfOps;

  /**
   * The list of types of Asset Cross References we use as PDFs.
   *
   * @var array|string[]
   */
  protected static array $allowedTypes = [
    'Owners Manual', 'Flyer/Brochure', 'MSDS'
  ];

  /**
   * The media IDs for existing media elements.
   *
   * @var array
   */
  protected array $mediaIds = [];

  /**
   * The list of assets to download.
   *
   * @var array
   */
  protected array $assets = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pdfOps = new PdfOperations();

    // Prep Directory.
    PdfOperations::prepPdfDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->mediaIds = [];
    $this->assets = [];
    $sku = NULL;
    $alt_text = '';

    // $value is the Product.
    if (!empty($value)) {
      $product = $value->xpath('parent::Product');

      if (!empty($product[0])) {
        $product = $product[0];
        $alt_text = $product->Name;
      }

      $asset_cross_reference = $value->xpath('parent::Product/AssetCrossReference');

      // Are there product level PDFs?
      $sku = $row->getSourceIdValues()['remote_sku'];
      $this->scanElementForPdfs($asset_cross_reference);

      if (!empty($product) && empty($this->assets) && empty($this->mediaIds)) {
        $parentProductAssets = $product->xpath('parent::Product/AssetCrossReference');

        if (!empty($parentProductAssets)) {
          // If we didn't find anything at the product level then we scan at the parent level.
          $this->scanElementForPdfs($parentProductAssets, 'SKU Group Level');
        }
      }

      // Now plug in the PDF at the beginning of the array.
      $final_asset_list = [];

      foreach ($this->assets as $asset) {
        $final_asset_list[] = $asset;
      }

      // If we have no assets to download and media IDs available then return.
      if (empty($final_asset_list) && !empty($this->mediaIds)) {
        return $this->mediaIds;
      }

      $store = \Drupal::service('tempstore.private')->get('apex_migrations');

      if (!empty($final_asset_list) && $store->get('image_server_available') === TRUE) {
        foreach ($final_asset_list as $asset) {
          try {
            $media_id = $this->pdfOps->getAndSavePdfMedia($asset['asset_id'], $alt_text);

            if ($media_id === FALSE) {
              $migrate_executable->saveMessage(
                '[Product PDFs] During import of "'
                . $sku . '" - Unable to load PDF "'
                . $asset['asset_id'] . '.pdf"'
              );
            }
            else {
              $this->mediaIds[] = [
                'media_id' => $media_id
              ];
            }
          }
          catch (PdfNotFoundOnFtpException $e) {
            $migrate_executable->saveMessage(
              '[Product PDFs] During import of "'
              . $sku . '" - Unable to find the PDF on the FTP server for asset: "'
              . $asset['asset_id'] . '.pdf". '
              . $e->getMessage()
            );
          }
          catch (\Exception | FilesystemException $e) {
            $migrate_executable->saveMessage(
              '[Product PDFs] During import of "'
              . $sku . '" - There was a problem loading PDF "'
              . $asset['asset_id'] . '.pdf". Error: '
              . $e->getMessage()
            );
          }
        }
      }
    }

    if (!empty($this->mediaIds)) {
      return $this->mediaIds;
    }

    throw new MigrateSkipProcessException();
  }

  /**
   * Scans a given XML element for child elements that contain PDFs.
   *
   * @param \SimpleXMLElement|mixed $element
   *   The SimpleXMLElement we are processing.
   * @param string $level
   *   The level we want to indicate for reporting purposes.
   */
  private function scanElementForPdfs(mixed $element, string $level = 'Product Level') {
    foreach ($element as $item) {
      $attributeType = (string) $item->attributes()->Type;
      $assetId = (string) $item->attributes()->AssetID;

      if (!empty($assetId)) {
        $media_id = PdfOperations::getPdfMediaId($assetId);

        // If we find the file then we need to reference it in the return array.
        if (!empty($media_id)) {
          $this->mediaIds[] = [
            'media_id' => $media_id
          ];
        }
        elseif (in_array($attributeType, self::$allowedTypes)) {
          $this->assets[] = [
            'filetype' => $level,
            'asset_id' => $assetId,
          ];
        }
      }
    }
  }

}
