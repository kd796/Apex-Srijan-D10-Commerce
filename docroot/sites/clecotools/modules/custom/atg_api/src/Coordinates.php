<?php

namespace Drupal\atg_api;

use function is_array;

class Coordinates {

  /**
   * @var float
   */
  public $latitude;

  /**
   * @var float
   */
  public $longitude;

  /**
   * Coordinates constructor.
   *
   * @param float $latitude
   * @param float $longitude
   */
  public function __construct(float $latitude, float $longitude) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
  }

  /**
   * PARSE
   * Parse a possible coordinates value.
   *
   * @param $value
   *
   * @return bool|\Drupal\atg_api\Coordinates
   */
  public static function parse($value) {
    if (is_array($value) && count($value) === 2) {
      $value = array_values($value);

      return new Coordinates($value[0], $value[1]);
    } elseif (is_string($value)) {
      list($latitude, $longitude) = array_map('floatval', explode(',', $value));

      return new Coordinates($latitude, $longitude);
    }

    return false;
  }
}
