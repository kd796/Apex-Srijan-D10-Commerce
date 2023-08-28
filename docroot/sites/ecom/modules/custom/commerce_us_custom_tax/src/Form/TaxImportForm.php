<?php

namespace Drupal\commerce_us_custom_tax\Form;

use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to import tax types from a CSV file with territories.
 */
class TaxImportForm extends FormBase {

  /**
   * Uploaded file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;
  /**
   * The file_system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;
  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Construct TaxImportForm object.
   *
   * @param \Drupal\file\Entity\File $fileSystem
   *   The file system object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger.
   */
  public function __construct(FileSystem $fileSystem, LoggerChannelFactoryInterface $loggerFactory) {
    // $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $fileSystem;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ecom_tax_type_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $validators = [
      'file_validate_extensions' => ['csv'],
    ];

    $form['browser'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('TAX'),
      '#collapsible' => TRUE,
    ];

    $form['browser']['csv_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Tax File Upload'),
      '#description' => $this->t('Upload a CSV file containing tax data .'),
      '#required' => TRUE,
      '#upload_validators' => $validators,
    ];
    $form['sample_file'] = [
      '#markup' => '<p>' . $this->t('Download the sample tax data file:') . '<a href="/themes/custom/ecom/sample_tax_data.csv">sample_tax_data.csv</a></p>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import or Update Tax Types'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $this->file = file_save_upload('csv_file', $form['browser']['csv_file']['#upload_validators'], FALSE, 0);
    // Check file.
    if (!$this->file) {
      $form_state->setErrorByName('csv_file', $this->t('You must add a valid file to the form in order to import tax.'));
    }

    $filepath = $this->fileSystem->realpath($this->file->getFileUri());
    $tax_datas = $this->taxImportReadCsv($filepath);
    if ($tax_datas == NULL) {
      $form_state->setErrorByName('csv_file', $this->t('Upload the file in code,country,state,city,county,rate,postalcoderange format and upload a file with unique code'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $filepath = $this->fileSystem->realpath($this->file->getFileUri());
    $tax_datas = $this->taxImportReadCsv($filepath);

    if (!empty($tax_datas)) {
      // Set up the batch process.
      $batch = [
        'title' => $this->t('Importing Tax Types....'),
        'operations' => [],
        'finished' => '\Drupal\commerce_us_custom_tax\Form\TaxImportForm::taxImportFinished',
      ];
      // Chunk the tax  data into smaller batches to process.
      $chunks = array_chunk($tax_datas, 20);

      foreach ($chunks as $index => $chunk) {
        $batch['operations'][] = [
          '\Drupal\commerce_us_custom_tax\Form\TaxImportForm::taxImportProcessBatch',
        [$chunk],
        ];
      }
      // Start the batch process.
      batch_set($batch);
    }
    else {
      $this->messenger()->addError($this->t('Error reading the CSV file. Please check the format and try again.'));
    }

  }

  /**
   * Helper function to read tax data from a CSV file.
   *
   * CSV header should contains city,state,county and postalcoderange.
   */
  public function taxImportReadCsv($file_path) {

    // We should upload a csv file in this format.
    $header_format = ['code', 'country', 'state', 'city', 'county',
      'rate', 'postalcoderange',
    ];

    $tax_datas = [];
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
      $header = NULL;
      while (($data = fgetcsv($handle)) !== FALSE) {
        if (!$header) {
          // Assuming the first row contains headers, save them for reference.
          $header = $data;

          if ($header !== $header_format) {
            return NULL;
          }

        }
        else {
          // Map the columns using the header row.
          $tax_data = array_combine($header, $data);

          $tax_datas[] = $tax_data;
        }
      }
      fclose($handle);
    }
    return $tax_datas;
  }

  /**
   * Batch process callback for tax type import.
   */
  public static function taxImportProcessBatch($tax_datas, &$context) {

    foreach ($tax_datas as $tax_data) {

      $message = 'Importing US taxes';
      // Importing/updating tax to taxonomy.
      \Drupal::service('commerce_us_custom_tax.utility')->importTax($tax_data);
      $context['message'] = $message;
      $context['results'][] = $tax_data['code'];
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
  public static function taxImportFinished($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('Processed  @count taxes successfully.', [
        '@count' => count($results),
      ]));
    }
    else {
      \Drupal::messenger()->addMessage(t('An error occurred during the tax  import process.'), 'error');
    }
  }

}
