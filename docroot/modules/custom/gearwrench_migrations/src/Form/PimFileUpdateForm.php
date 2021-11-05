<?php

namespace Drupal\gearwrench_migrations\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Provides a Gearwrench PIM dump upload form.
 */
class PimFileUpdateForm extends FormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The MIME type guesser.
   *
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a PimFileUpdateForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler instance to use.
   * @param \Symfony\Component\Mime\MimeTypeGuesserInterface $mime_type_guesser
   *   The theme manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, MimeTypeGuesserInterface $mime_type_guesser, FileSystemInterface $file_system) {
    $this->setConfigFactory($config_factory);

    $this->moduleHandler = $module_handler;
    $this->mimeTypeGuesser = $mime_type_guesser;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('file.mime_type.guesser'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gearwrench_migrations_pim_upload';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload and Replace'),
      '#button_type' => 'primary',
    ];

    $form['pim_upload'] = [
      '#type' => 'file',
      '#title' => t('Upload PIM Dump'),
      '#maxlength' => 40,
      '#description' => t("Upload the latest PIM Dump."),
      '#upload_validators' => [
        'gearwrench_migrations_file_validate_is_xml' => [],
        'file_validate_extensions' => [0 => 'xml']
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->moduleHandler->moduleExists('file')) {
      // Check for a new uploaded logo.
      if (isset($form['pim_upload'])) {
        $file = _file_save_upload_from_form($form['pim_upload'], $form_state, 0);

        if ($file) {
          // Put the temporary file in form_values so we can save it on submit.
          $form_state->setValue('pim_upload', $file);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the PIM file to a permanent location.
    $default_scheme = $this->config('system.file')->get('default_scheme');
    $filename = NULL;

    try {
      if (!empty($values['pim_upload'])) {
        $filename = $this->fileSystem->copy($values['pim_upload']->getFileUri(), $default_scheme . '://');
      }
      else {
        \Drupal::messenger()->addError('The pim upload is empty.');
      }
    }
    catch (FileException $e) {
      \Drupal::messenger()->addError('There was a problem copying the file.');
    }

    // If the user entered a path relative to the system files directory for
    // a logo or favicon, store a public:// URI so the theme system can handle it.
    if (!empty($filename)) {
      $filename = $this->validatePath($filename);
      \Drupal::messenger()->addStatus("Replacing with file: $filename");
      $destination = _gearwrench_migrations_clear_destination_and_pull_in_new($filename);

      if (!empty($destination)) {
        \Drupal::messenger()->addStatus('Upload was successful.');
      }
      else {
        \Drupal::messenger()->addError('There was a problem with moving the file.');
      }
    }
    else {
      \Drupal::messenger()->addError('There was a problem loading the file.');
    }
  }

  /**
   * Helper function for the system_theme_settings form.
   *
   * Attempts to validate normal system paths, paths relative to the public files
   * directory, or stream wrapper URIs. If the given path is any of the above,
   * returns a valid path or URI that the theme system can display.
   *
   * @param string $path
   *   A path relative to the Drupal root or to the public files directory, or
   *   a stream wrapper URI.
   *
   * @return mixed
   *   A valid path that can be displayed through the theme system, or FALSE if
   *   the path could not be validated.
   */
  protected function validatePath($path) {
    // Absolute local file paths are invalid.
    if ($this->fileSystem->realpath($path) == $path) {
      return FALSE;
    }

    // A path relative to the Drupal root or a fully qualified URI is valid.
    if (is_file($path)) {
      return $path;
    }

    // Prepend 'public://' for relative file paths within public filesystem.
    if (StreamWrapperManager::getScheme($path) === FALSE) {
      $path = 'public://' . $path;
    }

    if (is_file($path)) {
      return $path;
    }

    return FALSE;
  }

}
