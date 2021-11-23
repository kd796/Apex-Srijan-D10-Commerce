<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a get_listing_image plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: get_listing_image
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "get_listing_image"
 * )
 */
class GetListingImage extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $media_id = NULL;

    /** @var \SimpleXMLElement $productGroup */
    $productGroup = $value;

    if (!empty($productGroup)) {
      $alt_text = $productGroup->Name[0];
      $sku = $row->getSourceIdValues()['remote_sku'];
      $primary_image = NULL;
      $asset = [];
      $matchingProducts = $productGroup->xpath("//Product[@ID='$sku']");

      if (!empty($matchingProducts)) {
        /** @var \SimpleXMLElement $product */
        $product = $matchingProducts[0];
      }

      if (!empty($product)) {
        /** @var \SimpleXMLElement $primary_image */
        $primary_image = $product->xpath("/Product/AssetCrossReference[@Type='Primary Image']");

        if (is_array($primary_image)) {
          $primary_image = array_shift($primary_image);
        }
      }

      // If the current product doesn't have it, then maybe the product group does.
      if (empty($primary_image)) {
        $primary_image = $productGroup->xpath("/Product/AssetCrossReference[@Type='Primary Image']");

        // The first element should be the primary image for the product group.
        if (is_array($primary_image)) {
          $primary_image = array_shift($primary_image);
        }
      }

      if (!empty($primary_image)) {
        $assetId = urlencode($primary_image->attributes()->AssetID);
        $asset = [
          'sku' => $sku,
          'imagetype' => 'Product Level',
          'asset_id' => $assetId,
          'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
          'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
        ];
      }
      else {
        $migrate_executable->saveMessage(
          'While loading the primary image for "'
          . $sku . '" - Unable to find the primary image "'
        );
      }

      // Prep Directory.
      $image_directory = 'public://pim_images/';
      \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      try {
        $headers_array = @get_headers($asset['remote_file_path']);
        $headers_check = $headers_array[0];

        if (strpos($headers_check, "200")) {
          $file_data = file_get_contents($asset['remote_file_path']);

          if ($file_data) {
            $file = file_save_data($file_data, $asset['drupal_file_path'], FileSystemInterface::EXISTS_REPLACE);

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
        }
        else {
          $migrate_executable->saveMessage(
            'During import of "'
            . $sku . '" - Unable to load the primary image URL: "'
            . $asset['remote_file_path']
            . '". Header response: "' . $headers_check . '"'
          );
        }
      }
      catch (\Exception $e) {
        $migrate_executable->saveMessage(
          'During import of "'
          . $sku . '" - Unable to load the primary image. Error: ' . $e->getMessage()
        );
      }
    }

    return $media_id;
  }

}
