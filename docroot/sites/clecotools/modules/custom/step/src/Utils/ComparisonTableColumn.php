<?php

namespace Drupal\step\Utils;

use function preg_replace;

class ComparisonTableColumn {

  /**
   * Key to use for imperial units.
   *
   * @var string
   */
  const UNIT_TYPE_IMPERIAL = 'imperial';

  /**
   * Key to use for metric units.
   *
   * @var string
   */
  const UNIT_TYPE_METRIC = 'metric';

  /**
   * Label to show for this column.
   *
   * @var string
   */
  protected $label;

  /**
   * Key definition for this column.
   *
   * @var string|array
   */
  protected $key;

  /**
   * Unit definition for this column.
   *
   * @var string|array
   */
  protected $units;

  /**
   * Data type definition for this column.
   *
   * @var null|string
   */
  protected $type;

  /**
   * ComparisonTableColumn constructor.
   *
   * @param string $label Column display label
   * @param null $type Data type to use
   */
  public function __construct(string $label, $type = NULL) {
    $this->label = $label;
    $this->type  = $type;
  }

  /**
   * GET LABEL
   * Return the column label.
   *
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * GET KEY
   * Return the column key definition.
   *
   * @return array|string
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * GET TYPE
   * Return the column type.
   *
   * @return null|string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * GET UNITS
   * Return the units definition.
   *
   * @return array|string
   */
  public function getUnits() {
    return $this->units;
  }

  /**
   * GET SLUG
   * Return a sanitized slug to use for this column's data.
   * Generated based on column label.
   *
   * @return null|string
   */
  public function getSlug() {
    $slug = trim($this->label . ' ' . implode(' ', (array) $this->units));
    $slug = strtolower($slug);
    $slug = preg_replace('/[^\w]/i', '_', $slug);

    return $slug;
  }

  /**
   * FOR KEY
   * Set the key defintion.
   *
   * @param $key
   *
   * @return $this
   */
  public function forKey($key) {
    $this->key = $key;

    return $this;
  }

  /**
   * FOR UNIT TYPE KEY
   * Utility to set both a key and units value for the
   * specified unit type.
   *
   * @param string $unitType Unit type
   * @param string $key Data key to use
   * @param string|null $units Units to display
   *
   * @return $this
   */
  protected function forUnitTypeKey($unitType, $key, $units = NULL) {
    $this->key[$unitType] = $key;
    if ($units) {
      $this->withUnitTypeUnits($unitType, $units);
    }

    return $this;
  }

  /**
   * FOR IMPERIAL KEY
   * Alias to set an imperial key and units.
   *
   * @param string $key Data key to use
   * @param null|string $units Units to display
   *
   * @return \Drupal\step\Utils\ComparisonTableColumn
   */
  public function forImperialKey(string $key, $units = NULL) {
    return $this->forUnitTypeKey(self::UNIT_TYPE_IMPERIAL, $key, $units);
  }

  /**
   * FOR METRIC KEY
   * Alias tto set a metric key and units.
   *
   * @param string $key Data key to use
   * @param null|string $units Units to display
   *
   * @return \Drupal\step\Utils\ComparisonTableColumn
   */
  public function forMetricKey(string $key, $units = NULL) {
    return $this->forUnitTypeKey(self::UNIT_TYPE_METRIC, $key, $units);
  }

  /**
   * WITH UNITS
   * Set the units to display.
   *
   * @param string $units Units to display
   *
   * @return $this
   */
  public function withUnits(string $units) {
    $this->units = $units;

    return $this;
  }

  /**
   * WITH UNIT TYPE UNITS
   * Utility to set the units value for a specified unit type.
   *
   * @param string $unitType Unit type to set
   * @param string $units Units to display
   *
   * @return $this
   */
  protected function withUnitTypeUnits(string $unitType, string $units) {
    $this->units[$unitType] = $units;

    return $this;
  }

  /**
   * WITH IMPERIAL UNITS
   * Alias to set imperial units label.
   *
   * @param string $units Units to display
   *
   * @return \Drupal\step\Utils\ComparisonTableColumn
   */
  public function withImperialUnits(string $units) {
    return $this->withUnitTypeUnits(self::UNIT_TYPE_IMPERIAL, $units);
  }

  /**
   * WITH METRIC UNITS
   * Alias to set metric units label.
   *
   * @param string $units Units to display
   *
   * @return \Drupal\step\Utils\ComparisonTableColumn
   */
  public function withMetricUnits(string $units) {
    return $this->withUnitTypeUnits(self::UNIT_TYPE_METRIC, $units);
  }

  /**
   * WITH TYPE
   * Set the data type.
   *
   * @param string $type Data type
   *
   * @return $this
   */
  public function withType(string $type) {
    $this->type = $type;

    return $this;
  }
}
