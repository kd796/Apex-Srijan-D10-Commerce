<?php

namespace Drupal\campbell_common\Controller;

use Drupal\apex_migrations\FileOperations;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Controller routines for page example routes.
 */
class ProductImageController extends ControllerBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ProductImageController object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(FileSystemInterface $fileSystem, EntityTypeManagerInterface $entityTypeManager) {
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'campbell_common';
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

    // Check if the file already exists.
    if (file_exists($zip_filename)) {
      // Delete the existing zip file to prevent adding multiple files.
      $zip_file = FileOperations::loadFileByUri($zip_filename);
      if ($zip_file) {
        $zip_file->delete();
      }
    }

    // Create a new zip file.
    $new_zip_file = $this->createZip($this->getImages($node), $zip_filename);

    // Return the zip file.
    return $this->sendFile($new_zip_file, $base_filename);
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
    ob_clean();

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
      $zip_file = FileOperations::fileSaveData('', $zip_filename);
    }

    $realpath = $this->fileSystem->realpath($zip_filename);

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

    foreach ($product_images as $image) {
      $media = $this->entityTypeManager->getStorage('media')->load($image['target_id']);

      if (!empty($media->field_media_image) && !empty($media->field_media_image->target_id)) {
        $fid = $media->field_media_image->target_id;
        $file = $this->entityTypeManager->getStorage('file')->load($fid);
        $file_uri = $file->getFileUri();
        $file_realpath = $this->fileSystem->realpath($file_uri);

        $images[] = $file_realpath;
      }
    }

    return $images;
  }

}
