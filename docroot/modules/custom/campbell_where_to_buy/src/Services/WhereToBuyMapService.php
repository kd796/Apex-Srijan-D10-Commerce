<?php

namespace Drupal\campbell_where_to_buy\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides methods to get/alter Where To Buy Map.
 */
class WhereToBuyMapService {

  /**
   * The state address.
   *
   * @var array
   */
  protected $stateAddress;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a WhereToBuyMapService.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_manager,
    Connection $connection
  ) {
    $this->entityManager = $entity_manager;
    $this->connection = $connection;
  }

  /**
   * Set State address list.
   *
   * @param array $state_address
   *   State address array.
   */
  public function setStateAddress(array $state_address) {
    if (!isset($this->stateAddress)) {
      $this->stateAddress = $state_address;
    }
  }

  /**
   * Get State address list.
   *
   * @return array
   *   State address list.
   */
  public function getStateAddress() {
    return $this->stateAddress;
  }

  /**
   * Get Latitude Longitude from Address.
   *
   * @param string $city
   *   City Name.
   * @param string $province
   *   State Name.
   * @param string $country
   *   Country Code.
   * @param int $miles
   *   Miles.
   *
   * @return array
   *   Latitude Longitude in array.
   */
  private function getLatitudeLongitudeFromAddress(string $city = '', string $province = '', string $country = '', int $miles = 20) {
    $latlan = [];
    $query = $this->connection->select('node_field_data', 'n')
      ->fields('n', ['nid']);
    $query->fields('nfa', [
      'field_address_postal_code',
    ]);
    $query->fields('nfl', [
      'field_location_lat',
      'field_location_lng',
    ]);
    $query->join('node__field_location', 'nfl', 'n.nid = nfl.entity_id AND n.type = nfl.bundle');
    $query->join('node__field_address', 'nfa', 'n.nid = nfa.entity_id AND n.type = nfa.bundle');
    $query->condition('n.status', 1);
    $query->condition('n.type', 'local_retailer');

    if ($province) {
      $state_value = array_search($province, $this->stateAddress);
      $query->condition('nfa.field_address_administrative_area', ($state_value ?? ''));
    }
    if ($city) {
      $query->condition('nfa.field_address_locality', $city);
    }
    if ($country) {
      $query->condition('nfa.field_address_country_code', $country);
    }
    $query_result = $query->execute()->fetchAll();
    $latitude = "";
    $longitude = "";
    foreach ($query_result as $row) {
      $latitude = $row->field_location_lat;
      $longitude = $row->field_location_lng;
    }

    if ($latitude && $longitude) {
      $equator_lat_mile = 69.172;

      $latlan['maxLat'] = $latitude + $miles / $equator_lat_mile;
      $latlan['minLat'] = $latitude - ($latlan['maxLat'] - $latitude);
      $latlan['maxLong'] = $longitude + $miles / (cos($latlan['minLat'] * M_PI / 180) * $equator_lat_mile);
      $latlan['minLong'] = $longitude - ($latlan['maxLong'] - $longitude);
      $latlan['orgLat'] = $latitude;
      $latlan['orgLong'] = $longitude;
    }
    else {
      $latlan['maxLat'] = 0;
      $latlan['minLat'] = 0;
      $latlan['maxLong'] = 0;
      $latlan['minLong'] = 0;
      $latlan['orgLat'] = $latitude ?? 0;
      $latlan['orgLong'] = $longitude ?? 0;
    }
    return $latlan;
  }

  /**
   * Address based Proximity search.
   *
   * @param \Symfony\Component\HttpFoundation\ParameterBag $query_perms
   *   Service request_stack.
   * @param int $miles
   *   Miles.
   *
   * @return array
   *   Node nids.
   */
  public function findZipCodeProximitySearchFromAddress(ParameterBag $query_perms, int $miles = 20) {
    $latlan = [];
    $city = $query_perms->get('field_address_locality') ?? '';
    $province = $query_perms->get('field_address_administrative_area_textfield') ?? '';
    $country = $query_perms->get('field_address_country_code') ?? '';
    $latlan = $this->getLatitudeLongitudeFromAddress($city, $province, $country, $miles);

    $query_result = $this->getAllProximityData($latlan);

    $latlonA = ['lon' => $latlan['orgLong'], 'lat' => $latlan['orgLat']];
    $latlonB = ['lon' => 0, 'lat' => 0];
    $distance = [];

    foreach ($query_result as $result) {
      $latlonB['lat'] = $result->field_location_lat;
      $latlonB['lon'] = $result->field_location_lng;
      $distance[$result->nid]['distance'] = $this->locationDistanceBetween($latlonA, $latlonB, 'mile');
    }

    $distance_sorted = [];
    foreach ($distance as $key => $d) {
      $distance_sorted[$key] = $d['distance']['scalar'];
    }

    asort($distance_sorted);

    return array_keys($distance_sorted);
  }

  /**
   * Get Latitude Longitude from Zip code.
   *
   * @param string $zip
   *   Zip code.
   * @param int $miles
   *   Miles.
   *
   * @return array
   *   Latitude Longitude in array.
   */
  private function getLatitudeLongitudeZip($zip, int $miles = 20) {
    $latlan = [];

    $query_result = $this->connection->query('SELECT n.nid, nfa.field_address_postal_code, nfl.field_location_lat, nfl.field_location_lng
                                              FROM {node_field_data} n
                                              INNER JOIN {node__field_location} nfl
                                              ON n.nid = nfl.entity_id AND n.type = nfl.bundle
                                              INNER JOIN {node__field_address} nfa
                                              ON n.nid = nfa.entity_id AND n.type = nfa.bundle
                                              WHERE n.status = 1
                                              AND n.type = :node_type
                                              AND nfa.field_address_postal_code = :postal_code
                                              LIMIT 1', [
                                                ':node_type' => 'local_retailer',
                                                ':postal_code' => $zip,
                                              ])->fetchAssoc();

    if (isset($query_result['field_location_lat']) && $query_result['field_location_lat'] &&
      isset($query_result['field_location_lng']) && $query_result['field_location_lng']) {
      $equator_lat_mile = 69.172;

      $latlan['maxLat'] = $query_result['field_location_lat'] + $miles / $equator_lat_mile;
      $latlan['minLat'] = $query_result['field_location_lat'] - ($latlan['maxLat'] - $query_result['field_location_lat']);
      $latlan['maxLong'] = $query_result['field_location_lng'] + $miles / (cos($latlan['minLat'] * M_PI / 180) * $equator_lat_mile);
      $latlan['minLong'] = $query_result['field_location_lng'] - ($latlan['maxLong'] - $query_result['field_location_lng']);
      $latlan['orgLat'] = $query_result['field_location_lat'];
      $latlan['orgLong'] = $query_result['field_location_lng'];
    }
    else {
      $latlan['maxLat'] = 0;
      $latlan['minLat'] = 0;
      $latlan['maxLong'] = 0;
      $latlan['minLong'] = 0;
      $latlan['orgLat'] = 0;
      $latlan['orgLong'] = 0;
    }
    return $latlan;
  }

  /**
   * Zip based Proximity search for 40 miles.
   *
   * @param string $zip
   *   Zip code.
   * @param int $miles
   *   Miles.
   *
   * @return array
   *   Node nids.
   */
  public function findZipCodeProximitySearchZip($zip, int $miles = 20) {
    $latlan = [];
    $latlan = $this->getLatitudeLongitudeZip($zip, $miles);

    $query_result = $this->getAllProximityData($latlan);

    $latlonA = ['lon' => $latlan['orgLong'], 'lat' => $latlan['orgLat']];
    $latlonB = ['lon' => 0, 'lat' => 0];
    $distance = [];

    foreach ($query_result as $result) {
      $latlonB['lat'] = $result->field_location_lat;
      $latlonB['lon'] = $result->field_location_lng;
      $distance[$result->nid]['distance'] = $this->locationDistanceBetween($latlonA, $latlonB, 'mile');
    }

    $distance_sorted = [];
    foreach ($distance as $key => $d) {
      $distance_sorted[$key] = $d['distance']['scalar'];
    }
    asort($distance_sorted);
    return array_keys($distance_sorted);
  }

  /**
   * Given two points in lat/lon form, returns the distance between them.
   *
   * @param array $latlon_a
   *   An associative array where
   *      'lon' => is a floating point of the longitude coordinate for the
   *               point given by latlonA
   *      'lat' => is a floating point of the latitude coordinate for the
   *               point given by latlonB.
   * @param array $latlon_b
   *   Another point formatted like $latlon_b.
   * @param string $distance_unit
   *   A string that is either 'km' or 'mile'.
   *      If neither 'km' or 'mile' is passed, the parameter is forced to 'km'.
   *
   * @return array|null
   *   NULL if sense can't be made of the parameters.
   *    An associative array where
   *      'scalar' => Is the distance between the two lat/lon parameter points
   *      'distance_unit' => Is the unit of distance being represented by
   *                         'scalar'. This will be 'km' unless 'mile' is
   *                         passed for the $distance_unit param
   *
   * @ingroup Location
   */
  private function locationDistanceBetween(array $latlon_a = [], array $latlon_b = [], $distance_unit = 'km') {
    if (!isset($latlon_a['lon']) || !isset($latlon_a['lat']) || !isset($latlon_b['lon']) || !isset($latlon_b['lat'])) {
      return NULL;
    }

    if ($distance_unit != 'km' && $distance_unit != 'mile') {
      return NULL;
    }

    $meters = $this->earthDistance($latlon_a['lon'], $latlon_a['lat'], $latlon_b['lon'], $latlon_b['lat']);

    return [
      'scalar' => round($meters / (($distance_unit == 'km') ? 1000.0 : 1609.347), 1),
      'distance_unit' => $distance_unit,
    ];
  }

  /**
   * Earth distance.
   */
  private function earthDistance($longitude1, $latitude1, $longitude2, $latitude2) {
    // Estimate the earth-surface distance between two locations.
    $long1 = deg2rad($longitude1);
    $lat1 = deg2rad($latitude1);
    $long2 = deg2rad($longitude2);
    $lat2 = deg2rad($latitude2);
    $radius = $this->earthRadius(($latitude1 + $latitude2) / 2);

    $cosangle = cos($lat1) * cos($lat2) *
      (cos($long1) * cos($long2) + sin($long1) * sin($long2)) +
      sin($lat1) * sin($lat2);

    return acos($cosangle) * $radius;
  }

  /**
   * Earth redius.
   *
   * Latitudes in all of U. S.: from -7.2 (American Samoa) to 70.5 (Alaska).
   * Latitudes in continental U. S.: from 24.6 (Florida) to 49.0 (Washington).
   * Average latitude of all U. S. zipcodes: 37.9.
   */
  private function earthRadius($latitude = 37.9) {
    // Estimate the Earth's radius at a given latitude.
    // Default to an approximate average radius for the United States.
    $lat = deg2rad($latitude);

    $x = cos($lat) / 6378137.0;
    $y = sin($lat) / 6378137.0;

    return 1 / (sqrt($x * $x + $y * $y));
  }

  /**
   * Get all proximity data.
   *
   * @param array $latlan
   *   Location latitude and longitude.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|string|null
   *   Query Output.
   */
  private function getAllProximityData(array $latlan) {
    $query_result = $this->connection->query('SELECT n.nid, n.title, nfa.field_address_postal_code, nfl.field_location_lat, nfl.field_location_lng
                                                      FROM {node_field_data} n
                                                      INNER JOIN {node__field_location} nfl
                                                      ON n.nid = nfl.entity_id AND n.type = nfl.bundle
                                                      INNER JOIN {node__field_address} nfa
                                                      ON n.nid = nfa.entity_id AND n.type = nfa.bundle
                                                      WHERE n.status = 1
                                                      AND n.type = :node_type
                                                      AND
                                                      nfl.field_location_lat >= :min_lat
                                                      AND
                                                      nfl.field_location_lat <= :max_lat
                                                      AND
                                                      nfl.field_location_lng >= :min_lan
                                                      AND
                                                      nfl.field_location_lng <= :max_lan', [
                                                        ':node_type' => 'local_retailer',
                                                        ':min_lat' => $latlan['minLat'],
                                                        ':max_lat' => $latlan['maxLat'],
                                                        ':min_lan' => $latlan['minLong'],
                                                        ':max_lan' => $latlan['maxLong'],
                                                      ])->fetchAll();
    return $query_result;
  }

}
