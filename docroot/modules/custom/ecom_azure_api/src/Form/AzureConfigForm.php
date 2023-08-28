<?php

namespace Drupal\ecom_azure_api\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The configuration form.
 */
class AzureConfigForm extends ConfigFormBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * The Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user's IP address.
   *
   * @var string
   */
  private $currentUserIp;

  /**
   * Constructs a AzureConfigForm object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The Module Handler service.
   * @param Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The RequestStack service.
   */
  public function __construct(AccountProxyInterface $currentUser,
    ModuleHandlerInterface $moduleHandler,
    RequestStack $requestStack
    ) {
    $this->currentUser = $currentUser;
    $this->moduleHandler = $moduleHandler;
    $this->currentUserIp = $requestStack->getCurrentRequest()->getClientIp();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('module_handler'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ecom_azure_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'ecom_azure_api.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ecom_azure_api.settings');

    $form['ecom_app_whitelist'] = [
      '#type' => 'details',
      '#title' => $this->t('Allowed IP Address List'),
      '#description' => $this->t('<strong>Your current IP address is %ip</strong>.
        It is %user-check  whitelisted.', [
          '%ip' => $this->currentUserIp,
          '%user-check' => $config->get('ecom_user_ip_whitelisted') ? '' : 'NOT',
        ]),
      '#open' => TRUE,
      '#collapsible' => TRUE,
    ];
    $form['ecom_app_whitelist']['ecom_address_list'] = [
      '#title' => $this->t('Allowed IP address list'),
      '#type' => 'textarea',
      '#default_value' => $config->get('ecom_address_list') ? implode(PHP_EOL, $config->get('ecom_address_list')) : '',
      '#description' => $this->t('Enter the list of IP Addresses that are allowed to access the site.
        Enter one IP address per line, in IPv4 format.'),
    ];
    $form['ecom_app_whitelist']['ecom_user_ip_whitelisted'] = [
      '#title' => $this->t('User is in whitelisted IP range'),
      '#type' => 'hidden',
      '#default_value' => $config->get('ecom_user_ip_whitelisted') ?: FALSE,
    ];
    $form['ecom_app_allowed_urls'] = [
      '#type' => 'details',
      '#title' => $this->t('Allowed URLs List'),
      '#description' => $this->t('List of URLs that are allowed to access'),
      '#open' => TRUE,
      '#collapsible' => TRUE,
    ];
    $form['ecom_app_allowed_urls']['ecom_page_whitelist'] = [
      '#title' => $this->t('Allowed URL list'),
      '#type' => 'textarea',
      '#default_value' => $config->get('ecom_page_whitelist') ?
      implode(PHP_EOL, $config->get('ecom_page_whitelist')) : '',
      '#description' => $this->t('Enter the list of URLs that are allowed to access the site. Enter one URL per line.'),
    ];
    $form['ecom_app_authorization'] = [
      '#type' => 'details',
      '#title' => $this->t('App Authorization'),
      '#open' => TRUE,
      '#collapsible' => TRUE,
    ];
    $form['ecom_app_authorization']['ecom_app_token_url'] = [
      '#title' => $this->t('Token URL'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $config->get('ecom_app_token_url'),
    ];
    $form['ecom_app_authorization']['ecom_app_auth_scope'] = [
      '#title' => $this->t('Scope'),
      '#type' => 'textfield',
      '#description' => $this->t("Scopes defined in azure, e.g. user.read, openid profile email"),
      '#default_value' => $config->get('ecom_app_auth_scope'),
    ];
    $form['ecom_app_authorization']['ecom_app_auth_grant'] = [
      '#title' => $this->t('Grant Type'),
      '#type' => 'select',
      '#options' => [
        'password' => $this->t('password'),
        'client_credentials' => $this->t('client_credentials'),
      ],
      '#default_value' => $config->get('ecom_app_auth_grant'),
    ];
    $form['ecom_app_authorization']['ecom_app_cache_expire'] = [
      '#title' => $this->t('Cache expiry'),
      '#type' => 'textfield',
      '#description' => $this->t("Drupal's Cache expiry time in milliseconds."),
      '#default_value' => $config->get('ecom_app_cache_expire') ?: 3599,
    ];
    $form['ecom_client_authorization'] = [
      '#type' => 'details',
      '#title' => $this->t('Client Authorization'),
      '#open' => TRUE,
      '#collapsible' => TRUE,
    ];
    $form['ecom_client_authorization']['ecom_client_token_url'] = [
      '#title' => $this->t('Token URL'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $config->get('ecom_client_token_url'),
    ];
    $form['ecom_client_authorization']['ecom_client_auth_scope'] = [
      '#title' => $this->t('Scope'),
      '#type' => 'textfield',
      '#description' => $this->t("Scopes defined in azure, e.g. user.read, openid profile email"),
      '#default_value' => $config->get('ecom_client_auth_scope'),
    ];
    $form['ecom_client_authorization']['ecom_client_auth_grant'] = [
      '#title' => $this->t('Grant Type'),
      '#type' => 'select',
      '#options' => [
        'password' => $this->t('password'),
        'client_credentials' => $this->t('client_credentials'),
      ],
      '#default_value' => $config->get('ecom_client_auth_grant'),
    ];
    $form['ecom_client_authorization']['ecom_graph_user_api'] = [
      '#title' => $this->t('Graph API URL'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $config->get('ecom_graph_user_api'),
    ];
    $form['ecom_secrets'] = [
      '#type' => 'details',
      '#title' => $this->t('App secrets'),
      '#open' => TRUE,
      '#collapsible' => TRUE,
    ];
    $form['ecom_secrets']['ecom_tenant'] = [
      '#title' => $this->t('Tenant ID'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $config->get('ecom_tenant'),
    ];
    $form['ecom_secrets']['ecom_client_id'] = [
      '#title' => $this->t('Client ID'),
      '#type' => 'password',
      '#description' => $this->t("Application ID from registered application.
       %so", [
         '%so' => $config->get('ecom_client_id') ?
         'Refilling this field will override existing value.' : '',
       ]),
      '#default_value' => $config->get('ecom_client_id'),
    ];
    $form['ecom_secrets']['ecom_client_secret'] = [
      '#title' => $this->t('Client Secret'),
      '#type' => 'password',
      '#default_value' => $config->get('ecom_client_secret'),
      '#description' => $this->t("Application ID from registered application.
      %r", [
        '%r' => $config->get('ecom_client_secret') ?
        'Refilling this field will override existing value.' : '',
      ]),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $ip_addresses = $this->cleanIpAddressInput($form_state->getValue('ecom_address_list'));
    $form_state->setValue('ecom_user_ip_whitelisted', FALSE);
    if (!empty($ip_addresses)) {
      foreach ($ip_addresses as $ip_address) {
        if ($ip_address != '::1') {
          // Check if IP address is a valid singular IP address
          // (ie - not a range):
          if (!preg_match('~^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$~', $ip_address) && !preg_match('~^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$~', $ip_address)) {
            // IP address is not a single IP address, so we need to check if
            // it's a range of addresses:
            $pieces = explode('-', $ip_address);
            // We only need to continue checking this IP address
            // if it is a range of addresses:
            if (count($pieces) == 2) {
              $start_ip = trim($pieces[0]);
              if (!preg_match('~^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$~', $start_ip)) {
                $form_state->setError($form['ecom_app_whitelist']['ecom_address_list'], $this->t('@ip_address is not a valid IP address.', ['@ip_address' => $start_ip]));
              }
              else {
                $start_pieces = explode('.', $start_ip);
                $start_final_chunk = (int) array_pop($start_pieces);
                $end_ip = trim($pieces[1]);
                $end_valid = TRUE;
                if (preg_match('~^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$~', $end_ip)) {
                  $end_valid = TRUE;
                  $end_pieces = explode('.', $end_ip);
                  for ($i = 0; $i < 3; $i++) {
                    if ((int) $start_pieces[$i] != (int) $end_pieces[$i]) {
                      $end_valid = FALSE;
                    }
                  }
                  if ($end_valid) {
                    $end_final_chunk = (int) array_pop($end_pieces);
                    if ($start_final_chunk > $end_final_chunk) {
                      $end_valid = FALSE;
                    }
                  }
                }
                elseif (!is_numeric($end_ip)) {
                  $end_valid = FALSE;
                }
                else {
                  if ($end_ip > 255) {
                    $end_valid = FALSE;
                  }
                  else {
                    $start_final_chunk = array_pop($start_pieces);
                    if ($start_final_chunk > $end_ip) {
                      $end_valid = FALSE;
                    }
                  }
                }

                if (!$end_valid) {
                  $form_state->setError($form['ecom_app_whitelist']['ecom_address_list'], $this->t('@range is not a valid IP address range.', ['@range' => $ip_address]));
                }
              }
              $clientIpValue = ip2long($this->currentUserIp);
              $lowValue = ip2long($start_ip);
              $highValue = ip2long($end_ip);
              if ($clientIpValue >= $lowValue && $clientIpValue <= $highValue) {
                $form_state->setValue('ecom_user_ip_whitelisted', TRUE);
              }
            }
            else {
              $form_state->setError($form['ecom_app_whitelist']['ecom_address_list'], $this->t('@ip_address is not a valid IP address or range of addresses.', ['@ip_address' => $ip_address]));
            }
          }
        }
      }
    }

    $page_whitelist = $form_state->getValue('ecom_page_whitelist');
    $page_whitelist = trim($page_whitelist);
    if (strlen($page_whitelist)) {
      $pages = [];
      $paths = explode(PHP_EOL, $page_whitelist);
      foreach ($paths as $path) {
        $path = trim($path);
        if (strlen($path)) {
          if (!preg_match('/^\//', $path)) {
            $path = '/' . $path;
          }

          $pages[] = strtolower($path);
        }
      }

      $form_state->setValue('ecom_page_whitelist', $pages);
    }
    else {
      $form_state->setValue('ecom_page_whitelist', []);
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $item) {
      if (substr($key, 0, 5) !== "ecom_") {
        continue;
      }
      if (in_array($key, ['ecom_client_id', 'ecom_client_secret']) && empty($item)) {
        $item = $this->config('ecom_azure_api.settings')->get($key);
        $form_state->setValue($key, $item);
      }
      if ($key == "ecom_address_list") {
        $this->config('ecom_azure_api.settings')
          ->set($key, $this->cleanIpAddressInput($item))
          ->save();
      }
      elseif ($key == "ecom_page_whitelist") {
        $this->config('ecom_azure_api.settings')
          ->set($key, (array) $item)
          ->save();
      }
      else {
        $this->config('ecom_azure_api.settings')
          ->set($key, (string) $item)
          ->save();
      }
    }
    parent::submitForm($form, $form_state);

  }

  /**
   * Helper function to clean IP address values.
   */
  private function cleanIpAddressInput($input) {
    $ip_addresses = trim($input);
    $ip_addresses = preg_replace('/(\/\/|#).+/', '', $ip_addresses);
    $ip_addresses = preg_replace('~/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/~', '', $ip_addresses);

    $addresses = explode(PHP_EOL, $ip_addresses);

    $return = [];
    foreach ($addresses as $ip_address) {
      $trimmed = trim($ip_address);
      if (strlen($trimmed)) {
        $return[] = $trimmed;
      }
    }

    return $return;
  }

}
