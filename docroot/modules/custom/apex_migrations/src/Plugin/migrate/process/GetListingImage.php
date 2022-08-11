<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\apex_migrations\ImageFtp;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
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
   * The Image FTP class.
   *
   * @var \Drupal\apex_migrations\ImageFtp
   */
  protected ImageFtp $ftp;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->ftp = new ImageFtp();
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
              $assetId = apex_migrations_clean_asset_id((string) $item->attributes()->AssetID);

              if (!empty($assetId)) {
                $drupal_file_path = 'public://pim_images/' . $assetId . '.jpg';
                $media_id = _apex_migrations_get_file_media_id($drupal_file_path);

                // If we find the file then we need to reference it in the return array.
                if (!empty($media_id)) {
                  return $media_id;
                }
                else {
                  $assets[] = [
                    'sku' => $sku,
                    'imagetype' => 'Product Level',
                    'asset_id' => $assetId,
                    'drupal_file_path' => $drupal_file_path,
                    'remote_file_path' => $assetId . '.jpg',
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
            $assetId = apex_migrations_clean_asset_id((string) $child->attributes()->AssetID);

            if (!empty($assetId)) {
              $drupal_file_path = 'public://pim_images/' . $assetId . '.jpg';
              $media_id = _apex_migrations_get_file_media_id($drupal_file_path);

              // If we find the file then we need to reference it in the return array.
              if (!empty($media_id)) {
                return $media_id;
              }
              else {
                $assets[] = [
                  'imagetype' => 'SKU Group Level',
                  'asset_id' => $assetId,
                  'drupal_file_path' => $drupal_file_path,
                  'remote_file_path' => $assetId . '.jpg',
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

      // Prep Directory.
      $image_directory = 'public://pim_images/';
      \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      if (!empty($assets)) {
        $store = \Drupal::service('tempstore.private')->get('apex_migrations');

        // Do a preliminary check to see if the image server is reachable.
        if ($store->get('image_server_available') === TRUE) {
          foreach ($assets as $asset) {
            try {
              /*
               * @todo I should add an exception to be thrown when it cannot find
               *       the file. Then log the fact in the migration logger.
               *       (maybe do this in the ImageFTP class to simplify).
               */
              $file_data = $this->ftp->getImage($asset['remote_file_path']);

              if ($file_data !== FALSE) {
                /*
                 * @todo: Can all or most of this be simplified and pushed into
                 *        the ImageFtp class? An all-in-one solution here? Maybe
                 *        even feed it all of the IDs to go grab and loop in there.
                 */
                $file = _apex_migrations_file_save_data($file_data, $asset['drupal_file_path'], FileSystemInterface::EXISTS_REPLACE);

                // See if there's a media item we can use already.
                $usage = \Drupal::service('file.usage')->listUsage($file);

                if (count($usage) > 0 && !empty($usage['file']['media'])) {
                  $media_id = array_key_first($usage['file']['media']);
                }
                else {
                  $media = Media::create([
                    'bundle'           => 'image',
                    'uid'              => 1,
                    'field_media_image' => [
                      'target_id' => $file->id(),
                      'alt' => 'Image of ' . $alt_text
                    ],
                  ]);

                  $media->setName($asset['asset_id'])->setPublished(TRUE)->save();
                  $media_id = $media->id();
                }
              }
              else {
                $migrate_executable->saveMessage(
                  '[Listing Image] During import of "'
                  . $sku . '" - Unable to load image "'
                  . $asset['remote_file_path']
                );
              }
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
