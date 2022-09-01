<?php

namespace Drupal\apex_common\Commands;

use Drupal\Core\Config\Config;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\serialization\Encoder\XmlEncoder;
use Drupal\webform\Entity\WebformSubmission;
use Drush\Commands\DrushCommands;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\UnableToConnectToSftpHost;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Drush version agnostic commands.
 */
class ApexWarrantyExport extends DrushCommands {

  /**
   * The config object for the current module.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $apexConfig;

  /**
   * The FTP Root directory.
   *
   * @var string
   */
  protected string $root;

  /**
   * The FTP Connection.
   *
   * @var \League\Flysystem\PhpseclibV2\SftpConnectionProvider
   */
  protected SftpConnectionProvider $connection;

  /**
   * The FTP Adapter.
   *
   * @var \League\Flysystem\PhpseclibV2\SftpAdapter
   */
  protected SftpAdapter $adapter;

  /**
   * The filesystem class.
   *
   * @var \League\Flysystem\Filesystem
   */
  protected Filesystem $filesystem;

  /**
   * Initializes the ftp connection.
   */
  public function __construct() {
    parent::__construct();
    $this->getApexConfig();

    $sftp_host = $this->getApexConfig()->get('warranty_sftp_host');
    $sftp_username = $this->getApexConfig()->get('warranty_sftp_username');
    $sftp_password = $this->getApexConfig()->get('warranty_sftp_password');
    $this->root = $this->getApexConfig()->get('warranty_sftp_root');

    $this->connection = new SftpConnectionProvider(
      $sftp_host,
      $sftp_username,
      $sftp_password
    );

    $this->adapter = new SftpAdapter($this->connection, $this->root);

    // Load up our SFTP connection.
    $this->filesystem = new Filesystem($this->adapter);

    try {
      $this->connection->provideConnection();
    }
    catch (UnableToConnectToSftpHost $e) {
      $this->output()->writeln($e->getMessage());
      return;
    }
    catch (\Exception $e) {
      $this->output()->writeln($e->getMessage());
      return;
    }
  }

  /**
   * Load the warranty webform submission and upload to FTP.
   *
   * @command atg:warranty-export
   * @aliases atgwe
   */
  public function warrantyExport() {
    $submissions = $this->getLatestWebformSubmissions();
    $date = new \DateTime();
    $this->output()->writeln('Starting warranty export at ' . $date->format('Y-m-d H:i:s'));

    if (empty($submissions)) {
      $this->output()->writeln('No new submissions found.');
      return;
    }

    try {
      $converted_submissions = $this->convertSubmissions($submissions);
      $xml = $this->buildSubmissionXml($converted_submissions);
      $this->uploadToWarrantyFtp($xml);
    }
    catch (\Exception $e) {
      $this->output()->writeln($e->getMessage());
      return;
    }

    // Update the highest updated SID.
    $this->updateHighestExportedSid($submissions);
  }

  /**
   * Updates the highest exported SID.
   */
  protected function updateHighestExportedSid($submissions) {
    $highest_sid = $this->getHighestExportedSid();
    reset($submissions);

    $this->output()->writeln(
      'Updating highest exported SID from: ' . $highest_sid
    );

    /** @var \Drupal\Core\Entity\EntityInterface $submission */
    foreach ($submissions as $submission) {
      $id = (int) $submission->id();

      if ($id > $highest_sid) {
        $highest_sid = $id;
      }
    }

    $this->output()->writeln('To SID:' . $highest_sid);
    $this->getApexConfig()->set('warranty_highest_exported_sid', $highest_sid);
    $this->getApexConfig()->save();
  }

  /**
   * Gets the config object for the current module.
   */
  protected function getApexConfig() {
    if (empty($this->apexConfig)) {
      $this->apexConfig = \Drupal::configFactory()->getEditable('apex_common.settings');
    }

    return $this->apexConfig;
  }

  /**
   * Gets the last highest exported SID.
   */
  protected function getHighestExportedSid() {
    return (int) $this->getApexConfig()->get('warranty_highest_exported_sid');
  }

  /**
   * Gets the latest submissions.
   */
  protected function getLatestWebformSubmissions() {
    $this->output()->writeln('Getting the latest submissions...');
    $highest_exported_sid = $this->getHighestExportedSid();
    $query = $this->getSubmissionStorage()->getQuery()->condition(
      'webform_id',
      'warranty_replacement_form'
    );

    // Only the SIDs that come after the last highest exported SID.
    $query->condition('sid', $highest_exported_sid, '>');

    // Sort by created and sid in ASC or DESC order.
    $query->sort('created', isset($export_options['order']) ?? 'ASC');
    $query->sort('sid', isset($export_options['order']) ?? 'ASC');

    // Do not check access to submission since the exporter UI and Drush
    // already have access checking.
    // @see webform_query_webform_submission_access_alter()
    $query->accessCheck(FALSE);
    $entity_ids = $query->execute();

    return WebformSubmission::loadMultiple($entity_ids);
  }

  /**
   * Gets the storage object for webform submissions.
   */
  protected function getSubmissionStorage() {
    return \Drupal::entityTypeManager()->getStorage('webform_submission');
  }

  /**
   * Builds the XML from the submissions.
   */
  protected function buildSubmissionXml($submissions) {
    $this->output()->writeln('Generating XML...');
    $encoder = new XmlEncoder();
    $serializer = new Serializer([new GetSetMethodNormalizer()]);
    $encoder->setSerializer($serializer);

    return $encoder->encode($submissions, 'xml', [
      'xml_root_node_name' => 'dataroot',
    ]);
  }

  /**
   * Converts the style of all of the submissions.
   */
  protected function convertSubmissions($submissions) {
    $this->output()->writeln('Converting submissions to the clients format...');
    $data = [];

    foreach ($submissions as $webform_submission) {
      $submission_data = $webform_submission->toArray(TRUE, TRUE);
      $data['webform'][] = $this->wrfFormatConvert($submission_data);
    }

    return $data;
  }

  /**
   * Converts the array structure to the desired output format.
   */
  protected function wrfFormatConvert(array $data) {
    $allowed_fields = ['first_name', 'last_name', 'email_address',
      'phone_number', 'message',
    ];
    $address_fields = ['city', 'state_province', 'country', 'zip_code'];
    $new_data = [
      'wrf_vendor_code' => $this->getApexConfig()->get('warranty_vendor_code'),
      'wrf_item_number' => '',
    ];

    if (!empty($data['sid'])) {
      $new_data['wrf_sid'] = 'GW-' . $data['sid'];
    }

    foreach ($data['data'] as $field => $value) {
      if (in_array($field, $allowed_fields)) {
        $new_data['wrf_' . $field] = $value;
      }

      // We have to build a download link for the uploaded photo.

      // We have to retrieve the Product Number from the entity reference for the item number.
      if ($field == 'item_number' && !empty($value)) {
        $node = Node::load($value);

        if (is_object($node)) {
          $new_data['wrf_item_number'] = $node->getTitle();
        }
      }

      if ($field == 'product_image' && !empty($value)) {
        $file_entity = File::load($value);

        if (is_object($file_entity)) {
          $uri = $file_entity->createFileUrl(FALSE);
          $new_data['wrf_send_us_a_photo'] = $uri;
        }
      }

      if ($field == 'receipt_image' && !empty($value)) {
        $file_entity = File::load($value);

        if (is_object($file_entity)) {
          $uri = $file_entity->createFileUrl(FALSE);
          $new_data['wrf_receipt_image'] = $uri;
        }
      }

      if ($field == 'address') {
        foreach ($value as $add_field => $add_value) {
          if ($add_field == 'address') {
            $new_data['wrf_street_address'] = $add_value;
          }
          elseif ($add_field == 'country') {
            $new_data['wrf_country'] = 'US';

            if (!empty($add_value)) {
              $new_data['wrf_country'] = $add_value;
            }
          }
          elseif ($add_field == 'postal_code') {
            $new_data['wrf_zip_code'] = $add_value;
          }
          elseif (in_array($add_field, $address_fields)) {
            $new_data['wrf_' . $add_field] = $add_value;
          }
        }
      }
    }

    return $new_data;
  }

  /**
   * Uploads the XML file contents to the client's FTP server.
   */
  protected function uploadToWarrantyFtp($xml_contents) {
    if (!empty($xml_contents)) {
      $this->output()->writeln('XML content not empty');

      // Load config values.
      $host = $this->getApexConfig()->get('warranty_sftp_host');
      $root = $this->getApexConfig()->get('warranty_sftp_root');

      $this->output()->writeln('Connecting to host: ' . $host);
      $this->output()->writeln('Using file root: ' . $root);

      try {
        // Test the FTP connection.
        $this->connection->provideConnection();

        $dateObj = new \DateTime();
        $new_filename = $dateObj->format('Ymd-His') . '.xml';

        // Push that file.
        $this->filesystem->write($new_filename, $xml_contents);
        $this->output()->writeln('XML File has been sent to the server.');
      }
      catch (\Exception $e) {
        throw new \Exception('FTP Failure. Message: ' . $e->getMessage());
      }
    }
    else {
      $this->output()->writeln('Empty content.');
    }
  }

}
