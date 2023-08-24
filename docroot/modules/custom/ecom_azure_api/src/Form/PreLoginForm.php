<?php

namespace Drupal\ecom_azure_api\Form;

use Drupal\Core\Cache\CacheBackendInterface;
/**
 * @file
 * Contains \Drupal\ecom_azure_api\Form\PreLoginForm.
 */
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for PreLoginForm form.
 */
class PreLoginForm extends FormBase {

  /**
   * Http client variable.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Cache variable.
   *
   * @var Drupal\Core\Cache\Cache
   */
  protected $cache;

  /**
   * {@inheritdoc}
   */
  public function __construct(Client $http_client, CacheBackendInterface $cache) {
    $this->httpClient = $http_client;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('cache.default')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ecom_pre_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#cache'] = ['max-age' => 0];

    $form['network_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ATG Network ID'),
      '#required' => TRUE,
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $azure_config = $this->config('ecom_azure.settings');
    $access_token_cache = $this->cache->get('access_token');

    if (!$access_token_cache) {
      // Call OAuth API to get application access token.
      $access_token_data = $this->getAccessToken($azure_config);
      if (isset($access_token_data['error'])) {
        $form_state->setError($form, $this->t('%err', ['%err' => $access_token_data['error']]));
        return;
      }
      $access_token = $access_token_data['access_token'];
      $expire = REQUEST_TIME + $azure_config->get('ecom_app_cache_expire');
      $this->cache->set('access_token', $access_token, $expire, ['azure_access_token']);
    }
    else {
      // Retrieve the access token from the cache.
      $access_token = $access_token_cache->data;
    }

    $api_response = $this->getUserPrincipalName($azure_config, $access_token,
      $form_state->getValue('network_id'));
    // If the above call fails, show user credentials error.
    if (isset($api_response['error'])) {
      $form_state->setError($form, $this->t('%err', ['%err' => $api_response['error']]));
      return;
    }
    elseif (isset($api_response['@odata.count']) && $api_response['@odata.count'] == 0) {
      $form_state->setError($form,
        $this->t('%err', ['%err' => "Your credentials are not authorized to access this website."]));
      return;
    }

    if ($api_response && $api_response['@odata.count'] > 0) {
      $auth_response = $this->userAuthentication($azure_config, $api_response['value'][0]['userPrincipalName'],
      $form_state->getValue('password'));
      if (isset($auth_response['error'])) {
        $form_state->setError($form, $this->t('%err', ['%err' => $auth_response['error']]));
        return;
      }
    }
    else {
      $form_state->setError($form, $this->t('%err', [
        '%err' => "Either ATG Network ID or Password is missing",
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('session')->set('pre_login_success', TRUE);
    $current_path = \Drupal::service('path.current')->getPath();
    $destination = \Drupal::destination()->get();

    if ($current_path !== $destination) {
      $url = Url::fromUri('internal:' . $destination);
      $form_state->setRedirectUrl($url);
    }
  }

  /**
   * GetAccessToken via Azure API.
   */
  private function getAccessToken($config) {
    $oauth_endpoint = $config->get('ecom_app_token_url');
    $oauth_params = [
      'form_params' => [
        'grant_type' => $config->get('ecom_app_auth_grant'),
        'client_id' => $config->get('ecom_client_id'),
        'client_secret' => $config->get('ecom_client_secret'),
        'scope' => $config->get('ecom_app_auth_scope'),
      ],
    ];
    try {
      // Send request to OAuth API.
      $response = $this->httpClient->post($oauth_endpoint, $oauth_params);

      return json_decode($response->getBody(), TRUE);
    }
    catch (ClientException $e) {
      return [
        'error' => 'Connecting parameters not initialized.',
      ];
    }
  }

  /**
   * GetUserPrincipalName via Azure API.
   */
  private function getUserPrincipalName($config, $access_token, $name) {
    // Another API endpoint and headers.
    $api_endpoint = $config->get('ecom_graph_user_api');
    $query_params = [
      '$filter' => "onPremisesSamAccountName eq '" . $name . "'",
      '$count' => 'true',
      '$select' => 'userPrincipalName',
    ];
    $url = Url::fromUri($api_endpoint, ['query' => $query_params]);
    $api_url = htmlspecialchars_decode(urldecode($url->toString()));

    $api_headers = [
      'Authorization' => 'Bearer ' . $access_token,
      'ConsistencyLevel' => 'eventual',
    ];

    try {
      $response = $this->httpClient->get($api_url, ['headers' => $api_headers]);

      return json_decode($response->getBody(), TRUE);
    }
    catch (ClientException $e) {
      return [
        'error' => 'Your credentials are not authorized to access this website.',
      ];
    }
  }

  /**
   * Authenticate user via Azure API.
   */
  private function userAuthentication($config, $name, $password) {
    $oauth_endpoint = $config->get('ecom_client_token_url');
    $oauth_params = [
      'form_params' => [
        'grant_type' => $config->get('ecom_client_auth_grant'),
        'client_id' => $config->get('ecom_client_id'),
        'scope' => $config->get('ecom_client_auth_scope'),
        'username' => $name,
        'password' => $password,
      ],
    ];

    try {
      // Send request to OAuth API.
      $response = $this->httpClient->post($oauth_endpoint, $oauth_params);

      return json_decode($response->getBody(), TRUE);
    }
    catch (ClientException $e) {
      return [
        'error' => 'Your credentials are not authorized to access this website.',
      ];
    }
  }

}
