<?php

namespace Drupal\cleco_vuejs\Services;

use Prewk\XmlStringStreamer\Parser\StringWalker;
use stdClass;
use \SimpleXMLElement;

use Drupal;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\File\FileSystem;
use Drupal\Component\Serialization\Json;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

use Prewk\XmlStringStreamer;
use Prewk\XmlStringStreamer\Stream;
use Prewk\XmlStringStreamer\Parser;

use \Exception;
use \Drupal\Core\Database\IntegrityConstraintViolationException;

use Performance\Performance;

class StepService
{
    /**
     * @var array
     * Create translated ElasticSearch indices from XML attribute ContextID
     */
    private $locales = [
        'en' => 'EN-US All USA',
        'gb' => 'EN-US All USA',
        'de' => 'DE All DE',
        'es' => 'ES-MX All MX',
        'zh' => 'ZH-CN All China'
    ];

    /**
     * @var array
     * Attributes to ignore during indexing
     */
    private $excludeAttributes = [
        'ATT688984'
    ];

    /**
     * @var string
     * The langauge code to use when indexing data
     */
    private $langCode;

    /**
     * @var object
     * XML nodes to retrieve data from
     */
    private $xmlNode;

    /**
     * @var array
     * The documents that need to be updated in ElasticSearch
     */
    private $modelUpdates = [];

    /**
     * @var array
     * The options to search and filter data by
     */
    private $filters = [];

    /**
     * @var array
     * Cache XML node values
     */
    private $cachedAttr                   = [];
    private $cachedAttrLocale             = [];
    private $cachedClassification         = [];
    private $cachedClassificationAncestor = [];

    /**
     * @var array
     * Vocabulary taxonomy terms
     */
    private $productFilterVocab = [];

    /**
     * @var array
     * The STEP assets to index
     */
    private $indexDownloads = [];

    /**
     * @var array
     * Static list of all STEP attributes
     */
    private $attributesRef = [];

    /**
     * @var date $modified
     */
    private $modified;

    /**
     * @var array
     * Recursively stores all the values for Downloads doc's categories and product references
     */
    private $download = [];

    /**
     * @var array
     * The Asset key we want to to use for image styles
     */
    private $imageStylesAssetIds = [
        'source_to_jpg',
        'pro_tools_jpg_of_pdf'
    ];

    /**
     * @var array
     * Store the images to resize during bulk/delta imports
     *  - Thumb 285x350
     *  - Zoom Thumb 500x500
     */
    private $imageStylesPaths = [];

    /**
     * Create ES Downloads Index
     *
     * @param string            $type     Asset Type attribute, assists in excluding types not in StepHelper::downloadTypes()
     * @param \SimpleXMLElement $asset    The Asset node to get all the values from e.g. source_to_jpg
     * @param array             $line     The category from product loop
     * @param array             $category The category from product loop
     */
    private function buildDownloads(string $action, string $type, SimpleXMLElement $asset, SimpleXMLElement $product, $line = null, $category = null)
    {

        try {
            // AssetCrossReference @Type to include
            $includeTypes = StepHelper::downloadTypes();

            // Downloads are built from specific types
            if (array_key_exists($type, $includeTypes)) {
                $indexType = 'downloads';
                $index     = StepHelper::getEsIndexName($this->langCode) . '_' . $indexType;
                $uid       = (string) $asset->attributes()->ID;
                $doc       = new stdClass();
                // ES keys
                $doc->_action = $action;
                $doc->_index  = $index;
                $doc->_type   = $indexType;
                $doc->_id     = $uid;

                // Appends values to existing downloads document if it exist or adds new values
                // Must be called before other values
                if ($action == 'update') {
                    $doc->body['script'] = [
                        'lang'   => 'painless',
                        'source' => '
                        if (params.product_ref_name != null) {
                            for (int i = 0; i < params.line.length; ++i) {
                                if (!ctx._source.product_line.contains(params.line[i])) {
                                    ctx._source.product_line.add(params.line[i]);
                                }
                            }
                            for (int i = 0; i < params.category.length; ++i) {
                                if (!ctx._source.product_category.contains(params.category[i])) {
                                    ctx._source.product_category.add(params.category[i]);
                                }
                            }
                            for (int i = 0; i < ctx._source.product_ref.size(); ++i) {
                                if (!ctx._source.product_ref.name.contains(params.product_ref_name)) {
                                    ctx._source.product_ref.name.add(params.product_ref_name);
                                }
                                if (!ctx._source.product_ref.id.contains(params.product_ref_id)) {
                                    ctx._source.product_ref.id.add(params.product_ref_id);
                                }
                            }
                        }
                    ',
                        'params' => [
                            'line'             => $line ?? '',
                            'category'         => $category ?? '',
                            'product_ref_name' => (string) $product->Name ?? null,
                            'product_ref_id'   => (string) $product->attributes()->ID ?? null
                        ]
                    ];
                }

                // Download keys
                $doc->body['type']     = $type;
                $doc->body['id']       = $uid;
                $doc->body['name']     = (string) $asset->Name;
                $doc->body['modified'] = $this->modified;
                // Get all <Asset><AssetPushLocation>
                $doc->body['assets']   = $this->loopAssets($asset);
                // Loop <Values>
                $doc->body['values']   = $this->loopValues($asset);

                if ($action == 'index') {
                    // Recursively store all categories and prduct references
                    if (!is_null($line)) {
                        $this->download[$uid]['product_line'] = array_unique(array_merge($this->download[$uid]['product_line'] ?? [], $line));
                        $doc->body['product_line']            = $this->download[$uid]['product_line'];
                    }
                    if (!is_null($category)) {
                        $this->download[$uid]['product_category'] = array_unique(array_merge($this->download[$uid]['product_category'] ?? [], $category));
                        $doc->body['product_category']            = $this->download[$uid]['product_category'];
                    }
                    $this->download[$uid]['name']     = array_unique(array_merge($this->download[$uid]['name'] ?? [], [(string) $product->Name]));
                    $this->download[$uid]['id']       = array_unique(array_merge($this->download[$uid]['id'] ?? [], [(string) $product->attributes()->ID]));
                    $doc->body['product_ref']['name'] = $this->download[$uid]['name'];
                    $doc->body['product_ref']['id']   = $this->download[$uid]['id'];
                }
                // Only create array if at least one key has a value
                if (array_filter($doc->body['assets'])) {
                    $this->indexDownloads[] = $doc;
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in buildDownloads() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * @param  string
     * @param  string
     * @param  string
     * @return [type]
     */
    private function buildFiltersElasticSearch(string $attrID, string $attrName, string $value)
    {
        try {
            // If $attrID matches one of our Filters
            if (isset(StepHelper::productFilters()[$attrID])) {
                $value = $this->getValue($value, $attrName);
                // Skip our manipulated value if null
                if ($value !== null) {
                    // Check if attribute should be added to a predefined bucket
                    if (isset(StepHelper::productFilters()[$attrID]['bucket'])) {
                        $name = StepHelper::productFilters()[$attrID]['bucket'];
                    } else {
                        $name = $attrName;
                    }
                    // Add all filters to ElasticSearch
                    $key = StringHelper::createKey($name);
                    if (!isset($this->filters[$key])) {
                        $this->filters[$key] = [];
                    }
                    // Convert international decimals
                    $value = (StepHelper::productFilters()[$attrID]['type'] == 'range') ? StepHelper::normalizeDecimal($value, 2) : $value;
                    // Add unique filter values to filter type
                    if (!in_array($value, $this->filters[$key])) {
                        $this->filters[$key][] = $value;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in buildFiltersElasticSearch() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * @param string $attrID
     * @param string $reabableAttr
     * @param string $value
     * @param string $unitKey      Unit abbreviation key value
     */
    private function buildFiltersVocabulary(string $attrID, string $attrName, string $value, string $unitKey = '')
    {
        try {
            // If $attrID matches one of our Filters
            if (isset(StepHelper::productFilters()[$attrID])) {
                $value = $this->getValue($value, $attrName);
                // Skip our manipulated value if null
                if ($value !== null) {
                    // Check if attribute should be added to a predefined bucket
                    if (isset(StepHelper::productFilters()[$attrID]['bucket'])) {
                        // Use predefined bucket values for key and name
                        $name = StepHelper::productFilters()[$attrID]['bucket'];
                        $id   = 'N/A Created via code to implement custm bucket type.';
                        $key  = StringHelper::createKey($name);
                    } else {
                        $name = $attrName;
                        $id   = $attrID;
                        $key  = StringHelper::createKey($this->attributesRef[$attrID] . $unitKey) ?? StringHelper::createKey($name);
                    }

                    // Build Product Filters Vocabulary Terms
                    if (!isset($this->productFilterVocab[$name])) {
                        $this->productFilterVocab[$name]['config'] = [
                            'type' => StepHelper::productFilters()[$attrID]['type'],
                            'id'   => $id,
                            'key'  => $key
                        ];
                        // Store all values for filter type so we can later get min/max
                        $this->productFilterVocab[$name]['values'] = [];
                    }
                    // Convert international decimals
                    $value = (StepHelper::productFilters()[$attrID]['type'] == 'range') ? StepHelper::normalizeDecimal($value, 2) : $value;
                    // Add unique filter values to filter type
                    if (!in_array($value, $this->productFilterVocab[$name]['values'])) {
                        $this->productFilterVocab[$name]['values'][] = $value;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in buildFiltersVocabulary() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * @return mixed
     */
    private function buildProducts()
    {
        $indexType = 'products';
        $arr       = [];

        try {
            // Loop <Products><Product> parent
            if (!isset($this->xmlNode->products->Product) || (isset($this->xmlNode->products->Product) && !is_object($this->xmlNode->products->Product))) {
                $this->record('No products found in XML file.', 'error');
                return $arr;
            }

            foreach ($this->xmlNode->products->Product as $key => $product) {
                // Only create ES documents for SKU Groups
                // STEP XML nesting varies based on whether a SKU Group or SKU is approved
                // Delta feeds can have SKU Group and and SKUs on the same level and this causes issues with our loop
                if (strtolower($product->attributes()->UserTypeID) == 'sku group') {
                    $doc = new stdClass();
                    $uid = (string) $product->attributes()->ID;

                    if (isset($uid)) {
                        // ES keys
                        $doc->_action = 'index';
                        $doc->_index  = StepHelper::getEsIndexName($this->langCode) . '_' . $indexType;
                        $doc->_type   = $indexType;
                        $doc->_id     = $uid;
                        // Product keys
                        // @note name is overwritten with Coupon Headline in loopValues()
                        $doc->body['name'] = (string) trim(html_entity_decode(str_replace('&nbsp;', ' ', $product->Name)));
                        $doc->body['id']   = $uid;
                        $doc->body['type'] = 'Tools';
                        // @note slug is overwritten with Coupond Headline in loopValues()
                        $doc->body['slug']     = StringHelper::createSlug((string) $product->Name);
                        $doc->body['modified'] = $this->modified;
                        // Loop <ClassificationReference>
                        $this->loopClassifications($product, $doc);
                        // Loop <Values>
                        $doc->body['values'] = $this->loopValues($product, $doc);
                        // Loop <AssetCrossReference>
                        $this->loopAssetCrossReference($product, $doc);
                        // Get all product models <Products><Product><Product>
                        // @note This breaks with delta feeds and the approval process
                        $doc->body['models'] = $this->loopModels($product, $doc);
                        // Create Filters object to easily search data between product family and models
                        $doc->body['filters'] = $this->filters;
                        // @dev DO NOT DELETE. Creates MD file to display filters per product.
                        //echo '## ' . $product->Name . '<br>';
                        //print_r('```json <pre>' . json_encode($this->filters, JSON_PRETTY_PRINT) . '</pre>```');
                        //echo '<br>';
                        $this->filters = [];

                        // Incremental values
                        $arr[] = $doc;
                    }
                } else {
                    // Store SKUs for later use.
                    // Once the XML parsing is complete we'll udpate individual ES documents so we can
                    // nest the SKUs under the SKU Group
                    $this->modelUpdates[] = [
                        'product' => $product,
                        'doc_id'  => (string) $product->attributes()->ParentID
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in buildProducts() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }

        return $arr;
    }


    /**
     * Resize Images
     */
    private function createImageStyles()
    {
        try {
            // Prevent creating images locally
            if (strtoupper(getenv('ENV')) != 'PROD') {
                return;
            }

            foreach ($this->imageStylesPaths as $style => $indice) {
                $unique = array_unique($indice);
                foreach ($unique as $img) {
                    Drupal::service('step.image_styles_service')->resizeImg($img, $style);
                }
            }

            Drupal::service('step.image_styles_service')->createTermThumb($img);
        } catch (\Exception $e) {
            $this->record('Exception in createImageStyles() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * @param array  $fields
     * @param string $bundle
     */
    private function createTermField(string $bundle, array $fields)
    {
        try {
            foreach ($fields as $field) {
                // Prevent duplicated fields
                if (empty(FieldStorageConfig::loadByName('taxonomy_term', $field['name']))) {
                    $default = [
                        'field_name'             => $field['name'],
                        'langcode'               => $this->langCode,
                        'entity_type'            => 'taxonomy_term',
                        'type'                   => 'string',
                        'settings'               => [],
                        'module'                 => 'text',
                        'locked'                 => false,
                        'cardinality'            => 1,
                        'translatable'           => true,
                        'persist_with_no_fields' => false,
                        'custom_storage'         => false
                    ];

                    $params = array_merge($default, $field['params'] ?? []);

                    FieldStorageConfig::create($params)->save();
                }

                $this->enableTermFieldDisplays($bundle, $field);
            }
        } catch (\Exception $e) {
            $this->record('Exception in createTermField() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Creates Vocabularies Products and Product Filters
     *
     * @param string $vocab    Vocabulary name to create
     * @param string $descr    Vocabulary admin description
     * @param $tree     Taxonomy data
     * @param $callable CRUD taxonomy function
     */
    private function createVocabulary(string $vocab, string $descr, array $tree, callable $callable, array $fields = [])
    {
        try {
            $siteKeyHandle = StepHelper::getEsIndexName($this->langCode);

            $vid     = StringHelper::createKey($vocab);
            $results = Drupal::entityQuery('taxonomy_vocabulary')
                ->condition('vid', $vid)
                ->accessCheck(FALSE)
                ->execute();

            // Create vocabulary if it doesn't exist
            if (empty($results)) {
                Vocabulary::create(
                    [
                    'vid'                                   => $vid,
                    'name'                                  => !empty($vocab) ? ucwords($vocab) : 'null-vocab-' . uniqId(),
                    'description'                           => $descr,
                    'default_language[content_translation]' => true
                    ]
                )->save();
            }

            // Add fields to vocabulary
            if (!empty($fields)) {
                $this->createTermField($vid, $fields);
            }

            // Add Terms to Vocabulary
            call_user_func_array($callable, [$tree, $vid]);
        } catch (\Exception $e) {
            $this->record('Exception in createVocabulary() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Enable Field in Displays
     *
     * @param string $bundle
     * @param array  $field  [name, label, desc]
     */
    private function enableTermFieldDisplays(string $bundle, array $field)
    {
        try {
            if (empty(FieldConfig::loadByName('taxonomy_term', $bundle, $field['name']))) {
                // Add field to entity
                FieldConfig::create(
                    [
                    //'id'           => $id,
                    'field_name'   => $field['name'],
                    'entity_type'  => 'taxonomy_term',
                    'bundle'       => $bundle,
                    'label'        => $field['label'],
                    'description'  => $field['desc'] ?? '',
                    'translatable' => true
                    ]
                )->save();

                // View Display Default
                $form = EntityViewDisplay::load('taxonomy_term.' . $bundle . '.default');
                if (!$form) {
                    $form = EntityViewDisplay::create(
                        [
                        'targetEntityType' => 'taxonomy_term',
                        'bundle'           => $bundle,
                        'mode'             => 'default',
                        'status'           => true
                        ]
                    );
                    $form->save();
                }
                $form->setComponent($field['name'], ['label' => 'hidden'])->save();

                // Form Display Default
                $form = EntityFormDisplay::load('taxonomy_term.' . $bundle . '.default');
                if (!$form) {
                    $form = EntityFormDisplay::create(
                        [
                        'targetEntityType' => 'taxonomy_term',
                        'bundle'           => $bundle,
                        'mode'             => 'default',
                        'status'           => true
                        ]
                    );
                    $form->save();
                }
                $form->setComponent($field['name'])->save();
            }
        } catch (\Exception $e) {
            $this->record('Exception in enableTermFieldDisplays() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get/Cache Attribute Name for ElasticSearch English Keys
     * ElasticSearch will only contain keys in English
     * 51k+ keys, we don't want to look this value up every time
     *
     * @param  string $id The Attribute ID
     * @return mixed
     */
    private function getAttributeName(string $id)
    {
        try {
            if (isset($this->cachedAttr[$id])) {
                $value = $this->cachedAttr[$id];
            } else {
                //$value = (string) $this->xmlNode->attributes->xpath('/AttributeList//Attribute[@ID="' . $id . '"]')[0]->Name;
                $value = $this->attributesRef[$id] ?? null; // Always get english values

                $this->cachedAttr[$id] = $value;
            }

            return $value;
        } catch (\Exception $e) {
            $this->record('Exception in getAttributeName() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return $value;
        }
    }

    /**
     * Get/Cache Locale Attribute Name
     * Builds our Product Filters and ensures the name is translated properly
     *
     * @param  string $id The Attribute ID
     * @return mixed
     */
    private function getAttributeNameLocale(string $id)
    {
        try {
            if (isset($this->cachedAttrLocale[$id])) {
                $value = $this->cachedAttrLocale[$id];
            } else {
                $value = (string) $this->xmlNode->attributes->xpath('/AttributeList//Attribute[@ID="' . $id . '"]')[0]->Name;

                $this->cachedAttrLocale[$id] = $value;
            }

            return $value;
        } catch (\Exception $e) {
            $this->record('Exception in getAttributeNameLocale() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return $value;
        }
    }

    /**
     * Get Classification Ancestor for Product Line Category
     *
     * @param  string $id The Classification ID
     * @return mixed
     */
    private function getClassificationAncestor(string $id)
    {
        try {
            if (isset($this->cachedClassificationAncestor[$id])) {
                $value = $this->cachedClassificationAncestor[$id];
            } else {
                $value = (string) $this->xmlNode->classifications->xpath('//Classification[@ID="' . $id . '"]/ancestor::Classification[@UserTypeID="Website"]')[0]->Name;

                $this->cachedClassificationAncestor[$id] = $value;
            }

            return $value;
        } catch (\Exception $e) {
            $this->record('Exception in getClassificationAncestor() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return $value;
        }
    }

    /**
     * Get/Cache Classification Name
     *
     * @param  string $id              The Classification ID
     * @param  array  $productCategory
     * @return mixed
     */
    private function getClassificationName(string $id, array &$productCategory)
    {
        try {
            if (array_key_exists($id, $this->cachedClassification) && strtolower($this->cachedClassification[$id]['langcode']) == $this->langCode) {
                $value = $this->cachedClassification[$id]['value'];
                // Check if nested classification
                $this->getParentClassificationName($this->cachedClassification[$id]['user_type_id'], $id, $productCategory);
            } else {
                $classification = $this->xmlNode->classifications->xpath('//Classification[@ID="' . $id . '"]')[0];
                if (!empty($classification)) {
                    $value      = (string) $classification->Name;
                    $userTypeId = (string) $classification->attributes()->UserTypeID;

                    // add category name to caching, tagged with language code
                    $this->cachedClassification[$id] = [
                        'value'        => $value,
                        'user_type_id' => $userTypeId,
                        'langcode'     => $this->langCode
                    ];

                    // Check if nested classification
                    $this->getParentClassificationName($userTypeId, $id, $productCategory);
                }
            }

            return $value;
        } catch (\Exception $e) {
            $this->record('Exception in getClassificationName() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return $value;
        }
    }

    /**
     * Checks if Classification has parent
     *
     * @param  string $curLevel
     * @param  string $id
     * @param  $productCategory
     * @return mixed
     */
    private function getParentClassificationName(string $curLevel, string $id, array &$productCategory)
    {
        try {
            // Reverse recursively check for parent Classification until 'Web Level 1'
            // We don't want to go above the Product Line, which is 'Web Level 1'
            if ($curLevel !== 'Web Level 1') {
                // Get its parent Classification
                $parent = $this->xmlNode->classifications->xpath('//Classification[@ID="' . $id . '"]/parent::Classification')[0];
                if (!empty($parent)) {
                    $productCategory[] = (string) $parent->Name;
                    return $this->getClassificationName($parent->attributes()->ID, $productCategory);
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in getParentClassificationName() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return '';
        }
    }

    /**
     * @param  Drupal\Core\Entity\ContentEntityBase $entity
     * @return mixed
     */
    private function getTranslation(ContentEntityBase $entity)
    {
        try {
            if (!$entity->hasTranslation($this->langCode)) {
                $entity->addTranslation($this->langCode);
            }
            return $entity->getTranslation($this->langCode);
        } catch (\Exception $e) {
            $this->record('Exception in getTranslation() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Manipulates Final Value
     *   - Ensures Yes/No values are properly converted to the Attribute's name.
     *   - Removes unecessary filter options. e.g. Yes Vacuum / No Vacuum. The user simply will just select Vacuum for results.
     *   - Removes the duplicated values from PIM's text input e.g. non,no,no-
     *
     * @param  string $value
     * @param  string $reabableAttr
     * @return mixed
     */
    private function getValue(string $value, string $attrName)
    {
        try {
            $orig       = strtolower($value);
            $needlesNo  = ['non', 'no', 'non-', 'non -', 'no-', 'no -'];
            $needlesYes = ['has', 'yes', 'has-', 'has -', 'yes-', 'yes -'];

            foreach ($needlesYes as $needle) {
                if (substr($orig, 0, strlen($needle)) === $needle) {
                    $value = $attrName;
                }
            }

            foreach ($needlesNo as $needle) {
                if (substr($orig, 0, strlen($needle)) === $needle) {
                    $value = null;
                }
            }

            return $value;
        } catch (\Exception $e) {
            $this->record('Exception in getValue() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return $value;
        }
    }

    /**
     * Index Documents
     * Ran from Drupal's STEP form settings page
     * Full index of data, good for fixing health of index or large upates
     */
    public function indexBulk()
    {
        try {
            $locale = StepHelper::getCurrentSite()['code'];
            $xml    = File::load(Drupal::config('step.settings')->get('step_xml_' . $locale)[0])->getFileUri();

            $this->parseXML($xml);

            $this->record('Flushing Drupal cache (all) after Bulk import', 'notice');
            drupal_flush_all_caches();
        } catch (\Exception $e) {
            $this->record('Exception in indexBulk() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Index Data
     *   - Index ElasticSearch documents
     *   - Creates Vocabulary and Taxononies
     *   - Resizes images
     */
    private function indexData()
    {
        try {
            $serviceEs = Drupal::service('step.elastic_search_service');

            // @dev DO NOT DELETE
            // Simple function for getting static list of attributes. Refer to README.md in General Overview
            // StepHelper::getAttributeRelations($this->xmlNode->attributes);

            // Create Products Index
            $products = self::buildProducts();

            if (!$products) {
                $this->record('No SKU Groups (buildProducts) found in XML file.', 'notice');
            }

            if (!empty($products)) {
                $serviceEs->indexDocuments($products);
            }

            // Update models in document
            if (!empty($this->modelUpdates)) {
                $serviceEs->indexDocuments($this->updateModels());
            } else {
                $this->record('No SKUs (modelUpdates) found in XML file.', 'notice');
            }

            // Create Downloads Index
            if (!empty($this->indexDownloads)) {
                $serviceEs->indexDocuments($this->indexDownloads);
            }

            if (!isset($this->xmlNode->classifications) || (isset($this->xmlNode->classifications) && !is_object($this->xmlNode->classifications))) {
                $this->record('No classifications found in XML file.', 'error');
                return false;
            }

            // Create Vocabulary
            $classification = $this->xmlNode->classifications->xpath('/Classifications//Classification[@ID="Power Tools Web Hier Streamline"]/Classification[@UserTypeID="Website"]');

            if (!empty($classification)) {
                // Create Products Vocabulary
                // Creates Navigation, Product Line Vocab, Product Category Vocab
                $this->createVocabulary(
                    'Products',
                    'Vocabulary generated from STEP XML. Used for filters.',
                    $classification,
                    [$this, 'walkTermsClassifications'],
                    [
                        [
                            'name'   => 'field_es_desc',
                            'label'  => 'Description',
                            'params' => [
                                'type'     => 'string_long',
                                'settings' => [
                                    'max_length' => 5000,
                                    'is_ascii'   => false
                                ]
                            ]
                        ],
                        [
                            'name'  => 'field_es_id',
                            'label' => 'STEP AttributeID',
                            'desc'  => 'A unique ID that will be used programmatically.'
                        ],
                        [
                            'name'  => 'field_es_order',
                            'label' => 'Display Order'
                        ],
                        [
                            'name'  => 'field_es_image',
                            'label' => 'Image'
                        ]
                    ]
                );

                // Create Product Filters Vocabulary
                $this->createVocabulary(
                    'Product Filters',
                    'ElasticSearch filters and aggregation. Created from STEP.',
                    $this->productFilterVocab,
                    [$this, 'walkTermsFilters'],
                    [
                        [
                            'name'  => 'field_es_id',
                            'label' => 'STEP AttributeID',
                            'desc'  => 'A unique ID that will be used programmatically.'
                        ],
                        [
                            'name'  => 'field_es_filter_type',
                            'label' => 'Filter Type',
                            'desc'  => 'Sets front-end UI and search functionality.'
                        ],
                        [
                            'name'  => 'field_es_key',
                            'label' => 'ElasticSearch Key',
                            'desc'  => 'Keyname front-end uses to search fields.'
                        ],
                        [
                            'name'  => 'field_es_range_min',
                            'label' => 'Range Min',
                            'desc'  => 'Sets default value to the lowest value in the index.'
                        ],
                        [
                            'name'  => 'field_es_range_max',
                            'label' => 'Range Max',
                            'desc'  => 'Sets default value to the highest value in the index.'
                        ]
                    ]
                );

                $this->createImageStyles();
            }
        } catch (\Exception $e) {
            $this->record('Exception in indexData() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Recurring Delta Feed Imports
     * Cron runs every 24hrs at a specific time
     * Cron job set via crontrab, not Drupal
     */
    public function indexDelta()
    {
        $deltas     = [];
        $now        = time();
        $remotePath = 'ProductsImport/Queue/';

        /*
        Do we have credentials?
        */
        if (!getenv('DELTA_IP')) {
            $this->record('DELTA_IP is missing', 'error');
            return false;
        }

        if (!getenv('DELTA_USER')) {
            $this->record('DELTA_USER is missing', 'error');
            return false;
        }

        if (!getenv('DELTA_PASWD')) {
            $this->record('DELTA_PASWD is missing', 'error');
            return false;
        }

        $this->record('Running indexDelta(). Credentials are present.', 'notice');

        /*
        PHP's FTP functions are notoriously unreliable. Let's stop problems here first.
        */
        try {

            $this->record('Connecting to FTP server.', 'notice');
            $conn       = ftp_connect(getenv('DELTA_IP'));
            $this->record('Logging in to FTP server.', 'notice');
            $login      = ftp_login($conn, getenv('DELTA_USER'), getenv('DELTA_PASWD'));
            $this->record('Creating temporary directory.', 'notice');
            $tmpDir     = \Drupal::service('file_system')->getTempDirectory();
            $this->record('Directory path: ' . (string) $tmpDir, 'notice');
        } catch (\Exception $e) {
            $this->record('FTP setup failed: ' . $e->getMessage(), 'error');
            return false;
        }

        // Once successfully logged in, check if there's recent files with 24hrs from the time cron is ran
        // Cron runs once every 24hrs at 6am as of 11/07/18
        if ($login === true) {
            $this->record('FTP connected successfully. Checking recent files.', 'notice');
            try {

                $files = ftp_nlist($conn, $remotePath);
                $this->record('ftp_nlist reports ' . count($files) . ' total files (all, not zips) in: ' . $remotePath, 'notice');

                foreach ($files as $file) {

                    if (!is_dir($file) && pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
                        //$this->record('Examining '.$remotePath.$file, 'notice');
                        $time = ftp_mdtm($conn, $remotePath . $file);
                        if ($time === -1) {
                            continue; // couldn't get the file modified time
                        }

                        if (strtoupper(getenv('ENV')) == 'PROD') {
                            // Ignore files older than two hours
                            $window = '2hrs';
                            $difference = ($now - $time) < 7200 ? 1 : -1;
                        } else {
                            // Ignore files older than one day
                            $window = '24hrs';
                            $difference = floor(($now - $time) / 1000 / 60);
                        }
                        // 0 = File modified today
                        // 1 = File modified within 24hrs
                        if ($difference >= 0 && $difference <= 2) {
                            $deltas[] = $remotePath . $file;
                            $this->record('ADDED ' . $file . ' to target list (modified within ' . $window . ').', 'notice');
                        } else {
                            //$this->record('Ignoring '.$file);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->record('Error checking recent files: ' . $e->getMessage(), 'error');
            }
        } else {
            $this->record('Delta Feed FTP login failed.', 'error');
        }

        // Log if no delta file
        if (empty($deltas)) {
            $this->record('No recent delta file to import (deltas array is empty).', 'notice');
        } else {
            $this->record(count($deltas) . ' Delta files present.', 'notice');
        }

        // Create temporary dir if it doesn't exist
        if (!file_destination($tmpDir, FILE_EXISTS_ERROR) === false) {
            $this->record('Temporary directory does not exist. Attempting to re-create it.', 'notice');
            Drupal::service('file_system')->mkdir($tmpDir, 0775);
        }

        // check we have our tmp directory
        if (!file_exists($tmpDir) || !is_dir($tmpDir) || !is_writable($tmpDir)) {
            $this->record('Temp directory does not exist or is not writeable', 'error');
            return false;
        }

        // Declare the array
        $localDeltas = [];

        // Loop through all recent files and create local documents
        $this->record('Attempting to loop recent delta files.', 'notice');
        foreach ($deltas as $file) {
            $this->record('Looping ' . (string) $file, 'notice');
            try {
                $localFile = $tmpDir . '/' . basename($file);
                // Get the remoute file
                $this->record('Downloading (FTP) zip (' . (string) $file . ') --> ' . (string) $localFile, 'notice');
                if (ftp_get($conn, $localFile, $file, FTP_BINARY)) {
                    $this->record('Successfully retrieved file.', 'notice');

                    $zip = new \ZipArchive();
                    $this->record('Unzipping...', 'notice');

                    if ($zip->open($localFile)) {
                        $xml = $zip->getNameIndex(0);
                        $zip->extractTo($tmpDir);
                        $zip->close();
                        $localDeltas[] = $tmpDir . '/' . $xml;
                        $this->record('Delta Feed ' . basename($file) . ' downloaded.', 'info');
                    } else {
                        $this->record('Unable to open zip.');
                    }
                } else {
                    $this->record('Unable to retrieve zip file via FTP (ftp_get).');
                }
            } catch (\Exception $e) {
                $this->record('Error looping through delta zip files: ' . $e->getMessage(), 'error');
            }
        }

        if ($conn) {
            $this->record('Closing FTP connection.', 'notice');
            ftp_close($conn);
        }

        // Sort chronologically
        // This way an older file, potentially minutes apart doesn't overrite the newest
        if ($localDeltas && is_array($localDeltas) && count($localDeltas)) {
            $this->record('Sorting localDeltas', 'notice');
            usort(
                $localDeltas, function ($a, $b) {
                    return filemtime($a) < filemtime($b);
                }
            );

            // Finally, parse XML
            foreach ($localDeltas as $file) {
                try {
                    $this->record('Attempting to parse XML: ' . (string) $file, 'notice');
                    $this->parseXML($file, true);
                } catch (\Exception $e) {
                    $this->record('Error parsing delta XML file: ' . $e->getMessage(), 'error');
                }
            }
        } else {
            $this->record('localDeltas array is empty.', 'error');
        }

        $this->record('Flushing Drupal cache (all) after Delta import', 'notice');
        drupal_flush_all_caches();
    }

    /**
     * Loop Assets
     * <Assets>
     *
     * @param  \SimpleXMLElement $asset
     * @param  array             $includeTypes If we wanted to limit the Assets in the doc
     * @return mixed
     */
    private function loopAssets(SimpleXMLElement $asset, array $includeTypes = [])
    {
        $arr = [];
        try {

            if (isset($asset->AssetPushLocation)) {
                foreach ($asset->AssetPushLocation as $assetLoc) {
                    $id  = (string) $assetLoc->attributes()->ConfigurationID;
                    $key = StringHelper::createKey($id);

                    // If asset value isn't empty
                    if (isset($assetLoc) && $assetLoc != '') {
                        $basename = (string) basename($assetLoc);
                        // Build our image styles array for image resizing
                        // We'll create thumbs for products and downloads
                        // Only create product_zoom_thumb for products
                        if (in_array($key, $this->imageStylesAssetIds)) {
                            if ($key == 'source_to_jpg') {
                                $this->imageStylesPaths['product_zoom_thumb'][] = $basename;
                            }
                            $this->imageStylesPaths['product_thumb'][] = $basename;
                        }

                        if (!empty($includeTypes) && in_array($id, $includeTypes)) {
                            $arr[$key] = $basename;
                        } else {
                            $arr[$key] = $basename;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in loopAssets() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }
        return $arr;
    }

    /**
     * @param \SimpleXMLElement $product
     * @param stdClass          $doc
     */
    private function loopAssetCrossReference(SimpleXMLElement $product, &$doc, $isModel = false, $action = 'index')
    {
        try {
            if (isset($product->AssetCrossReference)) {
                foreach ($product->AssetCrossReference as $assetRef) {
                    $assetRefAttr = $assetRef->attributes();
                    $asset        = $this->xmlNode->assets->xpath('/Assets/Asset[@ID="' . (string) $assetRefAttr->AssetID . '"]')[0];
                    if (!empty($asset)) {
                        $assets = $this->loopAssets($asset);
                        $id     = (string) $asset->attributes()->ID;
                        // @todo add translations for Engineering Drawings
                        $type = (in_array(strtolower($assetRefAttr->Type), ['dxf file', 'igs file', 'stp file'])) ? 'Engineering Drawings' : (string) $assetRefAttr->Type;

                        // Assets to skip during mapping of SKU
                        // They should only be pulled from the SKU Group
                        if (boolVal($isModel)) {
                            if (in_array(
                                strtolower($type),
                                [
                                    'primary image',
                                    'secondaryimage',
                                    'secondary image',
                                    'part shot 1',
                                    'part shot 2',
                                    'part shot 3',
                                    'part shot 4',
                                    'part shot 5',
                                    'dimensional diagram'
                                ]
                            )
                            ) {
                                continue;
                            }
                        }

                        // Prevent duplicated assets
                        // @note STEP data tends or can be redundant. At times assets are applied to both SKU Group and SKU level
                        if (!isset($doc->body['assets']) || !in_array($id, array_column($doc->body['assets'], 'id'))) {
                            // Store Product assets for document
                            $doc->body['assets'][] = array_merge(
                                [
                                    'type' => $type,
                                    'id'   => $id
                                ],
                                $assets
                            );

                            // Build ES Downloads Index
                            $this->buildDownloads(
                                $action,
                                $type,
                                $asset,
                                $product,
                                $doc->body['product_line'] ?? null,
                                $doc->body['product_category'] ?? null
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in loopAssetCrossReference() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * @param  \SimpleXMLElement $product
     * @return mixed
     */
    private function loopClassifications(SimpleXMLElement $product, &$doc)
    {
        try {
            $doc->body['product_line']     = [];
            $doc->body['product_line_ids'] = [];
            $doc->body['product_category'] = [];

            if (isset($product->ClassificationReference)) {
                foreach ($product->ClassificationReference as $classificationRef) {
                    // <Asset><Classifications> missing @Type
                    $type = StringHelper::createKey($classificationRef->attributes()->Type);
                    $id   = (string) $classificationRef->attributes()->ClassificationID;
                    // Ignore other Classification Types e.g. Catalog
                    if ($type == 'web_reference') {
                        // Product Line, programmaticaly get the ancestor of a classification.
                        $doc->body['product_line'][] = $this->getClassificationAncestor($id);
                        // Used in ElasticSearchService::getRelatedProducts()
                        $doc->body['product_line_ids'][] = $id;
                        // Product Categories
                        $doc->body['product_category'][] = $this->getClassificationName($id, $doc->body['product_category']);
                    }
                }

                // Make sure array values are unique
                if (!empty($doc->body['product_line'])) {
                    $doc->body['product_line'] = array_values(array_unique($doc->body['product_line']));
                }

                if (!empty($doc->body['product_line_ids'])) {
                    $doc->body['product_line_ids'] = array_values(array_unique($doc->body['product_line_ids']));
                }

                if (!empty($doc->body['product_category'])) {
                    $doc->body['product_category'] = array_values(array_unique($doc->body['product_category']));
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in loopClassifications() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Loop Product Models
     * <Products><Product><Product>
     *
     * @param  \SimpleXMLElement $productFam
     * @param  stdClass          $doc
     * @return mixed
     */
    private function loopModels(SimpleXMLElement $productFam, &$doc)
    {
        $models    = [];
        try {

            $modelsLen = count($productFam->Product);

            for ($i = 0; $i < $modelsLen; $i++) {
                $model = $productFam->Product[$i];

                $models[] = [
                    'sku'    => (string) $model->attributes()->ID,
                    'name'   => (string) $model->Name,
                    'slug'   => StringHelper::createSlug((string) $model->Name),
                    'values' => $this->loopValues($model)
                ];

                $this->loopAssetCrossReference($model, $doc, true);
            }
        } catch (\Exception $e) {
            $this->record('Exception in loopModels() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }

        return $models;
    }

    /**
     * Retrieve the unit name from the UnitList or use the default value from the UnitID attribute.
     *
     * @param  string $unitId The Value's UnitID attribute
     * @return string
     */
    private function loopUnitList($unitId)
    {
        try {
            $unit = $this->xmlNode->unitlist->xpath('//Unit[@ID="' . (string) $unitId[0] . '"]');

            if (!empty($unit)) {
                // return StringHelper::createKey($unit[0]->Name);
            }

            return StringHelper::getUnit($unitId);
        } catch (\Exception $e) {
            $this->record('Exception in loopUnitList() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }


    /**
     * @param  \SimpleXMLElement $node
     * @return mixed
     */
    private function loopValues(SimpleXMLElement $node, &$doc = null)
    {
        $values = [];
        try {
            // Loop all $node <Values><Value>
            if (isset($node->Values)) {
                foreach ($node->Values->Value as $value) {
                    // Get readable key name instead of arbitrary "ATT####" value
                    $attrID = (string) $value->attributes()->AttributeID;
                    if (in_array($attrID, $this->excludeAttributes)) {
                        continue;
                    }

                    $attrName = $this->getAttributeName($attrID);

                    if (!is_null($attrName)) {
                        $attrKey = StringHelper::createKey($attrName);
                        // Use coupon headline for name and slug, if it exist
                        if ($attrID == 'ATT919' && !is_null($doc)) {
                            //echo str_replace('/&nbsp;/', '', html_entity_decode('ABC &nbsp; XYZ'));
                            $doc->body['name'] = (string) trim(html_entity_decode(str_replace('&nbsp;', ' ', $value)));
                            $doc->body['slug'] = StringHelper::createSlug((string) $value);
                        }
                        // ES values
                        $values[$attrKey] = (string) $value;
                        // Filters with English key name
                        $this->buildFiltersElasticSearch($attrID, $attrName, (string) $value);
                        // Terms with translated names
                        $this->buildFiltersVocabulary($attrID, $this->getAttributeNameLocale($attrID), (string) $value);
                    }
                }

                // Loop all $node <Values><MultiValue>
                foreach ($node->Values->MultiValue as $multiValue) {
                    $attrID = (string) $multiValue->attributes()->AttributeID;
                    if (in_array($attrID, $this->excludeAttributes)) {
                        continue;
                    }

                    $attrName = $this->getAttributeName($attrID);

                    if (!is_null($attrName)) {
                        $attrNameLocale = $this->getAttributeNameLocale($attrID);
                        $attrKey        = StringHelper::createKey($attrName);

                        foreach ($multiValue->Value as $multiValueValue) {
                            // Append UnitID to key, if exist
                            $unitKey  = '';
                            $unitAbbr = '';
                            $unit     = $multiValueValue->attributes()->UnitID;
                            if ($unit) {
                                $abbr = $this->loopUnitList($unit);
                                $unitKey  = '_' . $abbr;
                                $unitAbbr = ' (' . $abbr . ')';
                            }

                            // ES values
                            // Ensure this array key hasn't already been used as a string
                            // @note 11/16/18 Issues with ATT667662 and ATT22507 key/value pairs
                            if (!isset($values[$attrKey . $unitKey]) || gettype($values[$attrKey . $unitKey]) !== 'string') {
                                $values[$attrKey . $unitKey][] = (string) $multiValueValue;
                            }
                            // Quick way to identify attribute indexing issues
                            // if (gettype($values[$attrKey . $unitKey]) !== 'array') {
                            //     $values['conflict'][] = (string) $attrName;
                            // }
                            // Filters with English key name
                            $this->buildFiltersElasticSearch($attrID, $attrName . $unitAbbr, (string) $multiValueValue);
                            // Terms with translated names
                            $this->buildFiltersVocabulary($attrID, $attrNameLocale . $unitAbbr, (string) $multiValueValue, $unitKey);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in loopValues() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }

        return $values;
    }

    /**
     * @param  string $xml
     * @return boolean
     */
    private function isValidXML($xml)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);
        $errors = libxml_get_errors();

        return $errors; //empty($errors);
    }

    /**
     * @param string $xml
     * @param bool   $delta
     */
    private function parseXML(string $xml, bool $delta = false)
    {
        try {

            $url = Drupal::service('file_system')->realpath($xml);
            // $isValidXML = $this->isValidXML($url);
            $mimeType = mime_content_type($url);

            if (/*!$this->isValidXML($url) ||*/
                $mimeType !== 'application/xml' && $mimeType !== 'text/xml'
            ) {
                $this->record('Not a valid XML file: ' . $xml . ' (' . $mimeType . ')', 'error');

                return;
            }

            // Define some references
            $this->attributesRef = Json::decode(file_get_contents(__DIR__ . '/../config/mappings/attributes.json', true), true);
            $this->modified      = new \DateTime('now');
            $this->modified      = $this->modified->setTimeZone(new \DateTimeZone('America/New_York'));
            $this->modified      = $this->modified->format('U');
            $this->xmlNode       = new stdClass();

            // File chunkSize
            $stream = new Stream\File($url, 1024);
            // Construct the default parser
            $parser = new StringWalker(
                [
                'captureDepth'     => 2,
                'expectGT'         => true,
                'extractContainer' => true
                ]
            );
            // Create the streamer
            $streamer = new XmlStringStreamer($parser, $stream);

            while ($node = $streamer->getNode()) {
                $xml = simplexml_load_string($node);

                switch ($xml->getName()) {
                case 'Products':
                    $this->xmlNode->products = $xml;

                    break;
                case 'Classifications':
                    $this->xmlNode->classifications = $xml;

                    break;
                case 'UnitList':
                    $this->xmlNode->unitlist = $xml;

                    break;
                case 'AttributeList':
                    $this->xmlNode->attributes = $xml;

                    break;
                case 'Assets':
                    $this->xmlNode->assets = $xml;

                    break;
                }
            }

            // Get language code from XML root attribute ContextID
            $root          = $parser->getExtractedContainer();
            $rootXML       = simplexml_load_string($root);
            $rootContextID = (string) $rootXML->attributes()->ContextID[0];

            if (false === $this->langCode = array_search($rootContextID, $this->locales)) {
                $this->record($rootContextID . ' does not exist.', 'error');
                return;
            }

            // Use STEP EN for STEP EN-GB during bulk import
            if (!boolVal($delta) && strtolower($this->langCode) == 'en' && StepHelper::getCurrentSite()['code'] == 'gb') {
                $this->langCode = 'gb';
            }

            if (boolVal($delta)) {
                $this->record("Indexing {$this->langCode} Delta Feed", 'notice');
            }
            $this->indexData();

            // Use STEP EN XML for STEP EN-GB delta imports
            if (boolVal($delta) && $this->langCode == 'en') {
                $this->clearCachedData();
                $this->langCode = 'gb';
                $this->record("Indexing {$this->langCode} Delta Feed", 'notice');
                $this->indexData();
            }

            $this->clearCachedData();
        } catch (\Exception $e) {
            $this->record('Exception in parseXML() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Wipes out temporary data stores.
     * Should only be run after parsing an XML file.
     */
    private function clearCachedData()
    {
        // reset XML vars
        $this->xmlNode                      = null;
        $this->langCode                     = null;
        $this->modelUpdates                 = [];
        $this->indexDownloads               = [];
        $this->filters                      = [];

        // reset cache vars
        $this->cachedAttr                   = [];
        $this->cachedAttrLocale             = [];
        $this->cachedClassification         = [];
        $this->cachedClassificationAncestor = [];

        $clearedData = json_encode(
            [
            'xmlNode'                      => $this->xmlNode,
            'langCode'                     => $this->langCode,
            'modelUpdates'                 => $this->modelUpdates,
            'indexDownloads'               => $this->indexDownloads,
            'filters'                      => $this->filters,
            'cachedAttr'                   => $this->cachedAttr,
            'cachedAttrLocale'             => $this->cachedAttrLocale,
            'cachedClassification'         => $this->cachedClassification,
            'cachedClassificationAncestor' => $this->cachedClassificationAncestor,
            ]
        );

        $this->record("Cache cleared for {$this->langCode} Delta Feed: {$clearedData}", 'notice');
    }

    /**
     * @param Drupal\Core\Entity\ContentEntityBase $entity
     * @param string                               $field
     * @param string                               $value
     */
    private function setTermField(ContentEntityBase $entity, string $field, string $value)
    {
        try {
            // Only set a field if there's a value
            // @note Delta Feeds do not contain all fields.
            // @important Ensure fields are not set/updated to empty values
            if ($entity->hasField($field) && !empty($value)) {
                $entity->set($field, $value);
            }
        } catch (\Exception $e) {
            $this->record('Exception in setTermField() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Update document's model array
     * If a SKU is approved in STEP it'll no longer be nested under
     * the SKU Group. We'll need a script to find the matching model
     * in the document via the sku. This ensures we are updating the
     * appropriate model and not replacing the entire models array.
     * We don't want to update the entire model array because we're
     * unaware of the other possible models indexed.
     *
     * @return array $arr
     */
    private function updateModels()
    {
        $indexType = 'products';
        $arr       = [];

        try {
            foreach ($this->modelUpdates as $key => $model) {
                $doc          = new stdClass();
                $doc->_action = 'update';
                $doc->_index  = StepHelper::getEsIndexName($this->langCode) . '_' . $indexType;
                $doc->_type   = $indexType;
                $doc->_id     = $model['doc_id'];

                $sku = (string) $model['product']->attributes()->ID;

                // Updates existing model or add new model
                $doc->body['script'] = [
                    'lang'   => 'painless',
                    'source' => '
                      def trackModel = [];
                      for (int i = 0; i < ctx._source.models.length; ++i) {
                          if (ctx._source.models[i].sku.equalsIgnoreCase(params.model.sku)) {
                              ctx._source.models[i] = params.model;
                          } else {
                              if (trackModel.indexOf(params.model.sku) === -1) {
                                  trackModel.add(params.model.sku);
                                  ctx._source.models.add(params.model);
                              }
                          }
                      }
                  ',
                    'params' => [
                        'model' => [
                            'sku'    => $sku,
                            'name'   => (string) $model['product']->Name,
                            'slug'   => StringHelper::createSlug((string) $model['product']->Name),
                            'values' => $this->loopValues($model['product'])
                        ]
                    ]
                ];

                // Loop <ClassificationReference>
                $this->loopClassifications($model['product'], $doc);
                // Loop <AssetCrossReference>
                $this->loopAssetCrossReference($model['product'], $doc, true, 'update');

                $arr[] = $doc;
            }
        } catch (\Exception $e) {
            $this->record('Exception in updateModels() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
        }

        return $arr;
    }

    /**
     * Create Products Vocabulary
     *
     * @param array  $tree
     * @param string $vid
     * @param array  $parent
     */
    private function walkTermsClassifications($tree, string $vid, string $parent = null)
    {
        try {
            foreach ($tree as $key => $value) {

                // Term fields
                $esId  = $value->attributes()->ID;
                $name  = StepHelper::translate((string) $value->Name);
                $desc  = $value->xpath('.//MetaData/Value[@AttributeID="Pro Landing Body"]');
                $desc  = !empty($desc) ? (string) $desc[0] : '';
                $order = $value->xpath('.//MetaData/Value[@AttributeID="ATT15738"]');
                $order = !empty($order) ? (string) $order[0] : '';
                $image = '';
                if (isset($value->AssetCrossReference[0])) {
                    $id    = (string) $value->AssetCrossReference[0]->attributes()->AssetID;
                    $asset = $this->xmlNode->assets->xpath('/Assets/Asset[@ID="' . $id . '"]/AssetPushLocation[@ConfigurationID="Source to JPG"]');
                    if (!empty($asset)) {
                        $image = (string) basename($asset[0]);
                    }
                }

                // Prevent duplicates
                $results = Drupal::entityQuery('taxonomy_term')
                    ->condition('field_es_id.value', $esId, '=')
                    ->execute();

                // if a name is omittedor blank, we'll get a DB exception. So we allocate an obvious placeholder to detect the problem.
                if (!$name || empty($name) || is_null($name) || !strlen($name)) {
                    $name = 'null-term-class-' . uniqId();
                    $this->record('NULL name encounted in walkTermsClassifications(): ' . json_encode($value), 'error');
                }

                // Create or update term
                if (empty($results)) {

                    $term = Term::create(
                        [
                        'name'           => $name,
                        'vid'            => $vid,
                        'parent'         => $parent,
                        'langcode'       => $this->langCode,
                        'field_es_desc'  => [
                            'value' => $desc
                        ],
                        'field_es_order' => [
                            'value' => $order
                        ],
                        'field_es_id'    => [
                            'value' => $esId
                        ],
                        'field_es_image' => [
                            'value' => $image
                        ]
                        ]
                    );

                    $term = $this->getTranslation($term);
                    $term->save();
                } else {

                    $term = Term::load(key($results));

                    if (!empty($term)) {
                        $term = $this->getTranslation($term);
                        // @todo Add STEP attr to set whether it's enabled/disabled
                        $this->setTermField($term, 'name', $name);
                        $this->setTermField($term, 'field_es_id', $esId);
                        $this->setTermField($term, 'field_es_desc', $desc);
                        $this->setTermField($term, 'field_es_order', $order);
                        $this->setTermField($term, 'field_es_image', $image);
                        $term->save();
                    }
                }

                if (isset($value->Classification)) {
                    $this->walkTermsClassifications($value->Classification, $vid, $term->id());
                }
            }
        } catch (\Exception $e) {
            $this->record('Exception in walkTermsClassifications() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Create Product Filters Vocabulary
     *
     * @param array  $tree
     * @param string $vid
     */
    private function walkTermsFilters(array $tree, string $vid)
    {
        try {
            foreach ($tree as $key => $filter) {
                // Term fields
                $name     = StepHelper::translate($key);
                $esId     = $filter['config']['id'];
                $esKey    = $filter['config']['key'];
                $type     = $filter['config']['type'];
                $parent   = $filter['config']['parent'] ?? null;
                $rangeMin = '';
                $rangeMax = '';
                if ($type == 'range') {
                    $values   = $filter['values'];
                    $rangeMin = min($values);
                    $rangeMax = max($values);
                }

                // Prevent duplicates
                $results = Drupal::entityQuery('taxonomy_term')
                    ->condition('field_es_key.value', $esKey, '=')
                    ->execute();

                // if a name is omittedor blank, we'll get a DB exception. So we allocate an obvious placeholder to detect the problem.
                if (!$name || empty($name) || is_null($name) || !strlen($name)) {
                    $name = 'null-term-filter-' . uniqId();
                    $this->record('NULL name encounted in walkTermsFilters(): ' . json_encode($filter), 'error');
                }

                if (empty($results)) {

                    $term = Term::create(
                        [
                        'name'                 => $name,
                        'vid'                  => $vid,
                        'parent'               => null,
                        'langcode'             => $this->langCode,
                        'field_es_key'         => [
                            'value' => $esKey
                        ],
                        'field_es_filter_type' => [
                            'value' => $type
                        ],
                        'field_es_range_min'   => [
                            'value' => $rangeMin
                        ],
                        'field_es_range_max'   => [
                            'value' => $rangeMax
                        ],
                        'field_es_id'          => [
                            'value' => $esId
                        ]
                        ]
                    );

                    $term = $this->getTranslation($term);
                    $term->save();
                } else {

                    $term = Term::load(key($results));
                    if (!empty($term)) {
                        $term = $this->getTranslation($term);
                        // @todo Add STEP attr to set whether it's enabled/disabled?
                        $this->setTermField($term, 'name', $name);
                        $this->setTermField($term, 'field_es_filter_type', $type);
                        $this->setTermField($term, 'field_es_range_min', $rangeMin);
                        $this->setTermField($term, 'field_es_range_max', $rangeMax);
                        $this->setTermField($term, 'field_es_id', $esId);
                        $this->setTermField($term, 'field_es_key', $esKey);
                        $term->save();
                    }
                }
            }
        } catch (IntegrityConstraintViolationException $e) {
            $this->record('IntegrityConstraintViolationException in walkTermsFilters() [' . $e->getLine() . ']: ' . $e->getTraceAsString(), 'error');
            return false;
        } catch (Exception $e) {
            $this->record('Exception in walkTermsFilters() [' . $e->getLine() . ']: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * GET PRODUCT
     * Return the product object for the provided slug.
     *
     * @param string $slug Product slug from route parameters
     *
     * @return mixed|null
     */
    public function getProductAssets(string $key, string $value)
    {
        $productAssets = \Drupal::service('step.elastic_search_service')->getSingleProduct([$key, $value], [], true);

        if ($productAssets) {
            $productAssets = json_decode($productAssets);

            if (!json_last_error()) {
                return $productAssets;
            }
        }

        return null;
    }
}
