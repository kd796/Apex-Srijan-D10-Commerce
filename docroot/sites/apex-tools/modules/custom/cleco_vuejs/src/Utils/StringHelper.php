<?php

namespace Drupal\cleco_vuejs\Utils;

use Drupal\Component\Transliteration\PhpTransliteration;

/**
 *
 */
class StringHelper {

  /**
   * @param  string $str
   * @return mixed
   */
  public static function toAscii(string $str): string {
    $trans       = new PhpTransliteration();
    $transformed = $trans->transliterate($str, 'en');

    return $transformed;
  }

  /**
   * @param string $str
   */
  public static function stripHtml(string $str): string {
    return preg_replace('/<(.*?)>/u', '', $str);
  }

  /**
   * Safe/Readable Key Generation for ElasticSearch.
   *
   * @param string $key
   */
  public static function createKey(string $str) {
    // Convert local characeters.
    $str = self::toAscii($str);
    // Strip HTML.
    $str = self::stripHtml($str);
    // Lowercase.
    $str = strtolower($str);
    // Remove all special characters.
    $str = preg_replace('/[^a-z0-9_\s-]/', '_', $str);
    // Replace spaces or dashes with underscore.
    $str = preg_replace('/[\s-]+/', '_', (string) $str);
    // Remove double underscores.
    $str = preg_replace('/__+/', '_', (string) $str);
    // Remove trailing underscore.
    $str = rtrim($str, '_');

    return $str;
  }

  /**
   * Pretty URL Generator.
   *
   * @param string $str
   * @param string $delimiter
   *
   * @return mixed
   */
  public static function createSlug(string $str, string $delimiter = '-') {
    // Need add dash where white space before special characters removal
    // Convert local characeters.
    $str = self::toAscii($str);
    // Decode HTML entities.
    $str = html_entity_decode($str);
    // Strip HTML.
    $str = self::stripHtml($str);
    // Lowercase.
    $str = strtolower($str);
    // Replace spaces and underscores with dash.
    $str = preg_replace('/[\s_]/', '-', $str);
    // Replace Ampersand.
    $str = preg_replace('/&/', '-', $str);
    // Replace special characters.
    $str = preg_replace('/[^a-z0-9-]/', '', $str);
    // Remove double dash.
    $str = preg_replace('/--+/', '-', (string) $str);
    // Remove trailing dash.
    $str = rtrim($str, '-');

    return $str;
  }

  /**
   * Pretty Slug Generator.
   *
   * @param string $str
   */
  public static function createHandle(string $str) {
    // Convert local characeters.
    $str = self::toAscii($str);
    // Decode HTML entities.
    $str = html_entity_decode($str);
    // Strip HTML.
    $str = self::stripHtml($str);
    // Replace special characters.
    $str = preg_replace('/[^a-zA-Z0-9]/', '', $str);
    // Handle must start with a letter.
    $str = ltrim($str, '0123456789');

    return lcfirst($str);
  }

  /**
   * Get the last part of UnitID attr.
   *
   * @param string $str
   */
  public static function getUnit(string $str) {
    return self::createKey(substr($str, strrpos($str, '.') + 1));
  }

  /**
   * @param string $str
   */
  public static function crateReadable(string $str) {
    $str = str_replace('_', ' ', $str);

    return ucwords($str);
  }

}
