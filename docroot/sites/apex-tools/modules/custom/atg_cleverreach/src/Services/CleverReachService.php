<?php

namespace Drupal\atg_cleverreach\Services;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\atg_cleverreach\Traits\LoggerTrait;
use Drupal\Component\Serialization\Json;

/*
 * https://github.com/anderl1/cleverreach/blob/master/Restclient.php
 */

class CleverReachService
{
  use LoggerTrait;

  /**
   * @var mixed
   */
  protected $api;
  /**
   * @var string
   */
  protected $apiKey;

  /**
   * @var mixed
   */
  protected $endpoint;

  /**
   * @var mixed
   */
  protected $clientId;

  /**
   * @var mixed
   */
  protected $password;

  /**
   * @var mixed
   */
  protected $login;

  /**
   * @return mixed
   */
  protected function getAuth()
  {
    $settings     = Drupal::config('atg_cleverreach.settings');
    $clientid     = $settings->get('cleverreach_client_id');
    $clientsecret = $settings->get('cleverreach_client_secret');

    // The official CleverReach URL, no need to change this.
    $token_url = 'https://rest.cleverreach.com/oauth/token.php';

    // This must be the same as the previous redirect uri
    $fields['grant_type'] = 'client_credentials';

    // We use curl to make the request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $token_url);
    curl_setopt($curl, CURLOPT_USERPWD, $clientid . ':' . $clientsecret);
    curl_setopt($curl, CURLOPT_POSTFIELDS, ['grant_type' => 'client_credentials']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);

    return Json::decode($result);
  }

  public function setup()
  {
    if (!isset($this->api)) {
      // @todo figure out autoload. Class is never found.
      require __DIR__ . '/../../lib/rest_client.php';

      $settings = Drupal::config('atg_cleverreach.settings');

      $this->api = new \CR\tools\rest($settings->get('cleverreach_api_endpoint'));

      $this->api->setAuthMode('bearer', $this->getAuth()['access_token']);
    }
  }

  /**
   * @param FormStateInterface $form_state
   */
  public function generateLead(FormStateInterface $form_state)
  {
    $this->setup();

    $listId = $form_state->getValue('cleverreach_id');
    $email  = $form_state->getValue('email');
    $language = $form_state->getValue('newsletter_language');
    $formId = $form_state->getValue('opt_in_form_id');

    $form_values = $form_state->getValues();

    $global_attributes_all = ['opt_in', 'last_name', 'first_name', 'company', 'phone', 'postal'];

    $global_attributes = [];
    $attributes        = [];

    foreach ($form_values as $key => $value) {
      if (is_array($value)) {
        $value = implode(', ', $value);
      }

      if (in_array($key, $global_attributes_all)) {
        $global_attributes[$key] = $value;
      } else {
        $attributes[$key] = $value;
      }
    }

    $user = [
      'email'             => $email,
      'registered'        => time(),
      'activated'         => 0,
      'source'            => 'Drupal Website',
      'global_attributes' => $global_attributes,
      'attributes'        => $attributes
    ];

    $activate_data = [
      'email' => $email,
      'doidata' => [
        'user_ip' => $_SERVER["REMOTE_ADDR"],
        'referer'    => $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"],
        'user_agent' => $_SERVER["HTTP_USER_AGENT"],
      ]
    ];


    // $this->log('Sending this object: ' . print_r($user, true), 'notice');
    $this->log('user object: ' . print_r($user, true), 'notice');

    try {
      $response = $this->api->post("/groups/{$listId}/receivers", $user);
      $this->log('Generated Lead: ' . $email, 'notice');

      // send opt-in email
      if ($response && $formId) {
        $email = $this->api->post("/forms/{$formId}/send/activate", $activate_data);
        $this->log('Telling API to send opt-in email: ' . print_r($activate_data, true), 'notice');
      }
    } catch (\Exception $e) {
      $this->log(print_r(Json::decode($e), true), 'error');

      $this->log(print_r(Json::decode($e->getMessage()), true), 'error');
    }
  }
}
