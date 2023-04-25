<?php

namespace Drupal\cleco_migrations;

/**
 * ImageFtp class.
 *
 * This class goes out to the client's FTP server,
 * validates that an image exists,
 * then downloads it for use in the import processes.
 */
class ImageFtp extends Ftp {

  /**
   * Gets an image from the FTP server or returns false if it doesn't exist.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   * @param string $extension
   *   The asset extension.
   *
   * @return false|string
   *   Returns the image contents or FALSE.
   *
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getImage(string $asset_id, string $extension = ''): bool|string {
    $image_path = $this->buildImagePath($asset_id, $extension);
    if ($this->checkFileExists($image_path)) {
      return $this->filesystem->read($image_path);
    }

    throw new ImageNotFoundOnFtpException('image path: ' . $image_path);
  }

  /**
   * Construct the image path to use.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   * @param string $extension
   *   The asset extension.
   *
   * @return string
   *   Returns the constructed image path.
   */
  public function buildImagePath(string $asset_id, string $extension = ''): string {
    $mapped_extension = $this->mapFileExtension($extension);
    return $this->assetDirectory . $asset_id . $mapped_extension;
  }

  /**
   * Construct the Pdf path to use.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   * @param string $extension
   *   The asset extension.
   *
   * @return string
   *   Returns the constructed image path.
   */
  public function buildPdfPath(string $asset_id, string $extension = ''): string {
    $mapped_extension = $this->mapFileExtension($extension);
    return $this->assetDirectory . $asset_id . $mapped_extension;
  }

  /**
   * Gets an image from the FTP server or returns false if it doesn't exist.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   * @param string $extension
   *   The asset extension.
   *
   * @return false|string
   *   Returns the image contents or FALSE.
   *
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getPdf(string $asset_id, string $extension = ''): bool|string {
    $pdf_path = $this->buildPdfPath($asset_id, $extension);

    if ($this->checkFileExists($pdf_path)) {
      return $this->filesystem->read($pdf_path);
    }

    throw new ImageNotFoundOnFtpException('Product Download File path: ' . $pdf_path);
  }

  /**
   * Get file extension for the asset download.
   *
   * @param string $extension
   *   Asset extension.
   *
   * @return string
   *   Returns the constructed file extension.
   */
  public function mapFileExtension($extension = '') {
    $extension = strtolower($extension);
    $file_extension = $extension;
    if (empty($extension)) {
      $file_extension = 'jpg';
    }
    $list = [
      'eps' => 'jpg',
      'png' => 'jpg',
      'gif' => 'jpg',
      'tif' => 'jpg',
      'pdf' => 'pdf',
    ];
    if (isset($list[$extension])) {
      $file_extension = $list[$extension];
    }
    $file_extension = "." . $file_extension;
    return $file_extension;
  }

}
