<?php

namespace Drupal\step\Breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use function json_last_error;

class StepProductCategoryBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() === 'step.products.product_single';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    /** @var Breadcrumb $easy_breadcrumbs */
    $easy_breadcrumbs = \Drupal::service('easy_breadcrumb.breadcrumb')->build($route_match);

    $product = $this->getProduct($route_match->getParameter('product'));
    if ($product) {
      $breadcrumbs = new Breadcrumb();

      // 1. Create a new Breadcrumbs instance with the path up to the current page
      $easy_breadcrumbs_links = $easy_breadcrumbs->getLinks();
      $easy_breadcrumbs_path  = array_slice($easy_breadcrumbs_links, 0, -1);
      $breadcrumbs->setLinks($easy_breadcrumbs_path);

      // 2. Inject the product category link
      $catalog_link = end($easy_breadcrumbs_path);
      $category_name = $product->product_category[0] ?? NULL;

      if ($category_name) {
        /** @var \Drupal\Core\Url $category_url */
        $category_url = clone $catalog_link->getUrl();
        $category_url->setOption('query', [
          // Pre-encode commas so categories with commas in the name aren't split into separate categories
          'product_category' => str_replace(',', '%2C', $category_name),
        ]);
        $category_link = Link::fromTextAndUrl($category_name, $category_url);

        $breadcrumbs->addLink($category_link);
      }

      // 3. Add back in the current page
      $breadcrumbs->addLink(end($easy_breadcrumbs_links));

      // 4. Add route context so breadcrumbs aren't cached globally
      $breadcrumbs->addCacheContexts(['route']);

      return $breadcrumbs;
    }

    return $easy_breadcrumbs;
  }

  /**
   * GET PRODUCT
   * Return the product object for the provided slug.
   *
   * @param string $slug Product slug from route parameters
   *
   * @return mixed|null
   */
  public function getProduct($slug) {
    $product = \Drupal::service('step.elastic_search_service')->getSingleProduct(['slug', $slug]);
    if ($product) {
      $product = json_decode($product);
      if (!json_last_error()) {
        return $product;
      }
    }

    return NULL;
  }
}
