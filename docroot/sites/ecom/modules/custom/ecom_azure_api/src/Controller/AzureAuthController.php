<?php

namespace Drupal\ecom_azure_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for AzureAuthController.
 */
class AzureAuthController extends ControllerBase {

  /**
   * FormBuilder variable.
   *
   * @var string
   */
  protected $formBuilder;

  /**
   * Messenger to variable.
   *
   * @var string
   */
  protected $renderer;

  /**
   * Constructs a AzureAuthController object.
   */
  public function __construct(
    FormBuilderInterface $form_builder,
    RendererInterface $renderer) {
    $this->formBuilder = $form_builder;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('renderer')
    );
  }

  /**
   * Callback for API.
   */
  public function callback() {
    $form = $this->formBuilder->getForm('Drupal\ecom_azure_api\Form\PreLoginForm');
    $formMarkup = $this->renderer->render($form);

    return [
      '#markup' => $formMarkup,
    ];

  }

}
