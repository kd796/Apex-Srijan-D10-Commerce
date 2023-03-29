<?php

namespace Drupal\cleco_migrations;

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
   * @var \Drupal\cleco_migrations\ImageFtp
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
   * The local path for storing pdf.
   *
   * @var string
   */
  public static string $localPdfDirectory = 'public://pim_pdfs/';

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
    echo "\nCleco class:: imageoperations...buildLocalAssetImagePath...\n";
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
    echo "\nCleco class:: imageoperations...getImageMediaId...\n";
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
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
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
            'alt' => 'Image of ' . $alt_text,
          ],
        ]);

        $media->setName($asset_id)->setPublished(TRUE)->save();
        $media_id = $media->id();
      }

      return $media_id;
    }

    return FALSE;
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
  public static function buildLocalAssetPdfPath(string $asset_id) {
    echo "\nCleco class:: imageoperations...buildLocalAssetPdfPath...\n";
    return self::$localPdfDirectory . $asset_id . '.pdf';
  }

  /**
   * Gets the asset image from FTP and then saves it to Drupal.
   *
   * @param string $asset_id
   *   The Asset ID for the image.
   * @param string $alt_text
   *   The alt text for the image. (Optional)
   * @param string $lang_code
   *   The language code. (Optional)
   *
   * @return false|string
   *   Returns the media ID or FALSE.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getAndSaveImage(string $asset_id, string $alt_text = '', $lang_code = "en"): mixed {
    $asset_id_lang_code = $asset_id . $lang_code;
    $file_data = $this->ftp->getImage($asset_id);

    if ($file_data !== FALSE) {
      $drupal_file_path = self::buildLocalAssetImagePath($asset_id_lang_code);
      $file = ImageOperations::fileSaveData(
        $file_data,
        $drupal_file_path,
        FileSystemInterface::EXISTS_REPLACE
      );
    }
    if (!empty($file)) {
      $fid = $file->id();
    }
    if (!empty($fid)) {
      return $fid;
    }
    echo "\nReturn Fid:: $fid \n";
    return FALSE;
  }

  /**
   * Gets the asset pdf from FTP and then saves it to Drupal.
   *
   * @param string $asset_id
   *   The Asset ID for the image.
   * @param string $alt_text
   *   The alt text for the image. (Optional)
   * @param string $lang_code
   *   The language code. (Optional)
   *
   * @return false|string
   *   Returns the media ID or FALSE.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getAndSavePdf(string $asset_id, string $alt_text = '', $lang_code = 'en'): mixed {
    $asset_id_lang_code = $asset_id . $lang_code;
    $file_data = $this->ftp->getPdf($asset_id);

    if ($file_data !== FALSE) {
      $drupal_file_path = self::buildLocalAssetPdfPath($asset_id_lang_code);
      $file = ImageOperations::fileSaveData(
        $file_data,
        $drupal_file_path,
        FileSystemInterface::EXISTS_REPLACE
      );
    }
    if (!empty($file)) {
      $fid = $file->id();
    }
    if (!empty($fid)) {
      return $fid;
    }
    return FALSE;
  }

  /**
   * Gets the asset image from FTP and then saves it to Drupal.
   *
   * @param string $asset_id
   *   The Asset ID for the image.
   * @param string $alt_text
   *   The alt text for the image. (Optional)
   * @param string $lang_code
   *   The language code. (Optional)
   *
   * @return false|string
   *   Returns the media ID or FALSE.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getAndSaveDownloadsImageMedia(string $asset_id, string $alt_text = '', $lang_code = 'en'): mixed {
    $file_data = $this->ftp->getImage($asset_id);
    $asset_id_lang_code = $asset_id . $lang_code;

    if ($file_data !== FALSE) {
      $drupal_file_path = self::buildLocalAssetImagePath($asset_id_lang_code);
      $file = ImageOperations::fileSaveData(
        $file_data,
        $drupal_file_path,
        FileSystemInterface::EXISTS_REPLACE
      );

      // See if there's a media item we can use already.
      // To be removed and get Mapping from map table.
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
            'alt' => 'Image of ' . $alt_text,
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
