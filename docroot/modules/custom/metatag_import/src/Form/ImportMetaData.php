<?php

namespace Drupal\metatag_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Utility\Environment;
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
   * Constructs Parser object.
   * 
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * Entity type manager service.
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
   * Pagecallback function addImportContentItemCallback to finished the batch.
   */
  public static function addImportContentItemCallback($success, $results, $operations) {
    if ($success) {
      $message = t('Successfully updated');
      \Drupal::messenger()->addMessage($message);
    }
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
  
    $validators = array(
      'file_validate_extensions' => array('csv'),
    );

    $form['title_file'] = array(
      '#type' => 'managed_file',
      '#title' => t('Title File *'),
      '#size' => 20,
      '#description' => t('CSV format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://content/excel_files/',
    );
    $form['desc_file'] = array(
      '#type' => 'managed_file',
      '#title' => t('Description File *'),
      '#size' => 20,
      '#description' => t('CSV format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://content/excel_files/',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Upload and Import'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * Get CSV by id.
   * 
   * @param int $id
   *   CSV id.
   * @return array|null
   *  Parsed Csv
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
   *  CSV id
   * 
   * @return \Drupal\file\Entity\File|null
   *  Entity object
   */
  public function getCsvEntity(int $id) {
    if ($id) {
      return $this->entityTypeManager->getStorage('file')->load($id);
    }
    return NULL;
  }

  /**
   *  {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if($form_state->getValue('title_file')) {
      $file_entity_id = current($form_state->getValue('title_file'));
      $upCsvFile = File::load($file_entity_id);
      $upCsvFile->setPermanent(1);
      $upCsvFile->save();
      $csv_parse = $this->getCsvById($file_entity_id);
    }
    if($form_state->getValue('desc_file')) {
      $desc_id = current($form_state->getValue('desc_file'));
      $descCsvFile = File::load($desc_id);
      $descCsvFile->setPermanent(1);
      $descCsvFile->save();
      $desc_csv_parse = $this->getCsvById($desc_id);
    }

    $data = $this->getMergedMetaData($csv_parse, $desc_csv_parse);
    ImportMetaData::triggerBatch2($data);
  }

  /**
   *  {@inheritdoc}
   */
  public function getMergedMetaData($meta_title_data, $meta_desc_data) {
    $data = [];
    foreach ($meta_title_data as $key => $value) {
      $url = $value[0];
      $meta_title = $value[1];
      $data[$url] = [
        'meta_title' => $meta_title,
        'meta_desc' => "",
      ];
    }
    foreach ($meta_desc_data as $key => $value) {
      $url = $value[0];
      $meta_desc = $value[1];
      $data[$url] = [
        'meta_title' => isset($data[$url]['meta_title']) ? $data[$url]['meta_title'] : "",
        'meta_desc' => $meta_desc,
      ];
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public static function triggerBatch2($data) {
    if (empty($data)) {
      \Drupal::messenger()->addWarning(t('The uploaded file contains no rows with compatible redirect data.'));
    }
    else {
      $batchBuilder = new BatchBuilder();
      $numOperations = 0;
      $batchId = 1;
      if (!empty($data)) {
        foreach ($data as $url => $_data) {
          $batchBuilder->addOperation([ImportMetaData::class, 'actionOnData2'], [$_data]);
          $batchId++;
          $numOperations++;
        }
      }
      // 4. Create the batch.
      $batchBuilder
        ->setTitle('Updating @num node(s)', ['@num' => $numOperations,])
        ->setFinishCallback([ImportMetaData::class, 'addImportContentItemCallback'])
        ->setErrorMessage(t('Batch has encountered an error'));
      // 5. Add batch operations as new batch sets.
      batch_set($batchBuilder->toArray());
    }
  }

  /**
   *  {@inheritdoc}
   */
  public static function actionOnData2($data) {
    $url = $data['url'];
    $title = $data['meta_title'] ? $data['meta_title']: '';
    $desc = $data['meta_desc'] ? $data['meta_desc']: '';
    $alias = parse_url($url);
    $path = \Drupal::service('path_alias.manager')->getPathByAlias($alias['path']);
    $params = [];
    $url = Url::fromUri("internal:" . $path);
    $routed = $url->isRouted();
    if ($routed) {
      $params = $url->getRouteParameters();
    }
    if (!empty($params)) {
      $entity_type = key($params);
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($params[$entity_type]);
      $entity->set('field_meta_tags', serialize([
        'title' => $title,
        'description' => $desc,
      ]));
      $entity->save();
    }
    else {
      $redirects = \Drupal::entityTypeManager()
        ->getStorage('redirect')
        ->loadByProperties(['redirect_source__path' => self::stripLeadingSlash($path)]);
      if ($redirects) {
        \Drupal::messenger()->addWarning(t("Not Processed (has a Redirect): " . $path));
      }
      else {
        \Drupal::messenger()->addWarning(t("Not Processed (Page not found): " . $path));
      }
    }
  }

  /**
   *  {@inheritdoc}
   */
  protected static function stripLeadingSlash($path) {
    if (strpos($path, '/') === 0) {
      return substr($path, 1);
    }
    return $path;
  }

}
