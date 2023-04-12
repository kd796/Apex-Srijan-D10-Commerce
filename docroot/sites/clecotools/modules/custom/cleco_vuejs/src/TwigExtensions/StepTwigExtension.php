<?php

namespace Drupal\cleco_vuejs\TwigExtensions;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermStorage;
use Drupal\Component\Serialization\Json;
use Drupal\cleco_vuejs\Utils\StringHelper;
use Drupal\cleco_vuejs\Utils\StepHelper;

class StepTwigExtension extends \Twig_Extension
{

    public function getName()
    {
        return 'Step';
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('json_decode', [$this, 'jsonDecode']),
            new \Twig_SimpleFilter('to_str', [$this, 'toString'])
        ];
    }

    public function stepAssetBaseUrl()
    {
        return getenv('REMOTE_BASE') . getenv('STEP_BASE');
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('create_key', [$this, 'createKey']),
            new \Twig_SimpleFunction('order_filters', [$this, 'orderFilters']),
            new \Twig_SimpleFunction('unique_downloads', [$this, 'uniqueDownloads']),
            new \Twig_SimpleFunction('step_asset_base_url', [$this, 'stepAssetBaseUrl']),
            new \Twig_SimpleFunction('step_asset', [$this, 'stepAsset']),
            new \Twig_SimpleFunction('carousel_assets', [$this, 'carouselAssets']),
            new \Twig_SimpleFunction('sort_featured_products', [$this, 'sortFeaturedProducts']),
            new \Twig_SimpleFunction('getDownloadTypes', [$this, 'getDownloadTypes']),
            new \Twig_SimpleFunction('getProductLines', [$this, 'getProductLines']),
            new \Twig_SimpleFunction('get_product_filters', [$this, 'getProductFilters']),
            new \Twig_SimpleFunction('getSegments', [$this, 'getSegments']),
            new \Twig_SimpleFunction('getProductCarousel', [$this, 'getProductCarousel']),
            new \Twig_SimpleFunction('getSingleProduct', [$this, 'getSingleProduct']),
            new \Twig_SimpleFunction('getMultipleProducts', [$this, 'getMultipleProducts']),
            new \Twig_SimpleFunction('getRelatedProducts', [$this, 'getRelatedProducts']),
            new \Twig_SimpleFunction('get_locale', [$this, 'getLocale']),
            new \Twig_SimpleFunction('download_link', [$this, 'downloadLink']),
            new \Twig_SimpleFunction('product_categories', [$this, 'productCategories']),
            new \Twig_SimpleFunction('get_product_url', [$this, 'getProductUrl']),
            new \Twig_SimpleFunction('filter_content_featured_products', [$this, 'filterContentFeaturedProducts']),
            new \Twig_SimpleFunction('get_translations', [$this, 'getTranslations']),
            new \Twig_SimpleFunction('get_enhanced_product', [$this, 'getEnhancedProduct']),
            new \Twig_SimpleFunction('get_enhanced_product_features', [$this, 'getEnhancedProductFeatures']),
            new \Twig_SimpleFunction('get_enhanced_product_hotspots', [$this, 'getEnhancedProductHotspots']),
            new \Twig_SimpleFunction('get_enhanced_product_360_images', [$this, 'getEnhancedProduct360Images']),
            new \Twig_SimpleFunction('get_enhanced_product_line', [$this, 'getEnhancedProductProductLine']),
            new \Twig_SimpleFunction('get_enhanced_other_products', [$this, 'getEnhancedOtherProducts']),
            new \Twig_SimpleFunction('get_enhanced_related_products', [$this, 'getEnhancedRelatedProducts']),
        ];
    }

    public function getTranslations()
    {
        return StepHelper::getTranslations();
    }

    /**
     * @param  string $str The string to conver to safe key value
     * @return string
     */
    public function createKey(string $str)
    {
        return StringHelper::createSlug($str);
    }

    /**
     * Get CMS Term Product Filters Order
     * @todo Implement in CMS
     * @return array
     */
    public function orderFilters()
    {
        return json_encode([
            'Controllers & Software'                     => [],
            'Torque Wrenches'                            => [],
            'Pulse Tools'                                => [],
            'Hand Drilling, Countersinking & Spotfacing' => [],
            'Riveting'                                   => [],
            'Advanced Drills'                            => [
                'Tool Type',
                'Product Family'
            ],
            'Fixtured Spindles'                          => [
                'Spindle Type',
                'Tool Configuration',
                'Torque Transducer',
                'Drive Type'
            ],
            'Nutrunners & Screwdrivers'                  => [
                'Tool Configuration',
                'Control Type',
                'Power Type',
                'Product Family',
                'Air Inlet Size (INH)'
            ],
            'Impact Wrenches'                            => [
                'Product Family',
                'Drive Size',
                'Drive Type',
                'Material',
                'Tool Configuration',
                'Air Inlet Size (INH)'
            ],
            'Air Motors'                                 => [
                'Motor Configuration',
                'Product Family',
                'Features',
                'Spindle Type',
                'Exhaust',
                'Horsepower (HP)',
                'Torque Stall (FtLbs)',
                'Torque Stall (Nm)'
            ],
            'Grinders'                                   => [
                'Tool Type',
                'Tool Configuration',
                'Termination',
                'Type Housing',
                'Collet Size',
                'Exhaust',
                'Spindle Size',
                'Air Inlet Size (INH)',
                'Abrasive Capacity'
            ],
            'Sanders & Polishers'                        => [
                'Tool Type',
                'Exhaust',
                'Abrasive Type',
                'Abrasive Capacity',
                'Features',
                'Orbital Pattern Size (INH)',
                'Orbital Pattern Size (MMT)',
                'Termination'
            ],
            'Specialty Tools'                            => [
                'Product Type',
                'Abrasive Capacity',
                'Exhaust',
                'Tool Configuration',
                'Type Housing',
                'Air Inlet Size (INH)'
            ]

        ]);
    }

    /**
     * Remove Download Duplicates on Product Detail Page
     *
     * @param  array $arr
     * @return array
     */
    public function uniqueDownloads(array $arr)
    {
        return array_unique($arr, SORT_REGULAR);
    }

    /**
     * Download link for asset
     *
     * @param $asset
     */

    public function downloadLink($asset, $type)
    {   //custom-kuntal
        $origSrcFile = $asset['original_source_file'];
        $ext = pathinfo($origSrcFile)['extension'];
        $src = '';

        switch(strtolower($type)) {
            case '3d model':
                // $src = $asset['3d_model_igs'] ?? ($asset['3d_model'] ?? $origSrcFile);
                if (array_key_exists('3d_model_igs', $asset) && !empty($asset['3d_model_igs'])) {
                    $src = $asset['3d_model_igs'];
                } else if (array_key_exists('3d_model', $asset) && !empty($asset['3d_model'])) {
                    $src = $asset['3d_model'];
                } else {
                    $src = $origSrcFile;
                }
            break;
            default:
                $src = strtolower($ext) === 'tif' ? ($asset['imagesourceprime1'] ?? '#') : $origSrcFile;
            break;
        }

        return $this->stepAssetBaseUrl() . $src;
    }

    /**
     * Remove Translated Orphans
     *
     * @param $results ElasticSearch Results
     * @param $content Drupal Featured Products fields
     */
    public function filterContentFeaturedProducts($results, $content)
    {
        $matches = [];
        $fields  = $content['#items']->getValue();

        foreach ($results as $product) {
            foreach ($fields as $key => $field) {
                if (array_search($product['_source']['id'], $field)) {
                    $matches[] = $field;
                }
            }
        }

        return $matches;
    }

    /**
     * Orders ElasticSearch results based on CMS order by key name
     */
    public function sortFeaturedProducts($results, $sortBy)
    {
        if (isset($results[0]['_source'])) {
            usort($results, function ($a, $b) use (&$results, $sortBy) {
                foreach ($sortBy as $key => $value) {
                    if ($a['_source']['id'] == $value) {
                        return 0;
                        break;
                    }

                    if ($b['_source']['id'] == $value) {
                        return 1;
                        break;
                    }
                }
            });

            return $results;
        }

        return [];
    }

    /**
     * Return field values in Term
     */
    public function productCategories(string $name = null, string $tid = null)
    {
        $terms      = [];
        $data       = [];
        $properties = [];
        $curSite    = StepHelper::getCurrentSite();
        $alias      = Drupal::service('path_alias.manager')->getAliasByPath('/products/product-catalog', $curSite['code']);

        if (!empty($name)) {
            $term  = \Drupal::EntityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties['name']);
            $term  = reset($term);
            $terms = \Drupal::EntityTypeManager()->getStorage('taxonomy_term')->loadChildren($term->get('tid')->value);
        }

        if (!empty($tid)) {
            $terms = \Drupal::EntityTypeManager()->getStorage('taxonomy_term')->loadChildren($tid);
        }
        foreach ($terms as $term) {
            $term = \Drupal\taxonomy\Entity\Term::load($term->get('tid')->value);
            // Translated term
            if ($term->hasTranslation($curSite['code'])) {
                $term = $term->getTranslation($curSite['code']);
            }
            $media_field = [];
            $media_url = '';
            $image = $term->get('field_category_image')->getString() ?? '';
            if (empty($media_field) && !empty($image)) {
                $media_field = explode(',', $image);
                $image_load = Media::load($media_field[0]);
                $image_file = File::load($image_load->field_media_image->target_id);
                $image_url  = $image_file->getFileUri();
                $media_url = file_create_url($image_url);
            }
            $data[] = [
                'id'    => $term->get('field_classification_id')->value,
                'name'  => $term->get('name')->value,
                'body'  => $term->get('description')->value,
                'order' => $term->get('field_product_display_order')->value,
                'url'   => $alias . '?product_category=' . urlencode(preg_replace('/,/', '%2C', $term->get('name')->value)),
                'image' => !empty($media_url) ? $media_url : ''
            ];
        }
        return json_encode($data);
    }

    /**
     * @param $slug
     * @return string
     */
    public function getProductUrl(string $slug = null)
    {
        $curSite    = StepHelper::getCurrentSite();
        $alias      = Drupal::service('path_alias.manager')->getAliasByPath('/products/product-catalog', $curSite['code']);

        return $alias . '/' . $slug;
    }

    /**
     * @param $assets
     * @param $key ES key to retrieve basepath from
     * @param $style
     * @return mixed
     */
    public function stepAsset($assets, $key = 'source_to_jpg', $style = 'product_thumb')
    {
        $filepath = '';

        // Some products/documents will have multiple images
        // If possible, get thumb from Primary Image
        if ($style == 'product_thumb') {
            array_filter($assets, function ($asset) use (&$filepath, $key) {
                if ($asset['type'] == 'Primary Image' && isset($asset[$key])) {
                    $filepath = $this->stepAssetBaseUrl() . 'styles/thumb/' . rawurlencode($asset[$key]);
                }
            });
        }

        if (empty($filepath)) {
            array_filter($assets, function ($asset) use (&$filepath, $key, $style) {
                // All images have the same filename,
                // We'll use original filename the .jpg extension to get the resized images
                //$src  = $asset['original_source_file'];
                $slug = rawurlencode($this->findFilename($asset['id'], $style) . '.jpg');

                switch ($style) {
                    case 'product_thumb':
                        $filepath = $this->stepAssetBaseUrl() . 'styles/thumb/' . $slug;
                        break;
                    case 'product_zoom_thumb':
                        $filepath = $this->stepAssetBaseUrl() . 'styles/zoom-thumb/' . $slug;
                        break;
                    case 'large':
                        $filepath = $this->stepAssetBaseUrl() . $slug;
                        break;
                }
            });
        }

        return $filepath;
    }

    public function carouselAssets($assets)
    {
        $assets = StepHelper::sortAssetsForDisplay($assets);
        $carouselAssets = [];

        foreach ($assets as $index => $asset) {
            $carouselAssets[] = [
              'index'    => $index,
              'zoomable' => $this->stepAsset([$asset], 'source_to_jpg', 'large'),
              'thumb'    => $this->stepAsset([$asset], 'source_to_jpg', 'product_zoom_thumb')
            ];
        }

        // return json_encode($carouselAssets);
        return $carouselAssets;
    }

    /**
     * Scans asset folder for correct filename
     *
     * @param $slug
     * @param $style
     * @return string
     */
    public function findFilename($slug, $style)
    {
        $basePath = '';

        switch ($style) {
            case 'product_thumb':
                $basePath = 'styles/thumb/';
                break;
            case 'product_zoom_thumb':
                $basePath = 'styles/zoom-thumb/';
                break;
        }

        // if the exact filename exists, we don't need to do anything else
        if (file_exists(DRUPAL_ROOT . getenv('STEP_BASE') . $basePath . $slug . '.jpg')) {
            return $slug;
        }

        // if the exact filename doesn't exist, we'll need to look for it
        $assets = glob(DRUPAL_ROOT . getenv('STEP_BASE') . $basePath . '*.jpg');

        if ($assets) {
            foreach ($assets as $asset) {
                $path_parts = pathinfo($asset);
                $filename = $path_parts['filename'];

                if (strtoupper($filename) === strtoupper($slug)) {
                    return $filename;
                }
            }
        }

        return $slug;
    }

    /**
     * @param $json
     * @param $assoc
     */
    public function jsonDecode($json, bool $assoc = false)
    {
        return json_decode($json, $assoc);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getSegments()
    {
        $curUri = \Drupal::request()->getRequestUri();

        return array_values(array_filter(explode('/', $curUri), function ($segment) {
            // Explicitly check in case there is a 0 in a segment (i.e. foo/0 or foo/0/bar)
            return $segment !== '';
        }));
    }

    /**
     * @param string $key
     */
    public function getDownloadTypes()
    {
        return StepHelper::downloadTypes();
    }

    public function getLocale()
    {
        return StepHelper::getCurrentSite();
    }

    /**
     * @return mixed
     */
    public function getProductLines()
    {
        return StepHelper::getProductLines();
    }

    /**
     * @return mixed
     */
    public function getProductFilters()
    {
        return StepHelper::getProductFilters();
    }

    /**
     * @param string $indexName ElasticSearch Index Name
     * @param array $multiSearchQuery ['slug', value], ['id', value]
     */
    public function getProductCarousel(array $multiSearchQuery)
    {
        return Drupal::service('step.elastic_search_service')->getProductCarousel($multiSearchQuery);
    }

    /**
     * @param array $matchQuery
     */
    public function getSingleProduct(array $matchQuery)
    {
        return Drupal::service('step.elastic_search_service')->getSingleProduct($matchQuery);
    }

    /**
     * @param array $matchQuery
     */
    public function getMultipleProducts(array $matchQuery)
    {
        return Drupal::service('step.elastic_search_service')->getMultipleProducts($matchQuery);
    }

    /**
     * @param array $filters
     * @param string $name
     */
    public function getRelatedProducts(array $filters, string $name, int $size = 5, $enhanced = false)
    {
        return Drupal::service('step.elastic_search_service')->getRelatedProducts($filters, $name, $size, $enhanced);
    }

    /**
     * @param string $sku
     * @param string $slug
     * @return array
     */
    public function getEnhancedProduct($sku, $slug)
    {
        $curSite         = StepHelper::getCurrentSite();
        $enhancedProduct = [
            'node'         => null,
            'video_bg'     => null,
            'features'     => null,
            'hotspots'     => null,
            '360_images'   => null,
            'product_line' => null,
            'other'        => null,
            'related'      => null,
        ];

        $nodes = Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties([
                'langcode'   => $curSite['code'],
                'field_sku'  => $sku,
                // 'field_slug' => $slug,
            ]);

        if ($node = reset($nodes)) {
            $enhancedProduct['node']         = $node;
            $enhancedProduct['video_bg']     = $this->getEnhancedProductHeroVideoBackground($node);
            $enhancedProduct['features']     = $this->getEnhancedProductFeatures($node);
            $enhancedProduct['hotspots']     = $this->getEnhancedProductHotspots($node);
            $enhancedProduct['images']       = $this->getEnhancedProduct360Images($node);
            $enhancedProduct['product_line'] = $this->getEnhancedProductProductLine($node);
            $enhancedProduct['other']        = $this->getEnhancedOtherProducts($node);
            $enhancedProduct['related']      = $this->getEnhancedRelatedProducts($node);
        }

        // return null;
        return $enhancedProduct;
    }

    /**
     * Retrieves the URL for the video assigned to the current product's product line
     *
     * @param object $node
     * @return string
     */
    public function getEnhancedProductHeroVideoBackground($node)
    {
        $curSite                = StepHelper::getCurrentSite();
        $product_line_video     = null;
        $field_video_background = $node->field_video_background->getValue();

        if (count($field_video_background) && array_key_exists('target_id', $field_video_background[0])) {
            $field_video_background_load = Media::load($field_video_background[0]['target_id']);
            $field_video_background_file = File::load($field_video_background_load->field_media_video_file->target_id);
            //$product_line_video          = $field_video_background_file->url();
            $product_line_video          = $field_video_background_file->createFileUrl();
        } else {
            $field_product_line = $node->field_product_line->getValue();

            if (count($field_product_line) && array_key_exists('target_id', $field_product_line[0])) {
                $field_product_line_term = Term::load($field_product_line[0]['target_id']);

                // get translation if availabe
                if ($field_product_line_term->hasTranslation($curSite['code'])) {
                    $field_product_line_term = $field_product_line_term->getTranslation($curSite['code']);
                }

                $field_product_line_video = $field_product_line_term->get('field_product_line_video')->getValue();

                if (count($field_product_line_video) && array_key_exists('target_id', $field_product_line_video[0])) {
                    $field_product_line_video_load = Media::load($field_product_line_video[0]['target_id']);
                    $field_product_line_video_file = File::load($field_product_line_video_load->field_media_video_file->target_id);
                   // $product_line_video            = $field_product_line_video_file->url();
                   $product_line_video            = $field_product_line_video_file->createFileUrl();
                }
            }
        }

        return $product_line_video;
    }

    /**
     * @param object $node
     * @return array
     */
    public function getEnhancedProductFeatures($node)
    {
        $curSite         = StepHelper::getCurrentSite();
        $features        = [];
        $productFeatures = $node->field_product_features->getValue();

        foreach ($productFeatures as $feature) {
            if ($feature['target_id']) {
                $icon_key  = 'nut';
                $icon_src  = null;
                $image_url = '';

                $paragraph = Paragraph::load($feature['target_id']);
                $title     = $paragraph->field_title->first()->getValue();
                $copy      = $paragraph->field_copy->first()->getValue();
                $icon      = $paragraph->field_icon->first()->getValue();
                $image     = $paragraph->field_image->getValue();

                if ($icon['target_id']) {
                    $term     = Term::load($icon['target_id']);
                    $icon     = $term->get('name')->first()->getValue()['value'];
                    $icon_key = $this->createKey($icon);
                }

                $icon_path = DRUPAL_ROOT . "/themes/atg/dist/img/icons/{$icon_key}.svg";

                if (file_exists($icon_path)) {
                    $icon_src = file_get_contents($icon_path);
                }

                if (count($image) && array_key_exists('target_id', $image[0])) {
                    $image_load = Media::load($image[0]['target_id']);
                    $image_file = File::load($image_load->field_media_image->target_id);
                    $image_url  = ImageStyle::load('enhanced_product')->buildUrl($image_file->getFileUri());
                }

                $features[] = [
                    'title' => $title['value'] ?? null,
                    'copy'  => $copy['value'] ?? null,
                    'icon'  => [
                        'key' => $icon_key,
                        'src' => $icon_src,
                    ],
                    'image' => $image_url,
                ];
            }
        }

        return $features;
    }

    /**
     * Retrieves the assigned hotspots for the product viewer
     *
     * @param object $node
     * @return array
     */
    public function getEnhancedProductHotspots($node)
    {
        $hotspots = [];

        if ($node->field_feature_hotspots) {
            $productHotspots = $node->field_feature_hotspots->getValue();

            foreach ($productHotspots as $hotspot) {
                $hotspot_type         = null;
                $hotspot_text         = null;
                $hotspot_x_pos        = 50;
                $hotspot_y_pos        = 50;
                $hotspot_media_bundle = null;
                $hotspot_filename     = null;
                $hotspot_frame        = 0;

                if (count($hotspot) && array_key_exists('target_id', $hotspot)) {
                    $paragraph          = Paragraph::load($hotspot['target_id']);
                    $field_hotspot_text = $paragraph->field_hotspot_text->getValue();

                    if (count($field_hotspot_text) && array_key_exists('value', $field_hotspot_text[0])) {
                        $hotspot_text = $field_hotspot_text[0]['value'] ?? null;

                        if ($hotspot_text) {
                            $hotspot_type = $this->createKey($hotspot_text);
                        }
                    }

                    $field_x_position = $paragraph->field_x_position->getValue();

                    if (count($field_x_position) && array_key_exists('value', $field_x_position[0])) {
                        $hotspot_x_pos = $field_x_position[0]['value'] ?? 50;
                    }

                    $field_y_position = $paragraph->field_y_position->getValue();

                    if (count($field_y_position) && array_key_exists('value', $field_y_position[0])) {
                        $hotspot_y_pos = $field_y_position[0]['value'] ?? 50;
                    }

                    $field_hotspot_media = $paragraph->field_hotspot_media->getValue();

                    if (count($field_hotspot_media) && array_key_exists('target_id', $field_hotspot_media[0])) {
                        $media                = Media::load($field_hotspot_media[0]['target_id']);
                        $hotspot_media_bundle = $media->bundle() ?? null;
                        $media_field          = $media->field_media_video_file ?? $media->field_media_image;
                        $file                 = File::load($media_field->target_id);
                       // $hotspot_filename     = $file->url();
                       $hotspot_filename     = $file->createFileUrl();
                        
                    }

                    $field_display_frame = $paragraph->field_display_frame->getValue();

                    if (count($field_display_frame) && array_key_exists('value', $field_display_frame[0])) {
                        $hotspot_frame = ((int) $field_display_frame[0]['value'] - 1) ?? 0;
                    }

                    $hotspots[] = [
                        'text'  => $hotspot_text,
                        'type'  => substr($hotspot_type, 0, 20),
                        'media' => [
                            'src'    => $hotspot_filename ?? null,
                            'bundle' => $hotspot_media_bundle,
                            'ext'    => pathinfo($hotspot_filename, PATHINFO_EXTENSION) ?? null,
                        ],
                        'position' => [
                            'x' => $hotspot_x_pos,
                            'y' => $hotspot_y_pos,
                        ],
                        'frame' => $hotspot_frame,
                    ];
                }
            }
        }

        return $hotspots;
    }

    /**
     * Retrieves the loading and 360° product images
     *
     * @param object $node
     * @return array
     */
    public function getEnhancedProduct360Images($node)
    {
        $_360_images = [
            'frames'  => 0,
            'product' => null,
            'loading' => null,
        ];

        $field_360_image = $node->field_360_image->getValue();

        if (count($field_360_image) && array_key_exists('target_id', $field_360_image[0])) {
            $paragraph = Paragraph::load($field_360_image[0]['target_id']);

            $frames  = $paragraph->field_360_product_image_frames->getValue();
            $product = $paragraph->field_360_product_image;
            $loading = $paragraph->field_360_loading_image;

            if (count($frames) && array_key_exists('value', $frames[0])) {
                $_360_images['frames']  = $frames[0]['value'] ?? 24;
            }

           // $_360_images['product'] = $product->entity ? $product->entity->url() : null;
            $_360_images['product'] = $product->entity ? $product->entity->createFileUrl() : null;
            //$_360_images['loading'] = $loading->entity ? $loading->entity->url() : null;
            $_360_images['loading'] = $loading->entity ? $loading->entity->createFileUrl() : null;
        }

        return $_360_images;
    }

    /**
     * Retrieves details for current product's product line
     *
     * @param object $node
     * @return array
     */
    public function getEnhancedProductProductLine($node)
    {
        $curSite      = StepHelper::getCurrentSite();
        $product_line = [
            'name'        => null,
            'description' => null,
            'tagline'     => null,
            'video'       => null,
            'connect'     => null,
            'form_intro'  => null,
            // 'form'        => [
            //     'intro' => null,
            //     'image' => null,
            // ],
            // 'keywords'    => null,
        ];

        $field_product_line = $node->field_product_line->getValue();

        if (count($field_product_line) && array_key_exists('target_id', $field_product_line[0])) {
            $field_product_line_term  = Term::load($field_product_line[0]['target_id']);

            // get translation if availabe
            if ($field_product_line_term->hasTranslation($curSite['code'])) {
                $field_product_line_term = $field_product_line_term->getTranslation($curSite['code']);
            }

            $product_line['name'] = $field_product_line_term->get('name')->getValue()[0]['value'];

            $description = $field_product_line_term->get('description')->getValue();

            if (count($description) && array_key_exists('value', $description[0])) {
                $product_line['description'] = $description[0]['value'];
            }

            $field_tagline = $field_product_line_term->get('field_tagline')->getValue();

            if (count($field_tagline) && array_key_exists('value', $field_tagline[0])) {
                $product_line['tagline'] = $field_tagline[0]['value'];
            }

            $field_product_line_video    = $field_product_line_term->get('field_product_line_video')->getValue();

            if (count($field_product_line_video) && array_key_exists('target_id', $field_product_line_video[0])) {
                $field_product_line_video_load = Media::load($field_product_line_video[0]['target_id']);
                $field_product_line_video_file = File::load($field_product_line_video_load->field_media_video_file->target_id);
                //$product_line['video']         = $field_product_line_video_file->url()
                $product_line['video']         = $field_product_line_video_file->createFileUrl();
            }

            $field_connect_with_us = $field_product_line_term->get('field_connect_with_us')->getValue();

            if (count($field_connect_with_us) && array_key_exists('value', $field_connect_with_us[0])) {
                $product_line['connect'] = $field_connect_with_us[0]['value'];
            }

            $field_form_intro_text = $field_product_line_term->get('field_form_intro_text')->getValue();

            if (count($field_form_intro_text) && array_key_exists('value', $field_form_intro_text[0])) {
                $product_line['form_intro'] = $field_form_intro_text[0]['value'];
            }
        }

        return $product_line;
    }

    /**
     * Returns other products in the same product line.
     * e.g. CellCore, CellTek, NeoTek, mPro, etc.
     *
     * @param object $node
     * @return array
     */
    public function getEnhancedOtherProducts($node)
    {
        $curSite            = StepHelper::getCurrentSite();
        $related_products   = [];
        $field_product_line = $node->field_product_line->getValue();

        if (count($field_product_line) && array_key_exists('target_id', $field_product_line[0])) {
            // grab all other Enhanced Product nodes tagged with the same Product Line as current
            $products = \Drupal::entityTypeManager()
                ->getStorage('node')
                ->loadByProperties([
                    'type'               => 'enhanced_product',
                    'status'             => 1,
                    'field_product_line' => [$field_product_line[0]['target_id']],
                    'langcode'           => $curSite['code'],
                ]);

            foreach ($products as $product) {
                // we want to exclude the one we're currently viewing
                // if ($node->id() === $product->id()) continue; // jk

                $thumb = null;
                $large = null;
                $slug  = null;
                // $url   = null;
                $sku   = null;

                $field_sku = $product->get('field_sku')->getValue();
                if (count($field_sku) && array_key_exists('value', $field_sku[0])) {
                    $sku = $field_sku[0]['value'];
                }

                $field_360_image = $product->get('field_360_image')->getValue();

                if (count($field_360_image) && array_key_exists('target_id', $field_360_image[0])) {
                    $paragraph = Paragraph::load($field_360_image[0]['target_id']);
                    $file      = File::load($paragraph->field_360_loading_image->target_id);
                    $thumb     = ImageStyle::load('enhanced_product_thumbnail')->buildUrl($file->getFileUri());
                    $large     = ImageStyle::load('enhanced_product')->buildUrl($file->getFileUri());
                }

                $field_slug = $product->get('field_slug')->getValue();

                if (count($field_slug) && array_key_exists('value', $field_slug[0])) {
                    $slug = $field_slug[0]['value'];
                }

                // $url = Url::fromRoute('entity.node.canonical', ['node' => $product->id()]);
                // $url = $url->toString();

                $related_products[] = [
                    'id'    => $sku,
                    'title' => trim($product->getTitle()),
                    'url'   => $this->getProductUrl($slug), // $url,
                    'thumb' => $thumb,
                    'large' => $large,
                    'slug'  => $slug,
                ];
            }
        }

        if (count($related_products)) {
            array_multisort(array_column($related_products, 'title'), SORT_DESC, $related_products);
        }

        return $related_products;
    }

    /**
     * Returns related products based on the SKUs attached to the product
     *
     * @param object $node
     * @return array
     */
    public function getEnhancedRelatedProducts($node)
    {
        $field_related_products = $node->field_related_products->getValue();

        if (count($field_related_products) && array_key_exists('value', $field_related_products[0])) {
            $related_products = preg_split('/[\r\n]/', $field_related_products[0]['value']);

            $related_products = array_filter($related_products, function($value) {
                return !is_null($value) && $value !== '';
            });

            $matchQuery = [];

            foreach (array_unique($related_products) as $sku) {
                $matchQuery[] = [
                    'match' => [
                        'id' => $sku
                    ],
                ];
                $matchQuery[] = [
                    'match' => [
                        'models.sku' => $sku
                    ],
                ];
            }

            return Drupal::service('step.elastic_search_service')->getMultipleProducts($matchQuery);
        }

        return json_encode([]);
    }
}
