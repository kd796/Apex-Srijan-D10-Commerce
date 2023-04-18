<?php

namespace Drupal\step\Services;

use stdClass;

use Drupal;
use Drupal\step\Utils\StepHelper;
use Drupal\step\Traits\LoggerTrait;

class ImageStylesService
{
    use LoggerTrait;

    private $count = 0;
    /**
     * Create Thumb from Products Vocabulary
     */
    public function createTermThumb(string $transform)
    {
        $productCategories = Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('products');

        foreach ($productCategories as $category) {
            $term = Drupal\taxonomy\Entity\Term::load($category->tid);
            $img  = $term->get('field_es_image')->value;
            if (isset($img)) {
                $this->resizeImg($img);
            }
        }
    }

    /**
     * Resize Image and Create Directory
     *
     * @param $basePath
     * @param string $style     Image style to create: 'product_thumb', 'product_zoom_thumb'
     * @return mixed
     */
    public function resizeImg($basePath, string $style = 'product_thumb')
    {
        switch ($style) {
            case 'product_thumb':
                $exec      = '-strip -resize 275x350\> -background white -gravity center -extent 285x350 -unsharp 0.25x0.25+8+0.065 -density 72 -quality 90';
                $resizeDir = 'thumb/';
                break;
            case 'product_zoom_thumb':
                $exec      = '-strip -resize 500x500\> -background white -gravity center -extent 500x500 -unsharp 0.25x0.25+8+0.065 -density 72 -quality 90';
                $resizeDir = 'zoom-thumb/';
                break;
        }

        if (!isset($resizeDir)) {
            throw new \Exception('Image Style does not exist.');
            $this->log("$style Image Style does not exist", 'error');
            return;
        }

        $serverRoot = DRUPAL_ROOT;
        // @note Remote servers throwing "not authorized" with IP address, use $serverRoot
        $origFile   = ((strtoupper(getenv('ENV')) != 'PROD') ? getenv('REMOTE_BASE') : $serverRoot) . getenv('STEP_BASE') . $basePath;
        $stylePath  = $serverRoot . getenv('STEP_BASE') . 'styles/' . $resizeDir;
        $output     = $stylePath . $basePath; // where the file should be saved
        $origExist  = StepHelper::fileExists($origFile); // ensures file exist before attempting to resize

        // @note Server fails to create directory due to SELinux permission issues
        // Folder must already be created with right permissions(httpd_sys_rw_content_t)
        if (!file_exists($stylePath)) {
            mkdir($stylePath, 0775, true);
        }

        if ($origExist && (!file_exists($output) || filemtime($output) !== filemtime($origFile))) {
            // @note exec() file names must be quoted to catch names with spaces
            if (strtoupper(getenv('ENV')) != 'PROD') {
                exec("/usr/local/bin/convert '$origFile' $exec '$output'");
            } else {
                exec("/usr/bin/convert '$origFile' $exec '$output'");
            }
        }

        if (!$origExist) {
            $this->log("File $origFile doesn’t exist", 'error');
        }
    }
}
