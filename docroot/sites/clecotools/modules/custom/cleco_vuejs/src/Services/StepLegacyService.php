<?php

namespace Drupal\cleco_vuejs\Services;

use Prewk\XmlStringStreamer\Parser\StringWalker;
use stdClass;

use Drupal;
use Drupal\file\Entity\File;
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

use Performance\Performance;

class StepLegacyService
{

    /**
     * @var object
     * XML nodes to retrieve data from
     */
    private $xmlNode;

    /**
     * @var array
     * Store the images to resize during bulk/delta imports
     * Thumb 285x350
     * Zoom Thumb 500x500
     */
    private $imageStylesPaths = [];

    /**
     * Index Documents from STEP form settings page in CMS
     * Full index of data, good for fixing health of index
     */
    public function indexBulk()
    {
        $xml = File::load(Drupal::config('step.settings.legacy_documents')->get('step_legacy_xml')[0])->getFileUri();

        $this->parseXML($xml);
    }

    // Private methods
    // =========================================================================

    /**
     * @param string $xml
     * @param bool   $delta
     */
    private function parseXML(string $xml, bool $delta = false)
    {
        $this->modified = new \DateTime('now');
        $this->modified = $this->modified->setTimeZone(new \DateTimeZone('America/New_York'));
        $this->modified = $this->modified->format('U');
        $this->xmlNode  = new stdClass();

        // File chunkSize
        $stream = new Stream\File(Drupal::service('file_system')->realpath($xml), 1024);
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
            case 'Documents':
                $this->xmlNode->documents = $xml;

                break;
            }
        }

        // Create Products Index
        Drupal::service('step.elastic_search_service')->indexDocuments(self::buildLegacy());

        $this->createImageStyles();
    }

    /**
     * @return null
     */
    private function createImageStyles()
    {
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
    }

    /**
     * @return mixed
     */
    private function buildLegacy()
    {
        $indexType = 'legacy_documents';
        $site      = strtolower(preg_replace('/(?<!\_)[A-Z]/', '_$0', lcfirst(StepHelper::getCurrentSite()['name'])));
        $arr       = [];

        foreach ($this->xmlNode->documents->Document as $document) {
            $doc = new stdClass();
            $uid = StringHelper::createSlug((string) $document->Name);

            if (isset($uid)) {
                if (empty($document->Associated_Product)) {
                    continue;
                }

                $thumbSrc = pathinfo((string) $document->File_Name)['filename'] . '.jpg';
                // ES keys
                $doc->_action = 'index';
                $doc->_index  = $site . '_' . $indexType;
                $doc->_type   = $indexType;
                $doc->_id     = $uid;
                // Product keys
                $doc->body['id']       = (string) $document->Name;
                $doc->body['name']     = (string) $document->Name;
                $doc->body['type']     = 'Legacy'; // a.k.a Legacy Documents
                $doc->body['modified'] = $this->modified;
                $doc->body['assets']   = [
                    'source_to_jpg'        => $thumbSrc,
                    'original_source_file' => (string) $document->File_Name
                ];
                $doc->body['values'] = [
                    'associated_products' => explode(',', $document->Associated_Product),
                    'language'            => explode(';', $document->Language)
                ];

                $doc->body['product_line']     = [];
                $doc->body['product_category'] = [];

                $this->imageStylesPaths['product_thumb'][] = $thumbSrc;

                $arr[] = $doc;
            }
        }

        if (!$arr) {
            $this->record('No legacy documents found in XML file.', 'notice');
        }

        return $arr;
    }
}
