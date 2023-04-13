<?php

namespace Drupal\cleco_vuejs\Utils;

use function array_intersect;
use Drupal;
use Drupal\cleco_vuejs\Utils\Configurations\AccessoriesConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\AdvancedDrillsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\AirMotorsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\ControllersAndSoftwareConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\ElectricTorqueWrenchesConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\FixturedSpindlesConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\GrindersConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\HandDrillingCountersinkingAndSpotfacingConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\ImpactWrenchesConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\LintPickerConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\NibblersConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\NutrunnersAndScrewdriversConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\PercussionConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\PulseToolsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\RivetingConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\RoutersConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\SandersAndPolishersConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\SawsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\ShearsAndScissorsConfiguration;
use Drupal\cleco_vuejs\Utils\Configurations\SpecialtyToolsConfiguration;
use Drupal\cleco_vuejs\Utils\StringHelper;
use Drupal\Core\Language\LanguageInterface;
use function in_array;
use function is_numeric;
use function array_search;
use function bcadd;

class StepHelper
{

    /**
     * @var string ElasticSearch index name
     */
    private $esIndexName;

    /**
     * Hardocded translations
     */
    private static $translations = [
        'Search for “@query”' => [
            'gb' => 'Search for “@query”',
            'de' => 'Suche nach “@query”'
        ],
        'Legacy' => [
            'gb' => 'Legacy',
            'de' => 'Bisherige Produkte'
        ],
        'Product Line' => [
            'gb' => 'Product Line',
            'de' => 'Produktreihe'
        ],
        'Product Category' => [
            'gb' => 'Product Category',
            'de' => 'Produktkategorie'
        ],
        'Engineering Drawings' => [
            'gb' => 'Engineering Drawings',
            'de' => 'Technische Zeichnungen'
        ],
        'Features' => [
            'gb' => 'Features',
            'de' => 'Merkmale'
        ],
        'Catalog' => [
            'gb' => 'Catalog',
            'de' => 'Katalog'
        ],
        'Data Sheet' => [
            'gb' => 'Data Sheet',
            'de' => 'Datenblatt'
        ],
        'DXF File' => [
            'gb' => 'DXF File',
            'de' => 'DXF Zeichnung'
        ],
        'Abrasive Capacity' => [
            'gb' => 'Abrasive Capacity',
            'de' => 'Schleifleistung'
        ],
        'Target Torque (NM)' => [
            'gb' => 'Target Torque (NM)',
            'de' => 'Drehmoment (Nm)'
        ],
        'Target Torque (ft-lbs)' => [
            'gb' => 'Target Torque (ft-lbs)',
            'de' => 'Drehmoment (ft-lbs)'
        ],
        'Assembly Tools' => [
            'gb' => 'Assembly Tools',
            'de' => 'Montagewerkzeuge'
        ],
        'Assembly Instruction' => [
            'gb' => 'Assembly Instruction',
            'de' => 'Montageanleitungen',
        ],
        'Material Removal' => [
            'gb' => 'Material Removal',
            'de' => 'Materialabtrag'
        ],
        'Drilling & Riveting'  => [
            'gb' => 'Drilling & Riveting',
            'de' => 'Bohren & Nieten'
        ],
        'Controllers & Software' => [
            'gb' => 'Controllers & Software',
            'de' => 'Controller & Software'
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
    ];

    /**
     * The type of downloads to include in the ES inex
     */
    public static function downloadTypes()
    {
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
    public static function productFilters()
    {
        return [
            // checkboxes UI
            'ATT728132' => [
                'type' => 'checkboxes'
            ],
            'ATT665405' => [
                'type' => 'checkboxes'
            ],
            'ATT663751' => [
                'type' => 'checkboxes'
            ],
            'ATT16694'  => [
                'type' => 'checkboxes'
            ],
            'ATT727466' => [
                'type' => 'checkboxes'
            ],
            'ATT668491' => [
                'type' => 'checkboxes'
            ],
            'ATT727184' => [
                'type' => 'checkboxes'
            ],
            'ATT420'    => [
                'type' => 'checkboxes'
            ],
            'ATT664420' => [
                'type' => 'checkboxes'
            ],
            'ATT665609' => [
                'type' => 'checkboxes'
            ],
            'ATT16692'  => [
                'type' => 'checkboxes'
            ],
            'ATT16691'  => [
                'type' => 'checkboxes'
            ],
            'ATT26930'  => [
                'type'   => 'checkboxes',
                'bucket' => 'Abrasive Capacity'
            ],
            'ATT26811'  => [
                'type'   => 'checkboxes',
                'bucket' => 'Abrasive Capacity'
            ],
            'ATT16674'  => [
                'type'   => 'checkboxes',
                'bucket' => 'Abrasive Capacity'
            ],
            'ATT663752' => [
                'type' => 'checkboxes'
            ],
            'ATT16696'  => [
                'type' => 'checkboxes'
            ],
            'ATT345'    => [
                'type' => 'checkboxes'
            ],
            'ATT17322'  => [
                'type' => 'checkboxes'
            ],
            'ATT16689'  => [
                'type' => 'checkboxes'
            ],
            'ATT26820'  => [
                'type' => 'checkboxes'
            ],
            'ATT727185' => [
                'type' => 'checkboxes'
            ],
            'ATT727183' => [
                'type' => 'checkboxes'
            ],
            'ATT675745' => [
                'type' => 'checkboxes'
            ],
            'ATT727457' => [
                'type' => 'checkboxes'
            ],
            'ATT835'    => [
                'type' => 'checkboxes'
            ],
            'ATT499'    => [
                'type' => 'checkboxes'
            ],
            'ATT670145' => [
                'type' => 'checkboxes'
            ],
            'ATT434'    => [
                'type'   => 'checkboxes',
                'bucket' => 'Features'
            ],
            'ATT727414' => [
                'type'   => 'checkboxes',
                'bucket' => 'Features'
            ],
            'ATT727431' => [
                'type'   => 'checkboxes',
                'bucket' => 'Features'
            ],
            'ATT583306' => [
                'type'   => 'checkboxes',
                'bucket' => 'Features'
            ],
            'ATT584487' => [
                'type'   => 'range',
                'bucket' => 'Target Torque (NM)'
            ],
            'ATT584486' => [
                'type'   => 'range',
                'bucket' => 'Target Torque (NM)'
            ],
            'ATT659132' => [
                'type'   => 'range',
                'bucket' => 'Target Torque (ft-lbs)'
            ],
            'ATT659133' => [
                'type'   => 'range',
                'bucket' => 'Target Torque (ft-lbs)'
            ],
            'ATT802'    => [
                'type' => 'range'
            ],
            'ATT384'    => [
                'type' => 'range'
            ],
            'ATT242'    => [
                'type' => 'range'
            ],
            'ATT583314' => [
                'type' => 'range'
            ],
            'ATT698650' => [
                'type' => 'checkboxes'
            ],
            'ATT670154' => [
                'type' => 'range'
            ],
            'ATT16699'  => [
                'type' => 'range'
            ],
            'ATT22563'  => [
                'type' => 'range'
            ],
            'ATT585085' => [
                'type' => 'range'
            ],
            'ATT16698'  => [
                'type' => 'range'
            ],
            'ATT584578' => [
                'type' => 'range'
            ],
            'ATT16688'  => [
                'type' => 'range'
            ],
            'ATT664087' => [
                'type' => 'range'
            ],
            'ATT664086' => [
                'type' => 'range'
            ]
        ];
    }

    /**
     * @param  array $array
     * @param  array $item
     * @param  int   $position
     * @return mixed
     */
    public static function insertAt(array $array = [], array $item = [], int $position = 0)
    {
        $previous_items = array_slice($array, 0, $position, true);
        $next_items     = array_slice($array, $position, null, true);

        return $previous_items + $item + $next_items;
    }

    /**
     * ElasticSearch Index Name
     *   - Gets name from Site handle
     *   - Converts Site handle to snake_case
     */
    public static function getEsIndexName(string $langCode = null)
    {
        $settings = self::getCurrentSite();
        $code     = is_null($langCode) ? $settings['code'] : $langCode;

        return strtolower(getenv('ES_PREFIX') . preg_replace('/(?<!\_)[A-Z]/', '_$0', lcfirst($settings['name']) . '_' . $code));
    }

    /**
     * Get Current Site Details
     */
    public static function getCurrentSite()
    {
        $config    = Drupal::config('system.site');
        $language  = Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
        $urlConfig = Drupal::config('language.negotiation')->get('url')['source']; // path_prefix vs domain

        return [
            'name'       => $config->get('name'),
            'code'       => $language,
            'domain'     => Drupal::request()->getHost(),
            'path'       => ($urlConfig == 'path_prefix') ? '/' . $language : '',
            'url_config' => Drupal::config('language.negotiation')->get('url')['source']
        ];
    }

    /**
     * Get Translated Product Filters
     */
    public static function getProductFilters()
    {
        $curSite = self::getCurrentSite();
        // Get all Product Filters from the first level
        $filters              = Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('product_filters', 0, 1);
        $collectionCheckboxes = [];
        $collectionRanges     = [];

        foreach ($filters as $filter) {
            $term = Drupal\taxonomy\Entity\Term::load($filter->tid);
            if ($term->hasTranslation($curSite['code'])) {
                // Only translate name of term
                // ElasticSearch relies on english keys
                $locale = $term->getTranslation($curSite['code']);
            }

            if (isset($term) && isset($locale)) {
                $type = self::getFieldValue($term, 'field_es_filter_type');
                $key  = self::getFieldValue($term, 'field_es_key');
                // Min/Max values may be unique to locale
                $min = self::getFieldValue($locale, 'field_es_range_min');
                $max = self::getFieldValue($locale, 'field_es_range_max');

                if (isset($key) && isset($type)) {
                    // Seperate arrays for ordering and add ranges to the end
                    if ($type == 'checkboxes') {
                        $collectionCheckboxes[] = [
                            'name' => $locale->get('name')->getValue()[0]['value'],
                            'key'  => $key,
                            'type' => $type,
                            'min'  => '',
                            'max'  => ''
                        ];
                    } else {
                        $collectionRanges[] = [
                            'name' => $locale->get('name')->getValue()[0]['value'],
                            'key'  => $key,
                            'type' => $type,
                            'min'  => $min,
                            'max'  => $max
                        ];
                    }
                }
            }
        }

        // Alpha order
        // usort($collectionCheckboxes, function ($a, $b) {
        //     return $a['name'] <=> $b['name'];
        // });

        // usort($collectionRanges, function ($a, $b) {
        //     return $a['name'] <=> $b['name'];
        // });

        $collection = array_merge($collectionCheckboxes, $collectionRanges);

        return $collection;
    }

    /**
     * Get attribute name and id relations
     * We want all ES keys to be in english for readiblity
     * Convert output to json /src/config/mappings/attributes.json
     * Uncomment StepHelper::indexDocuments();
     */
    public static function getAttributeRelations(\SimpleXMLElement $attributes)
    {
        $ref = [];

        foreach ($attributes->Attribute as $key => $attr) {
            $ref[(string) $attr->attributes()->ID] = (string) $attr->Name;
        }

        dump($ref);
        exit;
    }

    /**
     * @param  string $url
     * @return mixed
     */
    public static function fileExists(string $url)
    {
        $exists = false;

        if (strtoupper(getenv('ENV')) == 'PROD') {
            $exists = is_readable($url);
        } else {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            if ($result !== false) {
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                    $exists = true;
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
    public static function sortAssetsForDisplay(array $assets)
    {
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

        // Only include assets from the types list
        $assets = array_filter(
            $assets, function ($asset) use ($assetTypes) {
                return in_array($asset['type'], $assetTypes);
            }
        );

        // Sort by the order of the types list
        // @TODO was causing duplicate entries
        usort(
            $assets, function ($asset) use ($assetTypes) {
                $index = array_search($asset['type'], $assetTypes);
                return is_numeric($index) ? $index : count($assetTypes);
            }
        );

        // $assets_tmp = [];

        // foreach ($assets as $asset) {
        //     $assets_tmp[array_search($asset['type'], $assetTypes)] = $asset;
        // }

        // ksort($assets_tmp);
        if (!is_null($assets)) {
            ksort($assets);
        }

        // return array_values($assets_tmp);
        return array_values($assets);
    }

    /**
     * Normalize International Decimals
     *
     * @param $val
     * @param int $precision
     */
    public static function normalizeDecimal($val, int $precision = 4)
    {
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
     * @param array $product Product array.
     *
     * @return array
     */
    public static function getModelTableDefinition(array $product)
    {
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
            AccessoriesConfiguration::class,
            AdvancedDrillsConfiguration::class,
            AirMotorsConfiguration::class,
            ControllersAndSoftwareConfiguration::class,
            ElectricTorqueWrenchesConfiguration::class,
            FixturedSpindlesConfiguration::class,
            GrindersConfiguration::class,
            HandDrillingCountersinkingAndSpotfacingConfiguration::class,
            ImpactWrenchesConfiguration::class,
            LintPickerConfiguration::class,
            NibblersConfiguration::class,
            NutrunnersAndScrewdriversConfiguration::class,
            PercussionConfiguration::class,
            PulseToolsConfiguration::class,
            RivetingConfiguration::class,
            RoutersConfiguration::class,
            SandersAndPolishersConfiguration::class,
            SawsConfiguration::class,
            ShearsAndScissorsConfiguration::class,
            SpecialtyToolsConfiguration::class
            ]
        );

        return $definition->toArray();
    }

    public static function getProtocol()
    {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
        ) {
            return 'https://';
        } else {
            return 'http://';
        }
    }

    /**
     * Translate hardcoded STEP data and filters
     *
     * @param  string $str
     * @return string Translated or original string
     */
    public static function translate(string $str)
    {
        $langCode = self::getCurrentSite()['code'];

        if ($langCode !== 'en') {
            $searchArr = array_map('strtolower', array_keys(self::$translations));
            $copy      = array_change_key_case(self::$translations, CASE_LOWER);

            if (false !== $key = array_search(strtolower($str), $searchArr)) {
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

    public static function getTranslations()
    {
        return self::$translations;
    }

    // Private methods
    // =========================================================================

    /**
     * @param Drupal\taxonomy\Entity\Term $term
     * @param string                      $fieldName
     */
    private static function getFieldValue(Drupal\taxonomy\Entity\Term $term, string $fieldName)
    {
        $value = $term->get($fieldName)->getValue();

        return !empty($value) ? $value[0]['value'] : null;
    }
}
