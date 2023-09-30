<?php

namespace Drupal\ecom_addrexx\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\ecom_addrexx\CommonConstants;

/**
 * Controller for handling AJAX requests related to US Tax Data.
 */
class AddressProfileController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
     );
  }

  /**
   * Handles an AJAX request to retrieve County by address.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with county or an error message.
   */
  public function getCounty(Request $request) {
    // Get values from the AJAX request.
    $city = $request->query->get('city');
    $state = $request->query->get('state');
    $zipcode = $request->query->get('zipcode');

    // Create an array to hold address elements.
    $addressElements = [
      'city' => $city,
      'state' => $state,
      'zipcode' => $zipcode,
    ];

    // Load term IDs for county Data by address elements.
    $termIds = $this->loadTermIdsByAddress($addressElements);

    $county = [];

    // Remove county condition, so that it should always display on value.
    // if (count($termIds) > 1) {
      foreach ($termIds as $termId) {
        // Load the taxonomy term.
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId);
        if ($term) {
          // Get the county field value.
          $county[] = $term->get('field_us_county')->value;
        }
      }
    // }

    // Return a JSON response with the term information or an error.
    if (!empty($county)) {
      return new JsonResponse(["county" => $county]);
    }
    else {
      return new JsonResponse([CommonConstants::API_RESULT_NOT_FOUND]);
    }
  }

  /**
   * Loads taxonomy term IDs for address elements.
   *
   * @param array $addressElements
   *   An array of address elements (city and state).
   *
   * @return array
   *   An array of taxonomy term IDs.
   */
  private function loadTermIdsByAddress(array $addressElements) {
    $cityVal = $addressElements['city'];
    $stateVal = $addressElements['state'];
    $zipcode = $addressElements['zipcode'];

    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'us_tax_data')
      ->accessCheck(FALSE)
      ->condition('field_us_city', $cityVal, 'LIKE')
      ->condition('field_us_state', $stateVal);
    if (!empty($zipcode) && $zipcode != '0') {
      $query->condition('field_starting_zip_code', $zipcode, '<=');
      $query->condition('field_ending_zip_code', $zipcode, '>=');
    }
    // Setting query range to 20 to minimize database query execution time.
    $query->range(0, 20);
    $termIds = $query->execute();

    // Reindex the array to start from 0.
    $termIds = array_values($termIds);

    return $termIds;
  }

  /**
   * Handles an AJAX request to retrieve city list.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with US Tax Data or an error message.
   */
  public function getCity(Request $request) {
    // Get values from the AJAX request.
    $state = $request->query->get('state');
    $cityCharacter = strtoupper($request->query->get('q'));

    // Create an array to hold address elements.
    $addressElements = [
      'state' => $state,
      'city' => $cityCharacter,
    ];

    // Load term IDs for US Tax Data by address elements.
    $termIds = $this->loadTermIdsByState($addressElements);
    $city = [];

    foreach ($termIds as $termId) {
      // Load the taxonomy term.
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId);
      if ($term) {
        // Skip duplicate city results.
        if (!in_array($term->get('field_us_city')->value, $city)) {
          // Get the city field value.
          $city[] = $term->get('field_us_city')->value;
        }
      }
    }

    // Return a JSON response with the term information or an error.
    if (!empty($city)) {
      return new JsonResponse($city);
    }
    else {
      return new JsonResponse([CommonConstants::API_RESULT_NOT_FOUND]);
    }
  }

  /**
   * Loads taxonomy term IDs for City options by state elements.
   *
   * @param array $addressElements
   *   An array of address elements (state and city).
   *
   * @return array
   *   An array of taxonomy term IDs.
   */
  private function loadTermIdsByState(array $addressElements) {
    $stateVal = $addressElements['state'];
    $cityStartsChar = $addressElements['city'];

    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'us_tax_data')
      ->accessCheck(FALSE);

    if (!empty($stateVal) && $stateVal != 'All') {
      $query->condition('field_us_state', $stateVal);
    }
    if (!empty($cityStartsChar)) {
      $query->condition('field_us_city', $cityStartsChar, 'STARTS_WITH');
      $query->groupBy("field_us_city");
    }
    // Setting query range to 20 to minimize database query execution time.
    $query->range(0, 20);

    $termIds = $query->execute();

    // Reindex the array to start from 0.
    $termIds = array_values($termIds);

    return $termIds;
  }

}
