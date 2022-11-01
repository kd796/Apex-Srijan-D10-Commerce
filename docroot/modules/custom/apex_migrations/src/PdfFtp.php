<?php

namespace Drupal\apex_migrations;

/**
 * PdfFtp class.
 *
 * This class goes out to the client's FTP server, validates that a PDF exists,
 * then downloads it for use in the import processes.
 */
class PdfFtp extends Ftp {

  /**
   * Gets an image from the FTP server or returns false if it doesn't exist.
   *
   * @param string $asset_id
   *   The asset ID used to build the image path.
   *
   * @return false|string
   *   Returns the image contents or FALSE.
   *
   * @throws \Drupal\apex_migrations\ImageNotFoundOnFtpException
   * @throws \League\Flysystem\FilesystemException
   */
  public function getPdf(string $asset_id): bool|string {
    $image_path = $this->buildPdfPath($asset_id);

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
  public function buildPdfPath(string $asset_id): string {
    return $this->assetDirectory . $asset_id . '.pdf';
  }

}
