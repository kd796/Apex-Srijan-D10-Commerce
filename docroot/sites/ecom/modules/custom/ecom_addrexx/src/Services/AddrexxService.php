<?php

namespace Drupal\ecom_addrexx\Services;

use GuzzleHttp\ClientInterface;
use Drupal\ecom_addrexx\AddrexxInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;

class AddrexxService implements AddrexxInterface {

  /**
   * Config factory variable.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface configFactory
   */
  protected $configFactory;


  /**
   * Http client variable.
   *
   * @var \Drupal\Core\Http\Client
   */
  protected $httpClient;

  /**
   * Variable to store addrexx endpoint URL.
   *
   * @var string endpointUrl
   */
  protected $endpointUrl;

  /**
   * Variable to store addrexx frontend key.
   *
   * @var string frontendKey
   */
  protected $frontendKey;

  /**
   * Class default constructor method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->configFactory = $config_factory->get('ecom_addrexx.settings');
    $this->httpClient = $http_client;

    $this->init();
  }

  /**
   * Default initialization of class member.
   */
  private function init() {
    if ($this->configFactory) {
      $this->endpointUrl = $this->configFactory->get('ecom_api_endpoint');
      $this->frontendKey = $this->configFactory->get('ecom_api_frontend_key');
    }
  }

  /**
   * Make API call.
   */
  protected function apiCall($search, $filters = []) {
    $apiUrl = $this->endpointUrl . $search;
    $filters = array_merge($filters, [
      'apiKey' => $this->frontendKey,
    ]);

    try {
      $apiUrl = Url::fromUri($apiUrl, ['query' => $filters])->toString();
      $apiUrl = htmlspecialchars_decode(urldecode($apiUrl));
      $response = $this->httpClient->get($apiUrl, [
        'headers' => [
          'Accept' => 'application/json',
        ],
      ]);
      if ($response->getStatusCode() == 200) {
        $jsonData = $this->removeParentheses($response->getBody()->getContents());
        $data = Json::decode(trim($jsonData));
        // Check if decoding was successful.
        if ($data !== NULL) {
          return $data;
        }
        else {
          return [
            "data" => "No suggestions found.",
          ];
        }
      }

      return [
        "error" => "Error while fetching address",
      ];
    }
    catch (\Exception $e) {
      return [
        'error' => 'Error while validating address.',
      ];
    }
  }

  /**
   * Remove parantheses.
   */
  private function removeParentheses($inputString) {
    $outputString = str_replace(['(', ')', ';'], "", $inputString);

    return $outputString;
  }

  /**
   * {@inheritdoc}
   */
  public function getbyZip($prefixText = '', $contextKey = '') {
    return $this->apiCall('ZIP', [
      'prefixText' => $prefixText,
      'contextKey' => $contextKey,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getbyFirstName($prefixText = '', $contextKey = '') {
    return $this->apiCall('FIRST', [
      'prefixText' => $prefixText,
      'contextKey' => $contextKey,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getbyLastName($prefixText = '', $contextKey = '') {
    return $this->apiCall('LAST', [
      'prefixText' => $prefixText,
      'contextKey' => $contextKey,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getbyStreet($prefixText = '', $contextKey = '') {
    return $this->apiCall('STREET22', [
      'prefixText' => $prefixText,
      'contextKey' => $contextKey,
    ]);
  }

}
