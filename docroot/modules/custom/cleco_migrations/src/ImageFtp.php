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
   *
   * @return false|string
   *   Returns the image contents or FALSE.
   *
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getImage(string $asset_id): bool|string {
    echo "\nCleco class:: ImageFtp...getImage...\n";
    $image_path = $this->buildImagePath($asset_id);

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
   *
   * @return string
   *   Returns the constructed image path.
   */
  public function buildImagePath(string $asset_id): string {
    return $this->assetDirectory . $asset_id . '.jpg';
  }

  /**
   * Construct the Pdf path to use.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   *
   * @return string
   *   Returns the constructed image path.
   */
  public function buildPdfPath(string $asset_id): string {
    return $this->assetDirectory . $asset_id . '.pdf';
  }

  /**
   * Gets an image from the FTP server or returns false if it doesn't exist.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   *
   * @return false|string
   *   Returns the image contents or FALSE.
   *
   * @throws \Drupal\cleco_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getPdf(string $asset_id): bool|string {
    $pdf_path = $this->buildPdfPath($asset_id);

    if ($this->checkFileExists($pdf_path)) {
      return $this->filesystem->read($pdf_path);
    }

    throw new ImageNotFoundOnFtpException('pdf path: ' . $pdf_path);
  }

}
