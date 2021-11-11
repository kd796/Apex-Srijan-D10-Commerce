<?php

namespace Drupal\gearwrench_core\Plugin\WebformExporter;

use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformExporter\DocumentBaseWebformExporter;
use Drupal\webform\Plugin\WebformExporter\TabularBaseWebformExporter;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\serialization\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class XlsxExporter.
 *
 * @package Drupal\webform_xlsx_export\Plugin\WebformExporter
 *
 * @WebformExporter(
 *   id = "gwxml",
 *   label = @Translation("GW XML"),
 *   description = @Translation("Exports results as Gearwrench formatted XML file."),
 *   options = FALSE
 * )
 */
class GwXmlWebformExporter extends TabularBaseWebformExporter {

  /**
   * This is just the array of data collected with each pass.
   *
   * @var array
   */
  private $documentData = [];

  /**
   * {@inheritdoc}
   */
  public function getFileExtension() {
    return 'xml';
  }

  /**
   * {@inheritdoc}
   */
  public function closeExport() {
    $dest_directory = 'private://warrant_submissions';
    $filename = $this->getExportFileName();

    $encoder = new XmlEncoder();
    $serializer = new Serializer([new GetSetMethodNormalizer()]);
    $encoder->setSerializer($serializer);

    if (!empty($this->documentData)) {
      $xml = $encoder->encode($this->documentData, 'xml', [
        'xml_root_node_name' => 'dataroot',
      ]);

      if (!empty($xml)) {
        /** @var \Drupal\Core\File\FileSystem $fileService */
        $fileService = \Drupal::service('file_system');
        $fileService->prepareDirectory(
          $dest_directory,
          FileSystemInterface::CREATE_DIRECTORY
        );

        $fileService->saveData($xml, $dest_directory . '/' . $filename, FileSystem::EXISTS_REPLACE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function writeSubmission(WebformSubmissionInterface $webform_submission) {
    $submission_data = $webform_submission->toArray(TRUE, TRUE);
    $this->documentData['webform'][] = $this->wrfFormatConvert($submission_data);
  }

  /**
   * Converts the array structure to the desired output format.
   */
  protected function wrfFormatConvert(array $data) {
    $allowed_fields = ['first_name', 'last_name', 'email_address',
      'phone_number', 'message'
    ];
    $address_fields = ['city', 'state_province', 'country', 'zip_code'];
    $new_data = [
      'wrf_vendor_code' => 'APEX-GWW-POR',
    ];

    if (!empty($data['sid'])) {
      $new_data['wrf_sid'] = $data['sid'];
    }

    foreach ($data['data'] as $field => $value) {
      if (in_array($field, $allowed_fields)) {
        $new_data['wrf_' . $field] = $value;
      }

      // We have to build a download link for the uploaded photo.

      // We have to retrieve the Product Number from the entity reference for the item number.
      if ($field == 'item_number' && !empty($value)) {
        $node = Node::load($value);
        $new_data['wrf_item_number'] = $node->getTitle();
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

}
