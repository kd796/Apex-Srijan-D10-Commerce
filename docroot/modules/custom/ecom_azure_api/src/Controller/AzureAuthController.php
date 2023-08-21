<?php

namespace Drupal\ecom_azure_api\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class for AzureAuthController.
 */
class AzureAuthController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * HTTP requeststack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Variable to store tenant ID.
   *
   * @var string
   */
  private $tenantId;

  /**
   * Variable to store client ID.
   *
   * @var string
   */
  private $clientId;

  /**
   * Variable to client secret.
   *
   * @var string
   */
  private $clientSecret;

  /**
   * Constructs a AzureAuthController object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->tenantId = '231bbb7c-1b4b-43dc-bcb5-c40e5f722be5';
    $this->clientId = '1b2cf885-bdc6-4b13-af26-73c624920d0a';
    $this->clientSecret = '5468Q~z8O9TqbJZtrulrC-m6cl1-p6bUOq7cEcBe';
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack')
    );
  }

  /**
   * Callback for API.
   */
  public function callback(Request $request = NULL) {
    if (!$request) {
      $request = $this->requestStack->getCurrentRequest();
    }
    $redirect_uri = $request->getUri();
    $auth_url = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/authorize';
    $token_url = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
    $scope = 'openid profile email';
    $state = 'random_state_string';

    // Handle callback.
    if ($request->query->has('code')) {
      $code = $request->query->get('code');

      // Exchange code for access token.
      $token_params = [
        'clientId' => $this->clientId,
        'clientSecret' => $this->clientSecret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
        'scope' => $scope,
      ];
      $token_response = \Drupal::httpClient()->post($token_url, [
        'form_params' => $token_params,
      ]);
      $token_data = json_decode($token_response->getBody(), TRUE);

      // Use access token for API requests.
      $access_token = $token_data['access_token'];
      $api_response = \Drupal::httpClient()->get('https://graph.microsoft.com/v1.0/me', [
        'headers' => [
          'Authorization' => 'Bearer ' . $access_token,
        ],
      ]);
      $api_data = json_decode($api_response->getBody(), TRUE);

      // Process and display API data.
      drupal_set_message('User ID: ' . $api_data['id']);
      drupal_set_message('User Name: ' . $api_data['displayName']);
    }
    else {
      // Redirect user to Azure AD for authentication.
      $auth_params = [
        'clientId' => $clientId,
        'response_type' => 'code',
        'redirect_uri' => $redirect_uri,
        'scope' => $scope,
        'state' => $state,
      ];
      return $this->redirect($auth_url, ['query' => $auth_params]);
    }
  }

}
