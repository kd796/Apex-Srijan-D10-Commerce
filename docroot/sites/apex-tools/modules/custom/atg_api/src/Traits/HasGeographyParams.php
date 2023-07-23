<?php

namespace Drupal\atg_api\Traits;

use Drupal\search_api\Query\Query;
use Drupal\atg_api\Coordinates;
use Drupal\atg_api\DistanceCalculator;

/**
 * Get map parameters.
 */
trait HasGeographyParams {

  /**
   * The Drupal request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   * The request object representing the current HTTP request or null.
   */
  protected $request;

  /**
   * Constructor for the request object.
   */
  public function __construct() {
    $this->request = \Drupal::request();
  }

  /**
   * Return the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Return Drupal request.
   */
  protected function getRequest() {
    return \Drupal::request();
  }

  /**
   * Return the coordinates to use for the origin of the geographic search.
   *
   * @return bool|\Drupal\atg_api\Coordinates
   *   Return Coordinates.
   */
  protected function getCenter() {
    $center = $this->parseCenter();
    if (!$center) {
      $center = $this->queryCenter();
    }

    return $center;
  }

  /**
   * Return the radius to use for the request.
   *
   * @return float|int
   *   Return Radius value.
   */
  protected function getRadius() {
    // @todo change value after ZIPCODE is updated.
    return 10;
  }

  /**
   * Return the units to use for the request.
   *
   * @return bool|mixed
   *   Return units.
   */
  protected function getUnits() {
    $units = $this->request->query->get('units');

    if (!DistanceCalculator::validateUnits($units)) {
      return FALSE;
    }

    return $units;
  }

  /**
   * Attempt to parse coordinates out of the request's `center` parameter.
   *
   * @return bool|\Drupal\atg_api\Coordinates
   *   Return Coordinates.
   */
  protected function parseCenter() {
    return Coordinates::parse($this->request->query->get('center'));
  }

  /**
   * Attempt to Geocode the value of the request's `q` paremeter.
   *
   * @return bool|\Drupal\atg_api\Coordinates
   *   Return Coordinates.
   */
  protected function queryCenter() {
    $plugins = ['googlemaps'];
    $query = $this->request->query->get('q');

    // Set country as US.
    $country = 'US';
    $query = $query . ', ' . $country;

    $addresses = \Drupal::service('geocoder')->geocode($query, $plugins);
    if ($addresses === FALSE) {
      return FALSE;
    }

    /** @var \Geocoder\Model\AddressCollection $addresses */
    $address     = $addresses->first();
    $coordinates = $address->getCoordinates();

    return new Coordinates($coordinates->getLatitude(), $coordinates->getLongitude());
  }

  /**
   * Return a DistanceCalculator for the passed entity.
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *
   * @return \Drupal\atg_api\DistanceCalculator
   * Returns distance.
   */

  /**
   * IMPORTANT: this is returning a type of Entity/Node, NOT just Entity.
   */
  protected function getEntityDistance($entity) {
    $coordinates = $this->getEntityCoordinates($entity);
    $center      = $this->getCenter();

    if ($center && $coordinates) {
      $distance = new DistanceCalculator($coordinates);
      $units    = $this->getUnits() ?: 'mi';

      return $distance->to($center)->in($units);
    }

    return NULL;
  }

  /**
   * Return the coordinates of the entity using the passed field name.
   *
   * @param \Drupal\Core\Entity\Entity $entity
   * @param string $field_name
   *
   * @return bool|\Drupal\atg_api\Coordinates
   * Return Coordinates.
   */

  /**
   * IMPORTANT: this is returning a type of Entity/Node, NOT just Entity.
   */
  protected function getEntityCoordinates($entity, $field_name = 'field_geographic_data') {
    $field = $entity->{$field_name};
    if ($field && $field->lat && $field->lon) {
      return new Coordinates($entity->{$field_name}->lat, $entity->{$field_name}->lon);
    }

    return FALSE;
  }

  /**
   * Modify the passed query to tag it for Haversine modification.
   *
   * @param \Drupal\search_api\Query\Query $query
   *   The query object to be modified.
   *
   * @see \atg_api_search_api_db_query_alter()
   */
  protected function applyGeoParameters(Query &$query) {
    $center = $this->getCenter();
    $radius = $this->getRadius();
    $units  = $this->getUnits() ?: 'mi';

    if ($center) {
      $query->addTag('haversine');
      $query->setOption('atg_api.haversine', [
        'center' => $center,
        'radius' => $radius,
        'units'  => $units,
      ]);
    }
  }

}
