<?php

namespace Drupal\apex_migrations;

use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;

/**
 * PdfOperations class.
 *
 * Performs ATG product pdf-specific operations.
 */
class PdfOperations extends FileOperations {

  /**
   * The Image FTP class.
   *
   * @var \Drupal\apex_migrations\PdfFtp
   */
  protected PdfFtp $ftp;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->ftp = new PdfFtp();
  }

  /**
   * The local path for storing images.
   *
   * @var string
   */
  public static string $localPdfDirectory = 'public://pim_pdfs/';

  /**
   * Preps the image directory.
   */
  public static function prepPdfDirectory(): void {
    \Drupal::service('file_system')->prepareDirectory(
      self::$localPdfDirectory,
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
  public static function buildLocalAssetPdfPath(string $asset_id) {
    return self::$localPdfDirectory . $asset_id . '.pdf';
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
  public static function getPdfMediaId(string $asset_id) {
    $drupal_file_path = self::buildLocalAssetPdfPath($asset_id);
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
  public function getAndSavePdfMedia(string $asset_id, string $alt_text = ''): mixed {
    $file_data = $this->ftp->getPdf($asset_id);

    if ($file_data !== FALSE) {
      $drupal_file_path = self::buildLocalAssetPdfPath($asset_id);
      $file = PdfOperations::fileSaveData(
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
          'bundle'           => 'file',
          'uid'              => 1,
          'field_media_file' => [
            'target_id' => $file->id(),
            'alt' => 'PDF of ' . $alt_text
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
