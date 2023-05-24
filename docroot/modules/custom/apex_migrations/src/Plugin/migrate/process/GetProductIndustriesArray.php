<?php

namespace Drupal\apex_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipProcessException;

/**
 * Get Product Industries Array.
 *
 * @MigrateProcessPlugin(
 *   id = "apex_get_product_industries"
 * )
 *
 * To get use the following:
 *
 * @code
 * field_text:
 *   plugin: apex_get_product_industries
 *   source: text
 * @endcode
 */
class GetProductIndustriesArray extends ProcessPluginBase {

  /**
   * The array of the attribute IDs to focus on.
   *
   * @var array|int[]|string[]
   */
  protected static array $attributeIdToUse = ['ATT814778'];

  /**
   * The array of industries we return.
   *
   * @var array|int[]|string[]
   */
  protected array $industriesArray = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->attributeIdToUse = self::$attributeIdToUse;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $vid = 'product_industry';
    $this->industriesArray = [];
    if (!empty($value)) {
      /** @var \SimpleXMLElement $child */
      $this->findProductIndustries($value, $migrate_executable, $row, $vid);

      if (empty($this->industriesArray)) {
        // Find the parent Product and try that one.
        $product = $value->xpath('parent::Product');
        if (!empty($product[0])) {
          $product = $product[0];

          // We should now be in the "Product" tag that
          // is the actual product. Going for it's parent.
          $parentProductValues = $product->xpath('parent::Product/Values');

          if (!empty($parentProductValues[0])) {
            $this->findProductIndustries($parentProductValues[0], $migrate_executable, $row, $vid);
          }
          else {
            throw new MigrateSkipProcessException();
          }
        }
      }
      $list = json_encode($this->industriesArray);
      return json_decode($list, TRUE);
    }
    return [];
  }

  /**
   * Finds and stores the industries for the product. Checks product and group.
   *
   * @param \SimpleXMLElement|array|mixed $value
   *   The SimpleXMLElement used to navigate the data.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration executable.
   * @param \Drupal\migrate\Row $row
   *   The current row object.
   * @param mixed $vid
   *   The Vocabulary that we need to focus on.
   */
  protected function findProductIndustries(mixed $value, MigrateExecutableInterface $migrate_executable, Row $row, string $vid) {
    if (!empty($value)) {
      foreach ($value->children() as $child) {
        $att_id = (string) $child->attributes()->AttributeID;
        if (in_array($att_id, $this->attributeIdToUse)) {
          if ($child->getName() !== 'MultiValue') {
            if ($tid = $this->getTidByName((string) $child, $vid)) {
              $this->industriesArray[] = $this->addToValues($vid, $tid);
            }
          }
          else {
            if (count($child->children()) > 0) {
              foreach ($child->children() as $item) {
                if ($tid = $this->getTidByName((string) $item, $vid)) {
                  $this->industriesArray[] = $this->addToValues($vid, $tid);
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * Common function to add values to array.
   *
   * @param string $vid
   *   The Vocabulary that we need to focus on.
   * @param mixed $tid
   *   Taxonomy term id.
   */
  protected function addToValues(string $vid, mixed $tid): array {
    $values_array = [
      'vid' => $vid,
      'target_id' => $tid,
    ];
    return $values_array;
  }

  /**
   * Load term by name.
   *
   * @param string $name
   *   The Vocabulary that we need to crosscheck on.
   * @param mixed $vocabulary
   *   Vocabulary Name.
   */
  protected function getTidByName($name = NULL, $vocabulary = NULL): int {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vocabulary)) {
      $properties['vid'] = $vocabulary;
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    return !empty($term) ? $term->id() : 0;
  }

}
