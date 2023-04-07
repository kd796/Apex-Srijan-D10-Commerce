<?php

namespace Drupal\metatag_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Url;

/**
 * Implements MetaData import Script.
 */
class ImportMetaData extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_excel_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes'] = [
      'enctype' => 'multipart/form-data',
    ];
    $validators = [
      'file_validate_extensions' => ['csv'],
    ];
    $form['title_file'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload Meta Title Data file'),
      '#size' => 20,
      '#description' => t('CSV format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://content/excel_files/',
      '#required' => TRUE,
    ];
    $form['desc_file'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload Meta Desc Data file'),
      '#size' => 20,
      '#description' => t('CSV format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://content/excel_files/',
      '#required' => TRUE,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Trigger Upload'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      if ($form_state->getValue('title_file')) {
        $file_entity_id = current($form_state->getValue('title_file'));
        $upCsvFile = File::load($file_entity_id);
        $upCsvFile->setPermanent(1);
        $upCsvFile->save();
        $csv_parse = $this->getCsvById($file_entity_id);
      }
      if ($form_state->getValue('desc_file')) {
        $desc_id = current($form_state->getValue('desc_file'));
        $descCsvFile = File::load($desc_id);
        $descCsvFile->setPermanent(1);
        $descCsvFile->save();
        $desc_csv_parse = $this->getCsvById($desc_id);
      }
      $data = $this->getMergedMetaData($csv_parse, $desc_csv_parse);
      ImportMetaData::triggerBatch($data);
    }
    catch (\Exception $e) {
      \Drupal::logger('metatag_import')->error($e->getMessage());
    }
  }

  /**
   * Get CSV by id.
   *
   * @param int $id
   *   CSV id.
   *
   * @return array|null
   *   Parsed Csv
   */
  public function getCsvById(int $id) {
    $entity = $this->getCsvEntity($id);
    $return = [];
    if (($csv = fopen($entity->uri->getString(), 'r')) !== FALSE) {
      while (($row = fgetcsv($csv, 0, ',')) !== FALSE) {
        $return[] = $row;
      }
      fclose($csv);
    }
    return $return;
  }

  /**
   * Load CSV.
   *
   * @param int $id
   *   CSV id.
   *
   * @return \Drupal\file\Entity\File|null
   *   Entity object
   */
  public function getCsvEntity(int $id) {
    if ($id) {
      return $this->entityTypeManager->getStorage('file')->load($id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getMergedMetaData($meta_title_data, $meta_desc_data) {
    $data = [];
    foreach ($meta_title_data as $key => $value) {
      $url = $value[0];
      $meta_title = $value[1];
      $data[$url] = [
        'url' => $url,
        'meta_title' => $meta_title,
        'meta_desc' => "",
      ];
    }
    foreach ($meta_desc_data as $key => $value) {
      $url = $value[0];
      $meta_desc = $value[1];
      $data[$url] = [
        'url' => $url,
        'meta_title' => $data[$url]['meta_title'] ?? "",
        'meta_desc' => $meta_desc,
      ];
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public static function triggerBatch($data) {
    try {
      if (empty($data)) {
        \Drupal::messenger()->addWarning(t('The uploaded file contains no rows with compatible redirect data.'));
      }
      else {
        $batchBuilder = new BatchBuilder();
        $numOperations = 0;
        $batchId = 1;
        if (!empty($data)) {
          foreach ($data as $url => $_data) {
            $batchBuilder->addOperation([
              ImportMetaData::class,
              'importMetaData',
            ],
              [
                $_data,
              ]
            );
            $batchId++;
            $numOperations++;
          }
        }
        // 4. Create the batch.
        $batchBuilder
          ->setTitle('Importing Meta Data (Title and Desc) for Non PIM Content')
          ->setFinishCallback([ImportMetaData::class, 'importMetaDataFinished'])
          ->setErrorMessage(t('Batch has encountered an error'));
        // 5. Add batch operations as new batch sets.
        batch_set($batchBuilder->toArray());
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('metatag_import')->error($e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function importMetaData($data, &$context) {
    try {
      $alias = parse_url($data['url']);
      $path = \Drupal::service('path_alias.manager')->getPathByAlias($alias['path']);
      $params = [];
      $url = Url::fromUri("internal:" . $path);
      $routed = $url->isRouted();
      if ($routed) {
        $params = $url->getRouteParameters();
      }
      if (!empty($params)) {
        $entity_type = key($params);
        $title = $data['meta_title'] ? $data['meta_title'] : '';
        $desc = $data['meta_desc'] ? $data['meta_desc'] : '';
        $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($params[$entity_type]);
        $metatag_manager = \Drupal::service('metatag.manager');
        $existing_md = $metatag_manager->tagsFromEntity($entity);
        $default_md = $metatag_manager->tagsFromEntityWithDefaults($entity);
        $_title = !empty($title) ? $title : ($existing_md['title'] ?? $default_md['title']);
        $_desc = !empty($desc) ? $desc : ($existing_md['description'] ?? $default_md['description']);
        $entity->set('field_meta_tags', serialize([
          'title' => $_title,
          'description' => $_desc,
        ]));
        $entity->save();
        $context['results'][] = $path;
      }
      else {
        $redirects = \Drupal::entityTypeManager()
          ->getStorage('redirect')
          ->loadByProperties(['redirect_source__path' => self::stripLeadingSlash($path)]);
        if ($redirects) {
          \Drupal::messenger()->addWarning("Not Processed (Has a Redirect): " . $path);
        }
        else {
          \Drupal::messenger()->addWarning("Not Processed (Page not found): " . $path);
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('metatag_import')->error($e->getMessage());
    }
  }

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public static function importMetaDataFinished($success, array $results, array $operations) {
    if ($success) {
      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed...
      \Drupal::messenger()->addMessage(t('Processed @count items successfully.', [
        '@count' => count($results),
      ]));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      \Drupal::messenger()->addError(t('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected static function stripLeadingSlash($path) {
    if (strpos($path, '/') === 0) {
      return substr($path, 1);
    }
    return $path;
  }

}
