<?php

namespace Drupal\atg_api;

class DistanceCalculator {

  /** @var \Drupal\atg_api\Coordinates */
  public $origin;

  /** @var \Drupal\atg_api\Coordinates */
  public $destination;

  /** @var string */
  public $units;

  /** @var int|float */
  protected $radius;

  protected static $radiusByUnits = [
    'mi' => 3959,
    'm'  => 6371000,
    'km' => 6371,
  ];

  public function __construct(Coordinates $coordinates) {
    $this->units  = 'mi';
    $this->radius = self::radius($this->units);
    $this->origin = $coordinates;
  }

  public function __toString() {
    return (number_format($this->getDistance(), 0)) . ' ' . $this->units;
  }

  public function toArray() {
    return [
      'origin' => $this->origin,
      'destination' => $this->destination,
      'units' => $this->units,
      'distance' => $this->getDistance(),
      'formatted' => (string) $this,
    ];
  }


  public function to(Coordinates $coordinates) {
    $this->destination = $coordinates;

    return $this;
  }


  public function in($units) {
    if (self::validateUnits($units)) {
      $this->units  = $units;
      $this->radius = self::radius($units);
    }

    return $this;
  }


  protected function getDistance() {
    // Convert coordinates to radians
    $originLatitudeRadians       = deg2rad($this->origin->latitude);
    $originLongitudeRadians      = deg2rad($this->origin->longitude);
    $destinationLatitudeRadians  = deg2rad($this->destination->latitude);
    $destinationLongitudeRadians = deg2rad($this->destination->longitude);

    // Get deltas by direction
    $latitudeDelta  = $originLatitudeRadians - $destinationLatitudeRadians;
    $longitudeDelta = $originLongitudeRadians - $destinationLongitudeRadians;

    // Calculate the angle
    $angle = 2 *
             asin(sqrt(pow(sin($latitudeDelta / 2), 2) +
                       cos($originLatitudeRadians) *
                       cos($destinationLatitudeRadians) *
                       pow(sin($longitudeDelta / 2), 2)));

    return $angle * $this->radius;
  }


  public function get() {
    return $this->getDistance();
  }


  public function toString() {
    return (number_format($this->getDistance(), 2)) . ' ' . $this->units;
  }

  public static function radius($units) {
    return self::$radiusByUnits[$units];
  }

  public static function validateUnits($units) {
    return in_array($units, array_keys(self::$radiusByUnits));
  }
}
