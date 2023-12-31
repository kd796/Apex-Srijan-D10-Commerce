<?php

namespace Drupal\cleco_migrations\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drush\Log\LogLevel;
use Drush\Drush;

/**
 * A Drush commandfile.
 */
class ProductsUnPublishFamilyService extends ProductServices {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    $this->logger = Drush::logger();
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
   * @command cleco:products-family-unpublish
   * @aliases clpfu
   *
   * @return int
   *   The Drush code for success or failure.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \League\Flysystem\FilesystemException
   */
  public function cleanUpProducts(): int {
    try {
      $cmd_status = 0;
      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties([
          'type'   => 'product',
          'status'   => 1,
        ]);
      // Iterate the products and get the product models.
      foreach ($nodes as $node) {
        $nid = $node->id();
        $node_title = $node->title->value;
        $langcode = $node->get('langcode')->value;
        echo "\n langcode : $langcode\n";
        $fields = $node->getFields();
        $models = $fields['field_product_models']->getValue();
        $sku_group = (isset($fields['field_sku_group']) && !empty($fields['field_sku_group']->getValue())) ?
          $fields['field_sku_group']->getValue()[0]['value'] : (isset($fields['field_sku']) ? $fields['field_sku']->getValue()[0]['value'] : '');
        $all_unpublished = TRUE;
        // If more than one model for a product and
        // any of them are published.
        if (!empty($models) && count($models) > 0) {
          foreach ($models as $model) {
            $model_details = $this->entityTypeManager
              ->getStorage('node')->load($model['target_id']);
            // Check if all the models inside the product are unpublished.
            if ($model_details->isPublished()) {
              $all_unpublished = FALSE;
              break;
            }
          }
          if ($all_unpublished) {
            // All the models are unpublished.
            if ($node) {
              $node->setUnpublished(TRUE);
              $node->save();
              // Clear the entity cache.
              $this->entityTypeManager->getStorage('node')->resetCache([$nid]);
              \Drupal::logger(
                "All the product models under $sku_group are in unpublished state",
                LogLevel::SUCCESS
              );
              \Drupal::logger(
                "Successfully published the product $node_title \n SKU: $sku_group \n Node ID: $nid",
                LogLevel::SUCCESS
              );
            }
          }
          else {
            \Drupal::logger(
              "All/ Some of the product models are published under $sku_group family",
              LogLevel::SUCCESS
            );
          }
          // Success.
          $cmd_status = 0;
        }
      }
    }
    catch (\Exception $e) {
      // Failure.
      $cmd_status = 1;
      $this->logger->error('Drush command failure: ' . $e->getMessage());
      $this->output()->writeln('Drush command failure: ' . $e->getMessage());
    }
    return $cmd_status;
  }

}
