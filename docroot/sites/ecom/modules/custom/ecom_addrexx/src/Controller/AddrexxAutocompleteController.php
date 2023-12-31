<?php

namespace Drupal\ecom_addrexx\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ecom_addrexx\AddrexxInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class AddrexxAutocompleteController extends ControllerBase {

  /**
   * Variable to hold addrexx object.
   *
   * @var \Drupal\ecom_addrexx\AddrexxInterface addrexx
   */
  protected $addrexx;

  /**
   * Class constructor.
   */
  public function __construct(AddrexxInterface $addrexx) {
    $this->addrexx = $addrexx;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ecom_addrexx.api'),
    );
  }

  /**
   * Autocomplete callback function.
   */
  public function autocompleteCallback(Request $request, $filter) {
    $string = $request->query->get('q');
    $contextKey = $request->query->get('contextKey');

    switch ($filter) {
      case "firstName":
        $suggestions = $this->addrexx->getbyFirstName($string);
        break;

      case "lastName":
        $suggestions = $this->addrexx->getbyLastName($string);
        break;

      case "zip":
        $suggestions = $this->addrexx->getbyZip($string);
        break;

      case "street":
        $suggestions = $this->addrexx->getbyStreet($string, $contextKey);
        $outputArray = [];
        // Remove numeric digits comes at end of street value.
        foreach ($suggestions as $input) {
          $output = preg_replace('/-\d+$/', '', $input);
          $outputArray[] = $output;
        }
        $suggestions = $outputArray;
        break;

      case "street2":
        $suggestions = $this->addrexx->getbyStreet2($string, $contextKey);
        $outputArray = [];
        // Remove numeric digits comes at end of apt value.
        foreach ($suggestions as $input) {
          $output = preg_replace('/-\d+$/', '', $input);
          $outputArray[] = $output;
        }
        $suggestions = $outputArray;
        break;
    }

    return new JsonResponse($suggestions);
  }

}
