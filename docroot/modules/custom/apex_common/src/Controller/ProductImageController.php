<?php

namespace Drupal\apex_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Controller routines for page example routes.
 */
class ProductImageController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'apex_common';
  }

  /**
   * Sets up the download of product images as a zip file.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The product node.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The download response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function downloadImages(NodeInterface $node) {
    // We have the product preloaded, so now we get the images.
    // Build the zip file here.
    $zip_directory = 'public://product_image_zip_files/';
    $base_filename = $node->id() . '-product-images.zip';
    $zip_filename = $zip_directory . $base_filename;

    // First check the cache for a previous zip file.
    /** @var \Drupal\file\Entity\File $zip_file */
    $zip_file = _apex_migrations_load_file_by_uri($zip_filename);
    $filesize = 0;

    if (is_object($zip_file)) {
      $filesize = $zip_file->getSize();
    }

    // If not found, create a new zip file.
    if (empty($zip_file) || empty($filesize)) {
      // Now get all the images.
      $images = $this->getImages($node);

      // Prep Directory.
      \Drupal::service('file_system')->prepareDirectory($zip_directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      // Let's make a new zip file.
      $realpath = $this->createZip($images, $zip_filename, $zip_file);
    }
    else {
      /** @var \Drupal\Core\File\FileSystem $filesystem */
      $filesystem = \Drupal::service('file_system');
      $realpath = $filesystem->realpath($zip_filename);
    }

    // Return the zip file.
    return $this->sendFile($realpath, $base_filename);
  }

  /**
   * Send the zip file to the browser.
   *
   * @param string $realpath
   *   The real path to the zip file.
   * @param string $base_filename
   *   The base filename.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The download response.
   */
  protected function sendFile(string $realpath, $base_filename = '') {
    $contents = file_get_contents($realpath);

    $response = new Response($contents);
    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $base_filename);

    $response->headers->set('Content-type', 'application/zip');
    $response->headers->set('Content-Disposition', $disposition);
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    $response->headers->set('Content-length', strlen($contents));

    return $response;
  }

  /**
   * Create the zip file.
   *
   * @param array $images
   *   An array of image file paths.
   * @param string $zip_filename
   *   The full path to the zip file.
   * @param \Drupal\file\Entity\File|null $zip_file
   *   The file entity for the zip file.
   *
   * @return false|string
   *   The real path to the zip file.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createZip(array $images, string $zip_filename, File $zip_file = NULL) {
    // First we create the file in the file system.
    if (empty($zip_file)) {
      $zip_file = _apex_migrations_file_save_data('', $zip_filename);
    }

    /** @var \Drupal\Core\File\FileSystem $filesystem */
    $filesystem = \Drupal::service('file_system');
    $realpath = $filesystem->realpath($zip_filename);

    $zip = new \ZipArchive();
    $zip->open($realpath);

    foreach ($images as $image) {
      $pieces = explode('/', $image);
      $simple_name = array_pop($pieces);
      $zip->addFile($image, $simple_name);
    }

    // Close the zip file so that we can save it and read the filesize.
    $zip->close();

    $filesize = filesize($realpath);
    $zip_file->setSize($filesize);
    $zip_file->save();

    return $realpath;
  }

  /**
   * Gets all of the images for a product.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The product node.
   *
   * @return array
   *   An array of image file paths.
   */
  protected function getImages(NodeInterface $node) {
    $images = [];
    $product_images = $node->get('field_product_images')->getValue();
    $fileSystem = \Drupal::service('file_system');

    foreach ($product_images as $image) {
      $media = Media::load($image['target_id']);

      if (!empty($media->field_media_image) && !empty($media->field_media_image->target_id)) {
        $fid = $media->field_media_image->target_id;
        $file = File::load($fid);
        $file_uri = $file->getFileUri();
        $file_realpath = $fileSystem->realpath($file_uri);

        $images[] = $file_realpath;
      }
    }

    return $images;
  }

}
