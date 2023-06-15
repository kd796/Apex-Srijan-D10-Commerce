<?php

namespace Drupal\cleco_migrations\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drush\Log\LogLevel;

/**
 * A Drush commandfile.
 */
class ProductsCleanupService extends ProductServices {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs an ExampleClass object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The Product cleanup logger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * Creates an instance of the ExampleClass.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return \Drupal\your_module\ExampleClass
   *   The ExampleClass instance.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.channel'),
    );
  }

  /**
   * Unpublish the product family, When all the product models.
   *
   * Inside the product are unpublished.
   *
   * @command cleco:products-cleanup
   * @aliases clpc
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function cleanUpProducts(): int {
    try {
      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties([
          'type'   => 'product',
          // 'nid'   => $nid,
        ]);
      // Iterate the products and get the product models.
      foreach ($nodes as $node) {
        $nid = $node->id();
        $langcode = $node->get('langcode')->value;
        echo "\n langcode : $langcode\n";
        $fields = $node->getFields();
        $models = $fields['field_product_models']->getValue();
        $sku_group = (isset($fields['field_sku_group']) && !empty($fields['field_sku_group']->getValue())) ?
          $fields['field_sku_group']->getValue()[0]['value'] : (isset($fields['field_sku']) ? $fields['field_sku']->getValue()[0]['value'] : '');
        $all_unpublished = TRUE;
        foreach ($models as $model) {
          $model_details = $this->entityTypeManager
            ->getStorage('node')->load($model['target_id']);
          // Check if all the models inside the product are unpublished.
          if ($model_details->isPublished()) {
            $all_unpublished = FALSE;
          }
        }
        if ($all_unpublished) {
          // All the models are unpublished.
          // $node = Node::load($nid);
          $node = $this->entityTypeManager
            ->getStorage('node')
            ->load($nid);
          if ($node) {
            $node->setUnpublished(TRUE);
            $node->save();
            // Clear the entity cache.
            $this->entityTypeManager->getStorage('node')->resetCache([$nid]);
            drush_log(
              "All the products under $sku_group are in unpublished state",
              LogLevel::SUCCESS
            );
            drush_log(
              "Successfully unpublished the products under $sku_group family",
              LogLevel::SUCCESS
            );
            $this->logger('cleco_migrations')
              ->info("All the products under $sku_group are in unpublished state");
            $this->logger('cleco_migrations')
              ->info("Successfully unpublished the products under $sku_group family");
          }
        }
        else {
          drush_log(
            "All/ Some of the products are published under $sku_group family",
            LogLevel::SUCCESS
          );
          $this->logger('cleco_migrations')
            ->info("All/ Some of the products are published under $sku_group family");
        }
        // Success.
        $result = 0;
      }
    }
    catch (\Exception $e) {
      // Failure.
      $result = 1;
      $this->logger->error('Drush command failure: ' . $e->getMessage());
      $this->output()->writeln('Drush command failure: ' . $e->getMessage());
    }
    return $result;
  }

}
