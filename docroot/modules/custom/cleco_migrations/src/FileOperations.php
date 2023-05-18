<?php

namespace Drupal\cleco_migrations;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;

/**
 * FileOperations class.
 *
 * Performs common file operations we use in multiple places.
 * Including URL calls.
 */
class FileOperations {

  /**
   * Find the media file for a given file.
   *
   * @param string $uri
   *   The URI of the file to load.
   *
   * @return int|string|void|null
   *   The media file ID. Or NULL if no media file is found.
   */
  public static function getMediaId(string $uri) {
    $file = self::loadFileByUri($uri);

    // See if there's a media item we can use already.
    if (!empty($file)) {
      /** @var \Drupal\file\FileUsage\DatabaseFileUsageBackend $file_usage */
      $file_usage = \Drupal::service('file.usage');
      $usage = $file_usage->listUsage($file);

      if (count($usage) > 0 && !empty($usage['file']['media'])) {
        return array_key_first($usage['file']['media']);
      }
    }

    return NULL;
  }

  /**
   * Load a file by its URI.
   *
   * @param string $uri
   *   The URI of the file to load.
   *
   * @return \Drupal\file\FileInterface|null
   *   The file object, or NULL if the file could not be loaded.
   */
  public static function loadFileByUri(string $uri): ?FileInterface {
    /** @var \Drupal\file\FileRepository $fileRepository */
    $fileRepository = \Drupal::service('file.repository');
    return $fileRepository->loadByUri($uri);
  }

  /**
   * Saves a file to the specified destination and creates a database entry.
   *
   * This is copied from Drupal 8's file_save_data() function since they have
   * deprecated the file_save_data() function even though it is a more robust
   * usage of the file repository service.
   *
   * @param string $data
   *   A string containing the contents of the file.
   * @param string|null $destination
   *   (optional) A string containing the destination URI. This must be a stream
   *   wrapper URI. If no value or NULL is provided, a randomized name will be
   *   generated and the file will be saved using Drupal's default files scheme,
   *   usually "public://".
   * @param int $replace
   *   (optional) The replace behavior when the destination file already exists.
   *   Possible values include:
   *   - FileSystemInterface::EXISTS_REPLACE: Replace the existing file. If a
   *     managed file with the destination name exists, then its database entry
   *     will be updated. If no database entry is found, then a new one will be
   *     created.
   *   - FileSystemInterface::EXISTS_RENAME: (default) Append
   *     _{incrementing number} until the filename is unique.
   *   - FileSystemInterface::EXISTS_ERROR: Do nothing and return FALSE.
   *
   * @return \Drupal\file\FileInterface|false
   *   A file entity, or FALSE on error.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown when there is an error updating the file storage.
   *
   * @see https://www.drupal.org/node/3223520
   * @see \Drupal\Core\File\FileSystemInterface::saveData()
   */
  public static function fileSaveData(string $data, string $destination = NULL, int $replace = FileSystemInterface::EXISTS_RENAME): bool|FileInterface {
    if (empty($destination)) {
      $destination = \Drupal::config('system.file')->get('default_scheme') . '://';
    }

    /** @var \Drupal\file\FileRepository $fileRepository */
    $fileRepository = \Drupal::service('file.repository');

    try {
      return $fileRepository->writeData($data, $destination, $replace);
    }
    catch (InvalidStreamWrapperException $e) {
      \Drupal::logger('file')->notice('The data could not be saved because the destination %destination is invalid. This may be caused by improper use of file_save_data() or a missing stream wrapper.', ['%destination' => $destination]);

      \Drupal::messenger()->addError(t('The data could not be saved because the destination is invalid. More information is available in the system log.'));

      return FALSE;
    }
    catch (FileException $e) {
      return FALSE;
    }
  }

  /**
   * Takes a YouTube video URL and gets a clean URL and retrieves video data.
   *
   * Source: Google JSON API for title, thumbnail, and other video info.
   *
   * @param string $video_url
   *   The YouTube URL.
   *
   * @return mixed
   *   The resulting data from YouTube.
   *
   * @throws \Exception
   */
  public static function getYoutubeData(string $video_url): mixed {
    $clean_url = self::getYoutubeCleanUrl($video_url);
    $result = new \stdClass();

    if (!empty($clean_url)) {
      $youtube_api_url = "https://www.youtube.com/oembed?format=json&url=$clean_url";
      $good_response = self::urlHasGoodResponse($youtube_api_url);

      if ($good_response !== TRUE) {
        throw new \Exception(
          'Unable to connect to remote URL: '
          . $clean_url . '. Header response: '
          . $good_response
        );
      }

      try {
        $data = file_get_contents($youtube_api_url);

        if (!empty($data)) {
          $result = json_decode($data);
        }
      }
      catch (\Exception $e) {
        throw new \Exception('Error loading Youtube URL. Message: ' . $e->getMessage());
      }
    }

    $result->clean_url = $clean_url;
    return $result;
  }

  /**
   * Gets a clean URL for a YouTube video.
   *
   * @param string $video_url
   *   The YouTube URL.
   *
   * @return string
   *   The cleaned URL.
   */
  public static function getYoutubeCleanUrl(string $video_url): string {
    $video_url = trim($video_url);

    // Now get a clean version of the video URL.
    $match = [];
    $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

    preg_match($pattern, $video_url, $match);

    if (!empty($match[1])) {
      $youtube_id = $match[1];
      return 'https://www.youtube.com/watch?v=' . $youtube_id;
    }

    return '';
  }

  /**
   * Tell you whether the remote URL gives you a 200 OK or a different response.
   *
   * @param string $url
   *   The URL to test.
   *
   * @return bool|mixed
   *   TRUE or FALSE.
   */
  public static function urlHasGoodResponse(string $url): mixed {
    $headers_array = @get_headers($url);
    $headers_check = $headers_array[0];

    if (strpos($headers_check, "200")) {
      return TRUE;
    }

    return $headers_check;
  }

  /**
   * Takes a source file and replaces the existing schema file in filesystem.
   *
   * This will allow us to update the import as needed.
   *
   * @param array|string $source_file
   *   The source file to pull in.
   *
   * @return null|string
   *   The destination for the file.
   */
  public static function clearDestinationAndPullInNew(array|string $source_file): ?string {
    // For Apex.
    $dest_directory = 'public://import/pim_data';
    $destination = NULL;

    \Drupal::service('file_system')->prepareDirectory(
      $dest_directory,
      FileSystemInterface::CREATE_DIRECTORY
    );

    if (is_object($source_file)) {
      $source_filename = $source_file->uri;
    }
    else {
      $source_filename = $source_file;
    }

    try {
      $destination = \Drupal::service('file_system')->copy(
        $source_filename,
        $dest_directory . '/pim_export_with_schema.xml',
        FileSystemInterface::EXISTS_REPLACE
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError('Failed to copy uploaded file to the pim_data directory. Error: ' . $e->getMessage());
    }

    return $destination;
  }

  /**
   * Takes a source file and replaces the existing schema file in filesystem.
   *
   * This will allow us to update the import as needed.
   *
   * @param array|string $source_file
   *   The source file to pull in.
   * @param array|string $output_file
   *   The output file to put in.
   *
   * @return null|string
   *   The destination for the file.
   */
  public static function clearDestinationAndPullInNewFile(array|string $source_file, $output_file = 'pim_export_with_schema.xml'): ?string {
    // For Apex.
    $dest_directory = 'public://import/pim_data';
    $destination = NULL;

    \Drupal::service('file_system')->prepareDirectory(
      $dest_directory,
      FileSystemInterface::CREATE_DIRECTORY
    );

    if (is_object($source_file)) {
      $source_filename = $source_file->uri;
    }
    else {
      $source_filename = $source_file;
    }

    try {
      $destination = \Drupal::service('file_system')->copy(
        $source_filename,
        $dest_directory . '/' . $output_file,
        FileSystemInterface::EXISTS_REPLACE
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError('Failed to copy uploaded file to the pim_data directory. Error: ' . $e->getMessage());
    }

    return $destination;
  }

}
