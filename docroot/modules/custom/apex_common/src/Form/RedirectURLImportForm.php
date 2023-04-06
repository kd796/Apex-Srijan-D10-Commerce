<?php

namespace Drupal\apex_common\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\apex_common\RedirectURLimporter;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RedirectImportForm handles the form.
 *
 * @package Drupal\apex_common\Form
 */
class RedirectURLImportForm extends FormBase {

  /**
   * Uploaded file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RedirectURLimport object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->setConfigFactory($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'RedirectURLImportForm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Import URLs from .csv file to add URL Redirects.'),
    ];
    $validators = [
      'file_validate_extensions' => ['csv'],
    ];
    $form['csv']['csv_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload CSV File'),
      '#description' => [
        '#theme' => 'file_upload_help',
        '#description' => $this->t('Only upload a CSV file, with the source urls.'),
      ],
      '#upload_validators' => $validators,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Trigger Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->file = file_save_upload('csv_file', $form['csv']['csv_file']['#upload_validators'], FALSE, 0);

    // Ensure we have the file uploaded.
    if (!$this->file) {
      $form_state->setErrorByName('csv_file', $this->t('You must add a valid file to the form in order to import redirects.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!isset($this->file)) {
      $this->messenger()->addWarning($this->t('No valid file was found. No redirects have been imported.'));
      return;
    }
    RedirectURLimporter::triggerBatch($this->file);

    // Remove file from Drupal managed files & from filesystem.
    $this->entityTypeManager->getStorage('file')->delete([$this->file]);

  }

}
