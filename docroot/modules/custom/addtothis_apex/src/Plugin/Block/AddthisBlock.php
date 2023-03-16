<?php

namespace Drupal\addtothis_apex\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Controller\TitleResolverInterface;

/**
 * Provides an addthis block.
 *
 * @Block(
 *   id = "addtothis_apex_custom",
 *   admin_label = @Translation("AddThis"),
 *   category = @Translation("Custom")
 * )
 */
class AddthisBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Constructs a new ExampleBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, RequestStack $request_stack, TitleResolverInterface $title_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
    $this->titleResolver = $title_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('request_stack'),
      $container->get('title_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $url = Url::fromRoute('<current>', [], ['absolute' => 'true'])->toString();

    $request = $this->requestStack->getCurrentRequest();
    $title = $this->titleResolver->getTitle($request, $this->routeMatch->getRouteObject());

    $addthis_content = "<div class='addthis-containers'><div class='addthis_toolbox addthis_default_style addthis_32x32_style' addthis:url='" . $url . "' addthis:title='" . $title . "'>
    <a class='addthis_button_facebook'></a>
    <a class='addthis_button_twitter'></a>
    <a class='addthis_button_google_plusone_share'></a>
    <a class='addthis_button_pinterest_share'></a>

    <a class='addthis_button_compact'></a>
    <a class='addthis_counter addthis_bubble_style'></a>
    </div></div>
    ";
    $node = $this->routeMatch->getParameter('node');
    if ($node) {
      $typeName = $node->bundle();

      if ($typeName == 'product') {

        $addthis_content .= "
        <div class='addthis_toolbox addthis_default_style fb-like-campbell'>
        <a class='addthis_button_facebook_like at300b' fb:like:layout='button_count'></a>
        </div>";
      }
    }
    return [
      '#theme' => 'add_to_this_content',
      '#addthis_content' => $addthis_content,
    ];
  }

}
