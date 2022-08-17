<?php

namespace Drupal\apex_migrations;

use Drupal\Core\Config\Config;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;

/**
 * ImageOperations class.
 *
 * Performs ATG product image-specific operations.
 */
class ImageOperations extends FileOperations {

  /**
   * The Image FTP class.
   *
   * @var \Drupal\apex_migrations\ImageFtp
   */
  protected ImageFtp $ftp;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->ftp = new ImageFtp();
  }

  /**
   * The local path for storing images.
   *
   * @var string
   */
  public static string $localImageDirectory = 'public://pim_images/';

  /**
   * Preps the image directory.
   */
  public static function prepImageDirectory(): void {
    \Drupal::service('file_system')->prepareDirectory(
      self::$localImageDirectory,
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );
  }

  /**
   * Just builds a simple path.
   *
   * @param string $asset_id
   *   The Asset ID for the image.
   *
   * @return string
   *   The path to store/find these images locally.
   */
  public static function buildLocalAssetImagePath(string $asset_id) {
    return self::$localImageDirectory . $asset_id . '.jpg';
  }

  /**
   * Takes and Asset ID, builds the path, then gets the Drupal Media ID.
   *
   * @param string $asset_id
   *   The Asset ID for the image.
   *
   * @return int|string|null
   *   The media ID.
   *
   * @NOTE: May not be needed.
   */
  public static function getImageMediaId(string $asset_id) {
    $drupal_file_path = self::buildLocalAssetImagePath($asset_id);
    return self::getMediaId($drupal_file_path);
  }

  /**
   * Gets the asset image from FTP and then saves it to Drupal.
   *
   * @param string $asset_id
   *   The Asset ID for the image.
   * @param string $alt_text
   *   The alt text for the image. (Optional)
   *
   * @return false|string
   *   Returns the media ID or FALSE.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\apex_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getAndSaveImageMedia(string $asset_id, string $alt_text = ''): mixed {
    $file_data = $this->ftp->getImage($asset_id);

    if ($file_data !== FALSE) {
      $drupal_file_path = self::buildLocalAssetImagePath($asset_id);
      $file = ImageOperations::fileSaveData(
        $file_data,
        $drupal_file_path,
        FileSystemInterface::EXISTS_REPLACE
      );

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

        $media->setName($asset_id)->setPublished(TRUE)->save();
        $media_id = $media->id();
      }

      return $media_id;
    }

    return FALSE;
  }

}
