<?php

namespace Drupal\helpers\TwigExtension;

use DOMDocument;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\cleco_vuejs\Utils\StepHelper;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Language\LanguageInterface;
use const PHP_URL_QUERY;
use Twig_Markup;
use function drupal_get_path;
use function file_exists;
use function file_get_contents;
use function is_object;
use function is_string;
use function preg_match;
use const PHP_URL_HOST;

class HelpersExtension extends \Twig_Extension
{

    /**
     * This is the same name we used on the services.yml file
     *
     * @return string
     */
    public function getName()
    {
        return 'helpers.twig_extension';
    }

    /**
     * Here is where we declare our new filter.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('drupal_region', [$this, 'drupalRegion']),
            new \Twig_SimpleFunction('svg', [$this, 'svgFunction']),
            new \Twig_SimpleFunction('mix', [$this, 'mixFunction']),
            new \Twig_SimpleFunction('isLocal', [$this, 'isLocalFunction']),
            new \Twig_SimpleFunction('theme_asset', [$this, 'themeAssetFunction']),
            new \Twig_SimpleFunction('fnmatch', [$this, 'fnmatch']),
            new \Twig_SimpleFunction('pass_attributes', [
                $this,
                'passAttributesFunction'
            ]),
            new \Twig_SimpleFunction('get_passed_attributes', [
                $this,
                'getPassedAttributesFunction'
            ], ['needs_context' => true]),
            new \Twig_SimpleFunction('get_paragraph', [
                $this,
                'getParagraphFunction'
            ]),
            new \Twig_SimpleFunction('getNodeCount', [$this, 'getNodeCount']),
            new \Twig_SimpleFunction('env', [$this, 'envFunction']),
            new \Twig_SimpleFunction('modelTableDefinition', [$this, 'modelTableDefinitionFunction']),
            new \Twig_SimpleFunction('alias_by_path', [$this, 'aliasByPath'])
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('string', [$this, 'stringFilter']),
            new \Twig_SimpleFilter('term_list', [
                $this,
                'termListFilter'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('youtube_thumb', [$this, 'youTubeThumbFilter']),
            new \Twig_SimpleFilter('sort_assets', [$this, 'sortAssetsFilter']),
            new \Twig_SimpleFilter('for_comparison_table', [$this, 'forComparisonTableFilter']),
            new \Twig_SimpleFilter('ksort', [$this, 'ksortFilter']),
        ];
    }

    public function aliasByPath($path, $langCode) {

        $langCode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

        return \Drupal::service('path_alias.manager')->getAliasByPath($path, $langCode);
    }

    /**
     * Taken from Twig Tweak Module
     * Builds the render array of a given region.
     *
     * @param string $region
     *   The region to build.
     * @param string $theme
     *   (Optional) The name of the theme to load the region. If it is not
     *   provided then default theme will be used.
     *
     * @return array
     *   A render array to display the region content.
     */
    public function drupalRegion($region, $theme = null)
    {
        $entity_type_manager = \Drupal::entityTypeManager();
        $blocks              = $entity_type_manager->getStorage('block')->loadByProperties([
            'region' => $region,
            'theme'  => $theme ?: \Drupal::config('system.theme')->get('default')
        ]);

        $view_builder = $entity_type_manager->getViewBuilder('block');

        $build = [];

        /* @var $blocks \Drupal\block\BlockInterface[] */
        foreach ($blocks as $id => $block) {
            if ($block->access('view')) {
                $block_plugin = $block->getPlugin();
                if ($block_plugin instanceof TitleBlockPluginInterface) {
                    $request = \Drupal::request();
                    if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
                        $block_plugin->setTitle(\Drupal::service('title_resolver')->getTitle($request, $route));
                    }
                }
                $build[$id] = $view_builder->view($block);
            }
        }

        if ($build) {
            $build['#region']         = $region;
            $build['#theme_wrappers'] = ['region'];
        }

        return $build;
    }

    /**
     * FUNCTION: SVG
     * Return the contents of an SVG at the given path.
     *
     * @param string $path Path to the SVG
     *
     * @return null|Twig_Markup
     */
    public function svgFunction($path)
    {
        $filename      = ltrim($path, '/');
        $theme_handler = \Drupal::service('theme_handler');
        $default_theme = $theme_handler->getDefault();

        $path = \Drupal::service('extension.list.theme')->getPath($default_theme) . '/' . $filename;
        if (file_exists($path)) {
            $name = pathinfo($path, PATHINFO_FILENAME);
            $svg  = file_get_contents($path);
            $svg  = preg_replace('/<\?xml.*?\?>/', '', $svg);
            $dom  = new DOMDocument();
            $dom->loadXML($svg);
            foreach ($dom->getElementsByTagName('svg') as $element) {
                $element->setAttribute('class', 'icon icon--' . $name);
            }

            $svg = $dom->saveHTML();

            return new Twig_Markup($svg, 'utf-8');
        }

        return null;
    }

    /**
     * FUNCTION: MIX
     * Get the manifest-generated path for a given theme file.
     *
     * @param string $path Path to file within current theme.
     *
     * @return string
     */
    public function mixFunction($path)
    {
        /**
         * @var mixed
         */
        static $manifest = null;
        $theme_path      = self::themePath();

        $manifest_filename = preg_replace('/^\/?dist/', '', $path);
        if (is_null($manifest)) {
            $manifest = json_decode(file_get_contents($theme_path . '/dist/mix-manifest.json'), true);
        }

        if (isset($manifest[$manifest_filename])) {
            return '/' . $theme_path . '/dist' . $manifest[$manifest_filename];
        }

        return '/' . $theme_path . '/' . ltrim($path, '/');
    }

    /**
     * FUNCTION: IS LOCAL
     * Check if the hostname appears to be a local hostname
     *
     * @return bool
     */
    public function isLocalFunction()
    {
        global $base_url;

        $host     = parse_url($base_url, PHP_URL_HOST);
        $segments = explode('.', $host);
        $tld      = array_pop($segments);

        return in_array($tld, [
            'localhost',
            '127.0.0.1',
            'test',
            'dev',
            'local'
        ]);
    }

    /**
     * FUNCTION: THEME ASSET
     * Return the url to the theme asset at the given path.
     *
     * @param string $path Path to a theme asset
     *
     * @return string
     */
    public function themeAssetFunction($path)
    {
        $theme_path = self::themePath();

        return '/' . $theme_path . '/' . ltrim($path, '/');
    }

    /**
     * THEME PATH
     * Retrieve the path to the current theme.
     *
     * @return string
     */
    protected static function themePath()
    {
        $theme_handler = \Drupal::service('theme_handler');
        $default_theme = $theme_handler->getDefault();
        $theme_path = \Drupal::service('extension.list.theme')->getPath($default_theme);

        return $theme_path;
    }

    /**
     * Return match against pattern
     *
     * @param string $pattern Pattern to match
     * @param string $string String to test against
     *
     * @return boolean
     */
    public function fnmatch(string $pattern, string $string)
    {
        return fnmatch($pattern, $string);
    }

    /**
     * FUNCTION: PASS ATTRIBUTES
     * Allow passing of attributes to a child Paragraph field
     *
     * @param array $render Render array from a child paragraph field
     * @param array $attributes Associative array of attributes to pass
     *
     * @return mixed
     */
    public function passAttributesFunction($render, $attributes = [])
    {
        $items = $render['#items'] ?? null;
        if ($items) {
            for ($i = 0; $i < count($items); $i++) {
                $render[$i]['#passed_attributes'] = $attributes;
            }
        } else {
            $render['#passed_attributes'] = $attributes;
        }

        return $render;
    }

    /**
     * GET PASSED ATTRIBUTES
     * Return any attributes passed by the parent Paragraph template
     *
     * @param array $context Twig context
     * @param array $defaults Array of defaults when no attributes were passed
     *
     * @return array
     */
    public function getPassedAttributesFunction($context, $defaults = [])
    {
        return $context['elements']['#passed_attributes'] ?? $defaults;
    }

    /**
     * FUNCTION: GET PARAGRAPH
     * Return a child paragraph (including values).
     *
     * @param array $render Render field for a field with children
     *
     * @return \Drupal\Core\Entity\EntityInterface|\Drupal\paragraphs\Entity\Paragraph|null
     */
    public function getParagraphFunction($render)
    {
        $object = $render['0'] ?? null;
        if ($object) {
            if (isset($object['#paragraph'])) {
                return Paragraph::load($object['#paragraph']->id());
            } else if (isset($object['target_id'])) {
                return Paragraph::load($object['target_id']);
            }
        }

        return null;
    }

    /**
     * FUNCTION: ENV
     * Return an environment variable.
     *
     * @param string $key Key to return
     *
     * @return array|false|string
     */
    public function envFunction($key)
    {
        return getenv($key);
    }

    /**
     * FUNCTION: MODEL TABLE DEFINITION
     *
     * @param array $product Product to use
     *
     * @return \Drupal\step\Utils\ComparisonTableDefinition
     */
    public function modelTableDefinitionFunction($product)
    {
        return StepHelper::getModelTableDefinition($product);
    }

    /**
     * FILTER: STRING
     * Cast the passed variable to string
     *
     * @param mixed $string
     *
     * @return mixed
     */
    public function stringFilter($string)
    {
        if (!is_string($string)) {
            if (is_object($string) && method_exists($string, 'toString')) {
                return $string->toString();
            }

            return (string) $string;
        }

        return $string;
    }

    /**
     * FILTER: TERM LIST
     * Return an imploded list of rendered term links
     *
     * @param mixed $value Terms field
     * @param string $glue Glue to join term links
     *
     * @return string
     */
    public function termListFilter($value, $glue = ', ')
    {
        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value, false);
        }

        return implode($glue, array_map(function ($item) {
            $term   = Term::load($item->target_id);
            $render = [
                '#type'  => 'link',
                '#url'   => $term->toUrl(),
                '#title' => $term->label()
            ];
            return \Drupal::service('renderer')->render($render);
        }, (array) $value));
    }

    /**
     * FILTER: YOUTUBE THUMB
     * Return a YouTube thumbnail URL by parsing the ID out of the provided URL
     *
     * @param string $url URL to the YouTube video
     *
     * @return null|string
     */
    public function youTubeThumbFilter($url)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches);
        $id = $query['v'] ?? $matches[1] ?? null;
        if ($id !== null) {
            return sprintf('https://img.youtube.com/vi/%s/hqdefault.jpg', $id);
        }

        return null;
    }

    /**
     * FILTER: NODE COUNT
     * Return a count of the nodes of the passed type
     *
     * @param string $type Node type to count
     *
     * @return mixed
     */
    public function getNodeCount(string $type)
    {
        // $query = db_select('node', 'n');
        $query = \Drupal::database()->select('node', 'n');
        $query->condition('n.type', $type);
        $query->addExpression('COUNT(*)');

        return $query->execute()->fetchField();
    }

    /**
     * FILTER: SORT ASSETS
     * Sort an array of product assets for display
     *
     * @param array $assets
     *
     * @return array
     */
    public function sortAssetsFilter($assets)
    {
        return StepHelper::sortAssetsForDisplay($assets);
    }

    /**
     * FILTER: KSORT
     * Sort an array by its keys
     *
     * @param $array
     *
     * @return mixed
     */
    public function ksortFilter($array) {
        ksort($array);

        return $array;
    }
}
