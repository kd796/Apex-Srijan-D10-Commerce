<?php

namespace Drupal\cleco_vuejs\Utils;

use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\Entity\Term;
use Drupal\cleco_vuejs\Utils\Configurations\BitHoldersConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\BitsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\CustomSolutionsAndSpecialtyToolsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\ExtensionsAndAdaptersConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\SocketsAndUniversalWrenchesConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\UGuardTmAntiMarProtectiveCoversConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\UniversalJointsConfiguration;
use Drupal\Core\Language\LanguageInterface;
use function in_array;
use function is_numeric;
use function array_search;
use function bcadd;

/**
 *
 */
class StepHelper {

  /**
   * @var stringElasticSearchindexname
   */
  private $esIndexName;

  /**
   * Hardocded translations.
   */
  private static $translations = [
    'Search for “@query”' => [
      'gb' => 'Search for “@query”',
      'de' => 'Suche nach “@query”',
    ],
    'Legacy' => [
      'gb' => 'Legacy',
      'de' => 'Bisherige Produkte',
    ],
    'Product Line' => [
      'gb' => 'Product Line',
      'de' => 'Produktreihe',
    ],
    'Product Category' => [
      'gb' => 'Product Category',
      'de' => 'Produktkategorie',
    ],
    'Engineering Drawings' => [
      'gb' => 'Engineering Drawings',
      'de' => 'Technische Zeichnungen',
    ],
    'Features' => [
      'gb' => 'Features',
      'de' => 'Merkmale',
    ],
    'Catalog' => [
      'gb' => 'Catalog',
      'de' => 'Katalog',
    ],
    'Data Sheet' => [
      'gb' => 'Data Sheet',
      'de' => 'Datenblatt',
    ],
    'DXF File' => [
      'gb' => 'DXF File',
      'de' => 'DXF Zeichnung',
    ],
    'Abrasive Capacity' => [
      'gb' => 'Abrasive Capacity',
      'de' => 'Schleifleistung',
    ],
    'Target Torque (NM)' => [
      'gb' => 'Target Torque (NM)',
      'de' => 'Drehmoment (Nm)',
    ],
    'Target Torque (ft-lbs)' => [
      'gb' => 'Target Torque (ft-lbs)',
      'de' => 'Drehmoment (ft-lbs)',
    ],
    'Assembly Tools' => [
      'gb' => 'Assembly Tools',
      'de' => 'Montagewerkzeuge',
    ],
    'Assembly Instruction' => [
      'gb' => 'Assembly Instruction',
      'de' => 'Montageanleitungen',
    ],
    'Material Removal' => [
      'gb' => 'Material Removal',
      'de' => 'Materialabtrag',
    ],
    'Drilling & Riveting'  => [
      'gb' => 'Drilling & Riveting',
      'de' => 'Bohren & Nieten',
    ],
    'Controllers & Software' => [
      'gb' => 'Controllers & Software',
      'de' => 'Controller & Software',
    ],
    'Parts List' => [
      'gb' => 'Parts List',
      'de' => 'Ersatzteillisten',
    ],
    'User Guide' => [
      'gb' => 'User Guide',
      'de' => 'Benutzerhandbuch',
    ],
    'Homologation' => [
      'gb' => 'Homologation',
      'de' => 'Homologationen',
    ],
    'Flyer/Brochure' => [
      'gb' => 'Flyer/Brochure',
      'de' => 'Flyer/Broschüre',
    ],
    'Dimensional Diagram' => [
      'gb' => 'Dimensional Diagram',
      'de' => 'Maßzeichnung',
    ],
    'CE Documentation' => [
      'gb' => 'CE Documentation',
      'de' => 'CE Konformitätserklärung',
    ],
    'Operating Instructions' => [
      'gb' => 'Operating Instructions',
      'de' => 'Betriebsanleitungen',
    ],
    'Installation Manual' => [
      'gb' => 'Installation Manual',
      'de' => 'Installationshinweise',
    ],
    'Instruction Manual' => [
      'gb' => 'Instruction Manual',
      'de' => 'Bedienungsanleitung',
    ],
    'Line Art' => [
      'gb' => 'Line Art',
      'de' => 'Skizze',
    ],
    'Service Manual' => [
      'gb' => 'Service Manual',
      'de' => 'Service-Handbuch',
    ],
    'System Manual' => [
      'gb' => 'System Manual',
      'de' => 'Systemhandbuch',
    ],
    'Trouble Shooting' => [
      'gb' => 'Trouble Shooting',
      'de' => 'Fehlersuche',
    ],
    'Manual' => [
      'gb' => 'Manual',
      'de' => 'Handbuch',
    ],
    'Maintenance Instruction' => [
      'gb' => 'Maintenance Instruction',
      'de' => 'Wartungsanleitung',
    ],
    'Programming Manual' => [
      'gb' => 'Programming Manual',
      'de' => 'Programmieranleitung',
    ],
    'Quick Installation Guide' => [
      'gb' => 'Quick Installation Guide',
      'de' => 'Kurzanleitung',
    ],
    'MSDS' => [
      'gb' => 'MSDS',
      'de' => 'Sicherheitsdatenblätter',
    ],
    'Owners Manual' => [
      'gb' => 'Owners Manual',
      'de' => 'Bedienungsanleitung',
    ],
    '3d Model' => [
      'gb' => '3d Model',
      'de' => '3D Modelle',
    ],
    'Installation Note' => [
      'gb' => 'Installation Note',
      'de' => 'Installationshinweise',
    ],
    'Language' => [
      'gb' => 'Language',
      'de' => 'Sprache',
    ],
    'Turkish' => [
      'gb' => 'Turkish',
      'de' => 'Türkisch',
    ],
    'Spanish' => [
      'gb' => 'Spanish',
      'de' => 'Spanisch',
    ],
    'Slovakian' => [
      'gb' => 'Slovakian',
      'de' => 'Slowakisch',
    ],
    'Russian' => [
      'gb' => 'Russian',
      'de' => 'Russisch',
    ],
    'Romanian' => [
      'gb' => 'Romanian',
      'de' => 'Rumänisch',
    ],
    'Portuguese' => [
      'gb' => 'Portuguese',
      'de' => 'Portugiesisch',
    ],
    'Polish' => [
      'gb' => 'Polish',
      'de' => 'Polnisch',
    ],
    'Korean' => [
      'gb' => 'Korean',
      'de' => 'Koreanisch',
    ],
    'Japanese' => [
      'gb' => 'Japanese',
      'de' => 'Japanisch',
    ],
    'Italian' => [
      'gb' => 'Italian',
      'de' => 'Italienisch',
    ],
    'Hungarian' => [
      'gb' => 'Hungarian',
      'de' => 'Ungarisch',
    ],
    'German' => [
      'gb' => 'German',
      'de' => 'Deutsch',
    ],
    'French' => [
      'gb' => 'French',
      'de' => 'Französisch',
    ],
    'English' => [
      'gb' => 'English',
      'de' => 'Englisch',
    ],
    'Dutch' => [
      'gb' => 'Dutch',
      'de' => 'Niederländisch',
    ],
    'Danish' => [
      'gb' => 'Danish',
      'de' => 'Dänisch',
    ],
    'Czech' => [
      'gb' => 'Czech',
      'de' => 'Tschechisch',
    ],
    'Chinese' => [
      'gb' => 'Chinese',
      'de' => 'Chinesisch',
    ],
    'Tools' => [
      'gb' => 'Tools',
      'de' => 'Werkzeuge',
    ],
    'Solutions' => [
      'gb' => 'Solutions',
      'de' => 'Lösungen',
    ],
    'Article' => [
      'gb' => 'Article',
      'de' => 'Artikel',
    ],
    'Articles' => [
      'gb' => 'Articles',
      'de' => 'Artikel',
    ],
    'Accessories' => [
      'gb' => 'Accessories',
      'de' => 'Zubehör',
    ],
    'Software' => [
      'gb' => 'Software',
      'de' => 'Software',
    ],
    'Hardware Manual' => [
      'gb' => 'Hardware Manual',
      'de' => 'Hardware-Handbuch',
    ],
    'IGS File' => [
      'gb' => 'IGS File',
      'de' => 'IGS-Datei',
    ],
    'Certificates' => [
      'gb' => 'Certificates',
      'de' => 'Zertifikate',
    ],
    'Repair Manual' => [
      'gb' => 'Repair Manual',
      'de' => 'Reparaturanleitung',
    ],
    'STP File' => [
      'gb' => 'STP File',
      'de' => 'STP-Datei',
    ],
    'Warranty' => [
      'gb' => 'Warranty',
      'de' => 'Garantie',
    ],
    'Explore the Features' => [
      'gb' => 'Explore the Features',
      'de' => 'Übersicht der Funktionen',
    ],
    'Explore 360°' => [
      'gb' => 'Explore 360°',
      'de' => '360° Ansicht',
    ],
    'Cordless Electric / Pistol' => [
      'gb' => 'Cordless Electric / Pistol',
      'de' => 'Kabelloser Pistolenschrauber',
    ],
    'I-Wrench - Rubber Protection - WIFI Module - Auto Head Recognition' => [
      'gb' => 'I-Wrench - Rubber Protection - WIFI Module - Auto Head Recognition',
      'de' => 'I-Wrench - Gummischutz - WLan-Modul - Automatische Kopferkennung',
    ],
    'Drive Size' => [
      'gb' => 'Drive Size',
      'de' => 'Laufwerksgröße',
    ],
    'Magnetism' => [
      'gb' => 'Magnetism',
      'de' => 'Magnetismus',
    ],
    'Opening Modifier' => [
      'gb' => 'Opening Modifier',
      'de' => 'Öffnungsmodifikator',
    ],
    'Socket Type Length' => [
      'gb' => 'Socket Type Length',
      'de' => 'Länge des Sockeltyps',
    ],
    'Drive' => [
      'gb' => 'Drive',
      'de' => 'Antrieb',
    ],
    'Type' => [
      'gb' => 'Type',
      'de' => 'Typ',
    ],
    'Point Size' => [
      'gb' => 'Point Size',
      'de' => 'Punktgröße',
    ],
    'Hex Size' => [
      'gb' => 'Hex Size',
      'de' => 'Hex-Größe',
    ],
    'Shank Length' => [
      'gb' => 'Shank Length',
      'de' => 'Schaftlänge',
    ],
    'Male Square Drive (in)' => [
      'gb' => 'Male Square Drive (in)',
      'de' => 'Außenvierkantantrieb (in)',
    ],
    'Size' => [
      'gb' => 'Size',
      'de' => 'Größe',
    ],
  ];

  /**
   * The type of downloads to include in the ES inex.
   */
  public static function downloadTypes() {
    return [
      '3d Model'                 => [],
      'Assembly Instruction'     => [],
      'Catalog'                  => [],
      'CE Documentation'         => [],
      'Certificates'             => [],
      'Data Sheet'               => [],
      'Dimensional Diagram'      => [],
      'DXF File'                 => [],
      'Engineering Drawings'     => [],
      'Flyer/Brochure'           => [],
      'Hardware Manual'          => [],
      'Homologation'             => [],
      'IGS File'                 => [],
      'Installation Manual'      => [],
      'Instruction Manual'       => [],
      'Line Art'                 => [],
      'Legacy'                   => [],
      'Manual'                   => [],
      'Maintenance Instruction'  => [],
      'MSDS'                     => [],
      'Operating Instructions'   => [],
      'Parts List'               => [],
      'Programming Manual'       => [],
      'Quick Installation Guide' => [],
      'Repair Manual'            => [],
      'Service Manual'           => [],
      'Software'                 => [],
      'STP File'                 => [],
      'System Manual'            => [],
      'Trouble Shooting'         => [],
      'User Guide'               => [],
      'Warranty'                 => [],
      'Owners Manual'            => [],
      'Installation Note'        => [],
    ];
  }

  /**
   * Filter Mapping
   *   - Define what fields will be used for filters and aggregation
   *   - Creates terms for Filters Vocabulary
   */
  public static function productFilters() {
    return [
      'ATT835' => [ // Drive Size
        'type' => 'checkboxes'
      ],
      'ATT669761' => [ // Magnetism
        'type' => 'checkboxes'
      ],
      'ATT669756' => [ // Opening Modifier
        'type' => 'checkboxes'
      ],
      'ATT669755' => [ // Socket Type Length
        'type' => 'checkboxes'
      ],
      'ATT425' => [ // Type
        'type' => 'checkboxes'
      ],
      'ATT339' => [ // Point Size
        'type' => 'checkboxes'
      ],
      'ATT666136' => [ // Hex Size
        'type' => 'checkboxes'
      ],
      'ATT666181' => [ // Shank Length
        'type' => 'checkboxes'
      ],
      'ATT26835' => [ // Drive
        'type' => 'checkboxes'
      ],
      'ATT728148'=> [ // Male Square Drive (in)
        'type' => 'checkboxes'
      ],
      'ATT948' => [ // Size
        'type' => 'checkboxes'
      ],
    ];
  }

  /**
   * @param  array $array
   * @param  array $item
   * @param  int $position
   * @return mixed
   */
  public static function insertAt(array $array = [], array $item = [], int $position = 0) {
    $previous_items = array_slice($array, 0, $position, TRUE);
    $next_items     = array_slice($array, $position, NULL, TRUE);

    return $previous_items + $item + $next_items;
  }

  /**
   * ElasticSearch Index Name
   *   - Gets name from Site handle
   *   - Converts Site handle to snake_case
   */
  public static function getEsIndexName(string $langCode = NULL) {
    $settings = self::getCurrentSite();
    $code     = is_null($langCode) ? $settings['code'] : $langCode;

    return strtolower(getenv('ES_PREFIX') . preg_replace('/(?<!\_)[A-Z]/', '_$0', lcfirst($settings['name']) . '_' . $code));
  }

  /**
   * Get Current Site Details.
   */
  public static function getCurrentSite() {
    $config   = \Drupal::config('system.site');
    $language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    // path_prefix vs domain.
    $urlConfig = \Drupal::config('language.negotiation')->get('url')['source'];

    return [
      'name'       => $config->get('name'),
      'code'       => $language,
      'domain'     => \Drupal::request()->getHost(),
      'path'       => ($urlConfig == 'path_prefix') ? '/' . $language : '',
      'url_config' => $urlConfig,
    ];
  }

  /**
   * Get Translated Product Filters.
   */
  public static function getProductFilters() {
    $language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    // Get all Product Filters from the first level.
    $filters              = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('product_filters', 0, 1);
    $collectionCheckboxes = [];
    $collectionRanges     = [];

    $cache = \Drupal::cache();
    $cid_product_filter = __FUNCTION__ . '_' . $language;
    $product_filter_data_cached = $cache->get($cid_product_filter);
    if ($product_filter_data_cached) {
      return $product_filter_data_cached->data;
    }

    foreach ($filters as $filter) {
      $term = Term::load($filter->tid);
      if ($term->hasTranslation($language)) {
        // Only translate name of term
        // ElasticSearch relies on english keys.
        $locale = $term->getTranslation($language);
      }

      if (isset($term) && isset($locale)) {
        $type = self::getFieldValue($term, 'field_es_filter_type');
        $key  = self::getFieldValue($term, 'field_es_key');
        // Min/Max values may be unique to locale.
        $min = self::getFieldValue($locale, 'field_es_range_min');
        $max = self::getFieldValue($locale, 'field_es_range_max');

        if (isset($key) && isset($type)) {
          // Seperate arrays for ordering and add ranges to the end.
          if ($type == 'checkboxes') {
            $collectionCheckboxes[] = [
              'name' => $locale->get('name')->getValue()[0]['value'],
              'key'  => $key,
              'type' => $type,
              'min'  => '',
              'max'  => '',
            ];
          }
          else {
            $collectionRanges[] = [
              'name' => $locale->get('name')->getValue()[0]['value'],
              'key'  => $key,
              'type' => $type,
              'min'  => $min,
              'max'  => $max,
            ];
          }
        }
      }
    }

    // Alpha order
    // usort($collectionCheckboxes, function ($a, $b) {
    //     return $a['name'] <=> $b['name'];
    // });.
    // usort($collectionRanges, function ($a, $b) {
    //     return $a['name'] <=> $b['name'];
    // });.
    $collection = array_merge($collectionCheckboxes, $collectionRanges);

    // Add to cache.
    $product_filter_tags = ['config:taxonomy.vocabulary.product_filters', 'taxonomy_term_list:product_filters'];
    $cache->set($cid_product_filter, $collection, Cache::PERMANENT, $product_filter_tags);

    return $collection;
  }

  /**
   * Get attribute name and id relations
   * We want all ES keys to be in english for readiblity
   * Convert output to json /src/config/mappings/attributes.json
   * Uncomment StepHelper::indexDocuments();
   */
  public static function getAttributeRelations(\SimpleXMLElement $attributes) {
    $ref = [];

    foreach ($attributes->Attribute as $attr) {
      $ref[(string) $attr->attributes()->ID] = (string) $attr->Name;
    }
  }

  /**
   * @param  string $url
   * @return mixed
   */
  public static function fileExists(string $url) {
    $exists = FALSE;

    if (strtoupper(getenv('ENV')) == 'PROD') {
      $exists = is_readable($url);
    }
    else {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_NOBODY, TRUE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      $result = curl_exec($ch);

      if ($result !== FALSE) {
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
          $exists = TRUE;
        }
      }

      curl_close($ch);
    }

    return $exists;
  }

  /**
   * SORT ASSETS FOR DISPLAY
   * Sort an assets array by custom asset type order.
   *
   * @param array $assets
   *
   * @return array
   */
  public static function sortAssetsForDisplay(array $assets) {
    $assetTypes = [
      'Beauty-Glamour Image',
      'Primary Image',
      'Secondary Image',
      'SecondaryImage',
      'Part Shot 1',
      'Part Shot 2',
      'Part Shot 3',
      'Part Shot 4',
      'Part Shot 5',
      'Dimension Diagram',
    ];

    // Only include assets from the types list.
    $assets = array_filter(
          $assets, function ($asset) use ($assetTypes) {
              return in_array($asset['type'], $assetTypes);
          }
      );

    // Sort by the order of the types list.
    // @todo was causing duplicate entries
    usort(
          $assets, function ($asset) use ($assetTypes) {
              $index = array_search($asset['type'], $assetTypes);
              return is_numeric($index) ? $index : count($assetTypes);
          }
      );

    // $assets_tmp = [];
    // Foreach ($assets as $asset) {
    //     $assets_tmp[array_search($asset['type'], $assetTypes)] = $asset;
    // }.
    // ksort($assets_tmp);
    if (!is_null($assets)) {
      ksort($assets);
    }

    // Return array_values($assets_tmp);
    return array_values($assets);
  }

  /**
   * Normalize International Decimals.
   *
   * @param $val
   * @param int $precision
   */
  public static function normalizeDecimal($val, int $precision = 4) {
    $input  = str_replace(' ', '', $val);
    $number = str_replace(',', '.', $input);
    if (strpos($number, '.')) {
      $groups    = explode('.', str_replace(',', '.', $number));
      $lastGroup = array_pop($groups);
      $number    = implode('', $groups) . '.' . $lastGroup;
    }
    return bcadd($number, 0, $precision);
  }

  /**
   * GET MODEL TABLE DEFINITION
   * Dynamic function to set a single product's model comparison table columns.
   *
   * @param array $product
   *   Product array.
   *
   * @return array
   */
  public static function getModelTableDefinition(array $product) {
    $definition = new ComparisonTableDefinition($product, 'models');
    $definition->setColumnOffset(1);
    // $definition->addColumn('Model')->forKey('sku');
    $definition->addColumn('S.No')
      ->forKey('number');
    $definition->addColumn('Model')
      ->forKey('name');
    $definition->addPrimaryKey('model');
    $definition->addColumn('Top Seller')
      ->forKey('values.top_seller');

    $definition->registerConfigurations(
          [
            BitHoldersConfiguration::class,
            BitsConfiguration::class,
            CustomSolutionsAndSpecialtyToolsConfiguration::class,
            ExtensionsAndAdaptersConfiguration::class,
            SocketsAndUniversalWrenchesConfiguration::class,
            UGuardTmAntiMarProtectiveCoversConfiguration::class,
            UniversalJointsConfiguration::class,
          ]
      );

    return $definition->toArray();
  }

  /**
   *
   */
  public static function getProtocol() {
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || $_SERVER['SERVER_PORT'] == 443
      ) {
      return 'https://';
    }
    else {
      return 'http://';
    }
  }

  /**
   * Translate hardcoded STEP data and filters.
   *
   * @param string $str
   *
   * @return string Translated or original string.
   */
  public static function translate(string $str) {
    $langCode = self::getCurrentSite()['code'];

    if ($langCode !== 'en') {
      $searchArr = array_map('strtolower', array_keys(self::$translations));
      $copy      = array_change_key_case(self::$translations, CASE_LOWER);

      if (FALSE !== $key = array_search(strtolower($str), $searchArr)) {
        return $copy[$searchArr[$key]][$langCode];
      }
    }

    return $str;
  }

  /**
   * Get original translation for given translated string.
   *
   * @param string $str
   *   The string to be translated to original.
   * @param string $lang
   *   Language of the given str.
   *
   * @return string
   *   Translated or original string.
   */
  public static function getOriginalTranslation($str, $lang) {
    $lang_col = array_column(self::$translations, $lang);
    $found = array_search($str, $lang_col);
    if ($found === FALSE) {
      return $str;
    }
    $index = 0;
    foreach (self::$translations as $key => $val) {
      if ($found !== $index) {
        $index++;
        continue;
      }
      return $key;
    }
  }

  /**
   *
   */
  public static function getTranslations() {
    return self::$translations;
  }

  // Private methods
  // =========================================================================.

  /**
   * @param \Drupal\taxonomy\Entity\Term $term
   * @param string $fieldName
   */
  private static function getFieldValue(Term $term, string $fieldName) {
    $value = $term->get($fieldName)->getValue();

    return !empty($value) ? $value[0]['value'] : NULL;
  }

}
