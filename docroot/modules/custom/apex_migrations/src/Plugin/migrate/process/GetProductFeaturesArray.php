<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get Product Features Array.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_product_features_array"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_product_features_array
 *   source: text
 * @endcode
 */
class GetProductFeaturesArray extends ProcessPluginBase {

  /**
   * Array of feature attribute IDs and their position.
   *
   * @var array|int[]
   */
  protected static array $attributeToPosition = [
    'ATT100' => 0,
    'ATT101' => 1,
    'ATT102' => 2,
    'ATT103' => 3,
    'ATT104' => 4,
    'ATT105' => 5,
    'ATT106' => 6,
    'ATT107' => 7,
    'ATT108' => 8,
    'ATT109' => 9,
    'ATT22085' => 10,
    'ATT22086' => 11,
    'ATT22087' => 12,
    'ATT22088' => 13,
    'ATT22089' => 14,
    'ATT22090' => 15,
    'ATT22091' => 16,
    'ATT22092' => 17,
    'ATT22093' => 18,
    'ATT22094' => 19,
  ];

  /**
   * The array of the attribute IDs to focus on.
   *
   * @var array|int[]|string[]
   */
  protected array $attributeIdsToUse = [];

  /**
   * The array of final copy we return.
   *
   * @var array|int[]|string[]
   */
  protected array $copyArray = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Do this once instead on each call of the findFeatures() function.
    $this->attributeIdsToUse = array_keys(self::$attributeToPosition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->copyArray = [];

    if (!empty($value)) {
      /** @var \SimpleXMLElement $child */
      $this->findFeatures($value, $migrate_executable, $row);

      if (empty($this->copyArray)) {
        // Find the parent Product and try that one.
        $product = $value->xpath('parent::Product');

        if (!empty($product[0])) {
          $product = $product[0];

          // We should now be in the "Product" tag that is the actual product. Going for it's parent.
          $parentProductValues = $product->xpath('parent::Product/Values');
          $this->findFeatures($parentProductValues[0], $migrate_executable, $row);
        }
      }

      // This forces it to sort in the order we want them in.
      ksort($this->copyArray);
      $this->copyArray = json_encode($this->copyArray);
      return json_decode($this->copyArray, TRUE);
    }

    return $this->copyArray;
  }

  /**
   * Finds and stores the features list for the product. Checks product and group.
   *
   * @param \SimpleXMLElement|array|mixed $value
   *   The SimpleXMLElement used to navigate the data.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration executable.
   * @param \Drupal\migrate\Row $row
   *   The current row object.
   */
  protected function findFeatures(mixed $value, MigrateExecutableInterface $migrate_executable, Row $row) {
    foreach ($value->children() as $child) {
      $att_id = (string) $child->attributes()->AttributeID;

      if (in_array($att_id, $this->attributeIdsToUse)) {
        // Get the sorting position.
        $delta = self::$attributeToPosition[$att_id];

        if ($child->getName() !== 'MultiValue') {
          $data = (string) $child;
        }
        else {
          $data = (string) $child->Value;
        }

        if (strlen($data) >= 1000) {
          // The field has a string limit of 1,000 characters.
          $data = substr($data, 0, 1000);

          // We also need to report this as an issue.
          $sku = $row->getSourceIdValues()['remote_sku'];
          $migrate_executable->saveMessage("[Product Features Array] When importing Attribute ID $att_id for SKU $sku, we had to trim the field down to 1,000 characters to avoid errors.");
        }

        $this->copyArray[$delta] = [
          'copy_point' => $data,
        ];
      }
    }
  }

}
