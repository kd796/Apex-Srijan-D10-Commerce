<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a get_product_images plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: get_product_images
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "get_product_images"
 * )
 */
class GetProductImages extends ProcessPluginBase {

  protected $allowedTypes = [
    'Beauty-Glamour Image', 'Cutaway Image', 'Highlight Image',
    'In Package Image', 'In Use Image', 'Literature', 'Part Shot 1',
    'Part Shot 2', 'Part Shot 3', 'Part Shot 4', 'Part Shot 5',
    'Product Logo', 'Secondary Image', 'Warning Image', 'ICON'
  ];

  /**
   * Get all the asset images that match the allowed types.
   */
  protected function getAllowedTypesImages($elem) {
    $assets = [];

    // Get the images for all of the allowed types.
    foreach ($this->allowedTypes as $type) {
      $typeImages = $elem->xpath("/Product/AssetCrossReference[@Type='$type']");

      if (!empty($typeImages)) {
        foreach ($typeImages as $image_element) {
          $assetId = (string) $image_element->attributes()->AssetID;

          $assets[] = [
            'imagetype' => 'Product Level',
            'asset_id' => $assetId,
            'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
            'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
          ];
        }
      }
    }

    return $assets;
  }

  /**
   * Get the primary image.
   */
  protected function getPrimaryImage($elem, $sku) {
    /** @var \SimpleXMLElement $primary_image */
    $primary_image_element = $elem->xpath("/Product/AssetCrossReference[@Type='Primary Image']");

    if (is_array($primary_image_element)) {
      $primary_image_element = array_shift($primary_image_element);
    }

    $assetId = $primary_image_element->attributes()->AssetID;

    $primary_image = [
      'sku' => $sku,
      'imagetype' => 'Product Level',
      'asset_id' => $assetId,
      'drupal_file_path' => 'public://pim_images/' . $assetId . '.jpg',
      'remote_file_path' => 'http://www.imagesource.apextoolgroup.com/website/' . $assetId . '.jpg',
    ];

    return $primary_image;
  }

  /**
   * Get the video URLs.
   */
  protected function getVideoUrls($elem) {
    return $elem->xpath("/Product/Values/MultiValue[@AttributeID='ExternalVideoURL']/Value");
  }

  /**
   * Build a standard media asset array.
   */
  protected function buildMediaAsset($asset, $sku, MigrateExecutableInterface $migrate_executable) {
    $media_array = [];

    /*
     * @todo: Investigate to see if there is a way to check for the existing file before even sending a network request.
     *
     * Ideas:
     *  - Calculate the destination file name and check for that in media/files.
     *  - Store the origin file path somewhere and relate to imported file.
     */
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
          $media_array = [
            'media_id' => $media_id
          ];
        }
        else {
          $media = Media::create([
            'bundle'           => 'image',
            'uid'              => 1,
            'field_media_image' => [
              'target_id' => $file->id(),
            ],
          ]);

          $media->setName($asset['asset_id'])->setPublished(TRUE)->save();
          $media_array = [
            'media_id' => $media->id()
          ];
        }
      }
    }
    else {
      $migrate_executable->saveMessage(
        'During import of "'
        . $sku . '" - Unable to load image "'
        . $asset['remote_file_path']
        . '". Header response: "' . $headers_check . '"'
      );
    }

    return $media_array;
  }

  /**
   * Build a video asset array.
   */
  protected function buildVideoAsset($video, $sku, MigrateExecutableInterface $migrate_executable) {
    $media_array = [];

    // Process the video down to a normal URL, not a video series.
    $video_json = gearwrench_core_get_youtube_data($video);
    $clean_url = gearwrench_core_get_youtube_clean_url($video);

    if (!empty($video_json->title)) {
      $media = Media::create([
        'bundle'           => 'remote_video',
        'uid'              => 1,
        'field_media_video_embed_field' => [
          'value' => $clean_url,
        ],
      ]);

      $media->setName($video_json->title)->setPublished(TRUE)->save();
      $media_array = [
        'media_id' => $media->id()
      ];
    }
    else {
      $migrate_executable->saveMessage(
        'During import of "'
        . $sku . '" - Unable to retrieve YouTube video metadata for url: '
        . $clean_url
      );
    }

    return $media_array;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $media_ids = [];

    /** @var \SimpleXMLElement $productGroup */
    $productGroup = $value;

    if (!empty($productGroup)) {
      $sku = $row->getSourceIdValues()['remote_sku'];
      $primary_image_element = NULL;
      $assets = [];
      $videos = [];

      $matchingProducts = $productGroup->xpath("//Product[@ID='$sku']");

      if (!empty($matchingProducts)) {
        /** @var \SimpleXMLElement $product */
        $product = $matchingProducts[0];
      }

      if (!empty($product)) {
        $primary_image = $this->getPrimaryImage($product, $sku);
        $assets = $this->getAllowedTypesImages($product);
        $videos = $this->getVideoUrls($product);
      }

      if (empty($primary_image)) {
        $primary_image = $this->getPrimaryImage($productGroup, $sku);
      }

      if (empty($assets)) {
        $assets = $this->getAllowedTypesImages($productGroup);
      }

      if (empty($videos)) {
        $videos = $this->getVideoUrls($productGroup);
      }

      if (empty($primary_image)) {
        $migrate_executable->saveMessage(
          'While loading the primary image for "'
          . $sku . '" - Unable to find the primary image "'
        );
      }

      if (empty($assets)) {
        $migrate_executable->saveMessage(
          'While loading the product images for "'
          . $sku . '" - Unable to find the product images "'
        );
      }

      // Now plug in the Primary Image at the beginning of the array.
      $final_asset_list = [];

      // This final array will now allow the primary image to be the priority.
      if (!empty($primary_image)) {
        $final_asset_list[] = $primary_image;
      }

      foreach ($assets as $asset) {
        $final_asset_list[] = $asset;
      }

      // Prep Directory.
      $image_directory = 'public://pim_images/';
      \Drupal::service('file_system')->prepareDirectory($image_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      foreach ($final_asset_list as $asset) {
        try {
          $media_array = $this->buildMediaAsset($asset, $sku, $migrate_executable);

          if (!empty($media_array)) {
            $media_ids[] = $media_array;
          }
        }
        catch (\Exception $e) {
          $migrate_executable->saveMessage(
            'During import of "'
            . $sku . '" - There was a problem loading image "'
            . $asset['remote_file_path']
            . '". Error: ' . $e->getMessage()
          );
        }
      }

      // Take all the videos and create media items.
      if (!empty($videos)) {
        foreach ($videos as $video) {
          try {
            $media_array = $this->buildVideoAsset($video, $sku, $migrate_executable);

            if (!empty($media_array)) {
              $media_ids[] = $media_array;
            }
          }
          catch (\Exception $e) {
            $migrate_executable->saveMessage(
              'During import of "'
              . $sku . '" - Unable to load the video. Error: ' . $e->getMessage()
            );
          }
        }
      }
    }

    return $media_ids;
  }

}
