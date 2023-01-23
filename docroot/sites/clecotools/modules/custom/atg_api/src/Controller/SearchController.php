<?php

namespace Drupal\atg_api\Controller;

use Drupal\atg_api\Traits\HasGeographyParams;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Entity;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\Query;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchController extends ControllerBase
{
  use HasGeographyParams;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|null
   */
  protected $requestStack;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|null
   */
  protected $messenger;

  public $data = [];

  public $includes = [];

  protected $request;

  public function __construct()
  {
    $this->request = \Drupal::request();
  }

  /**
   * @param $index
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Drupal\search_api\SearchApiException
   */
  public function countrydataindex($index)
  {
    $index = Index::load($index);
    /** @var \Drupal\search_api\Query\Query $query */
    $query = $index->query();

    $code = $this->request->query->get('country');

    $query->addCondition("country_code", $code);

    $this->applyFilters($query);

    /** @var \Drupal\search_api\Query\ResultSet $results */
    $results = $query->execute();

    foreach ($results->getIterator() as $id => $item) {

      /** @var \Drupal\Core\Entity\Entity $entity */
      $entity = $item->getOriginalObject($id)->getValue();

      $processed_entity = $this->serializeEntity($entity);
      $this->injectEntityIncludesForCountrySearch($processed_entity);

      $this->data[] = $processed_entity;
    }

    return new JsonResponse($this->makeResponse());
  }

  protected function injectEntityIncludesForCountrySearch(&$processed_entity)
  {
    $includes = $this->request->query->get('include');

    if ($includes) {
      $includes = array_map('trim', explode(',', $includes));

      foreach ($includes as $include) {
        if (empty($include)) continue;

        $relationship = $processed_entity->relationships->{$include};
        if (is_array($relationship->data)) {
          $items = $relationship->data;
        } else {
          $items = [$relationship->data];
        }

        foreach ($items as $item) {
          if (empty($this->includes[$item->id])) {
            if(count($item) > 0 ) {
              $taxonomy = explode('--', $item->type);
              $taxonomy = array_shift($taxonomy);

              $entity = \Drupal::service('entity.repository')
                ->loadEntityByUuid($taxonomy, $item->id);
              if ($entity) {
                $this->includes[$item->id] = $this->serializeEntity($entity);
              }
            }
          }
        }
      }
    }
  }

  /**
   * @param $index
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Drupal\search_api\SearchApiException
   */
  public function index($index)
  {
    $index = Index::load($index);
    /** @var \Drupal\search_api\Query\Query $query */
    $query = $index->query();
    $this->applyGeoParameters($query);
    $this->applyFilters($query);

    /** @var \Drupal\search_api\Query\ResultSet $results */
    $results = $query->execute();

    foreach ($results->getIterator() as $id => $item) {
      /** @var \Drupal\Core\Entity\Entity $entity */
      $entity = $item->getOriginalObject($id)->getValue();

      $processed_entity = $this->serializeEntity($entity);
      $this->injectEntityIncludes($processed_entity);

      $distance                               = $this->getEntityDistance($entity);
      $processed_entity->attributes->distance = $distance ? $distance->toArray() : null;

      $this->data[] = $processed_entity;
    }

    return new JsonResponse($this->makeResponse());
  }

  /**
   * SERIALIZE ENTITY
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *
   * @return mixed
   */

  /*
     IMPORTANT: this is returning a type of Entity/Node, NOT just Entity
     */
  protected function serializeEntity($entity)
  {
    /** @var \Drupal\jsonapi\EntityToJsonApi $serializer */
    $serializer = \Drupal::service('jsonapi_extras.entity.to_jsonapi');
    $data       = json_decode($serializer->serialize($entity));

    return $data->data;
  }

  /**
   * APPLY FILTERS
   * Apply any filters passed in the query as conditions on the query.
   *
   * @param \Drupal\search_api\Query\Query $query
   */
  protected function applyFilters(Query &$query)
  {
    $filters = $this->request->query->get('filter');

    if ($filters) {
      foreach ($filters as $key => $value) {
        if ($value) {
          $query->addCondition($key, $value);
        }
      }
    }
  }

  protected function injectEntityIncludes(&$processed_entity)
  {
    $includes = $this->request->query->get('include');

    if ($includes) {
      $includes = array_map('trim', explode(',', $includes));

      foreach ($includes as $include) {
        if (empty($include)) continue;

        $relationship = $processed_entity->relationships->{$include};
        if (is_array($relationship->data)) {
          $items = $relationship->data;
        } else {
          $items = [$relationship->data];
        }

        foreach ($items as $item) {
          if (empty($this->includes[$item->id])) {
            $taxonomy = explode('--', $item->type);
            $taxonomy = array_shift($taxonomy);

            $entity = \Drupal::service('entity.repository')
              ->loadEntityByUuid($taxonomy, $item->id);
            if ($entity) {
              $this->includes[$item->id] = $this->serializeEntity($entity);
            }
          }
        }
      }
    }
  }

  /**
   * MAKE RESPONSE
   * Convert the data and includes into a response array
   *
   * @return array
   */
  protected function makeResponse()
  {
    $response         = [];
    $response['data'] = $this->data;

    if ($this->includes) {
      $response['included'] = array_values($this->includes);
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    /** @var static $controller */
    $controller = parent::create($container);

    $controller->setRequestStack($container->get('request_stack'));

    return $controller;
  }

  /**
   * Retrieves the request stack.
   *
   * @return \Symfony\Component\HttpFoundation\RequestStack
   *   The request stack.
   */
  public function getRequestStack()
  {
    return $this->requestStack ?: \Drupal::service('request_stack');
  }

  /**
   * Retrieves the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request|null
   *   The current request.
   */
  public function getRequest()
  {
    return $this->getRequestStack()->getCurrentRequest();
  }

  /**
   * Sets the request stack.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The new request stack.
   *
   * @return $this
   */
  public function setRequestStack(RequestStack $request_stack)
  {
    $this->requestStack = $request_stack;
    return $this;
  }
}
