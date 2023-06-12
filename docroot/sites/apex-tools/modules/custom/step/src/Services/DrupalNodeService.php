<?php

namespace Drupal\step\Services;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\field\FieldConfigInterface;
use Drupal\field\EntityReferenceFieldItemList;
use Drupal\step\Utils\StepHelper;
use Drupal\step\Utils\StringHelper;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\step\Traits\LoggerTrait;
use stdClass;

class DrupalNodeService
{
    use LoggerTrait;

    /**
     * @var array
     * Only field types we want to store in ElasticSearch
     */
    private $allowedTypes = ['string_long', 'string', 'list_string', 'text_long', 'text', 'list_string', 'link', 'text_with_summary'];
    /**
     * @var array
     * The type of fields where we need dig down to field values via loops
     */
    private $requiresLoop = ['entity_reference_revisions', 'entity_reference'];
    /**
     * @var array
     * Any fields we may want to ignore
     */
    private $excludeFields = ['field_size', 'field_uuid'];
    /**
     * @var array
     * Bundle types we want to ignore
     * Some bundles throw errors for `$node->get($fieldId)->entity`
     */
    private $excludeBundles = ['contact_form', 'image'];

    /**
     * @param bool $startOfTime If we should index all nodes or get latest
     */
    public function indexDocuments(bool $startOfTime = false)
    {
        $modified = new \DateTime('now');
        $modified = $modified->setTimeZone(new \DateTimeZone('America/New_York'));
        $modified = $modified->format('U');

        // Check if we should get ndoes from beginning of time or from yesterday
        // Cron will need to be configured to run daily
        if ($startOfTime === false) {
            $date = new \DateTime('now');
            $date->add(\DateInterval::createFromDateString('yesterday'));
        } else {
            $date = new \DateTime('1970-01-01');
        }
        $date = $date->setTimeZone(new \DateTimeZone('America/New_York'));
        $date = $date->format('U');

        $query = \Drupal::entityQuery('node');

        $types = $query
            ->orConditionGroup()
            ->condition('type', 'home')
            ->condition('type', 'about')
            ->condition('type', 'basic_page')
            ->condition('type', 'geo_directory')
            ->condition('type', 'service_support')
            ->condition('type', 'solutions')
            ->condition('type', 'solutions_landing')
            ->condition('type', 'article');

        $dates = $query
            ->orConditionGroup()
            ->condition('created', $date, '>=')
            ->condition('changed', $date, '>=');

        $result = $query
            ->condition($types)
            ->condition($dates)
            ->condition('status', 1)
            ->sort('created', 'ASC')
            ->condition('langcode', StepHelper::getCurrentSite()['code'])
            ->execute();

        $documents = [];
        $index     = -1;
        $indexType = 'nodes';

        if (!empty($result)) {
            foreach ($result as $nid) {
                $index++;
                $node   = Node::load($nid);
                $fields = $this->contentTypeFields($node->getType(), Drupal::service('entity.manager'));
                $title  = $node->get('title')->value;
                // ElasticSearch configuration
                $obj          = new stdClass();
                $uid          = StringHelper::createSlug((string) $title);
                $obj->_action = 'index';
                $obj->_index  = StepHelper::getEsIndexName() . '_' . $indexType;
                $obj->_type   = $indexType;
                $obj->_id     = $uid;
                // Node keys

                $obj->body['name']     = (string) $title;
                $obj->body['type']     = StringHelper::crateReadable($node->getType());
                $obj->body['slug']     = $node->url();
                $obj->body['modified'] = $modified;

                foreach ($fields as $fieldId => $field) {
                    if (!is_null($node->get($fieldId))) {
                        //@todo Do we need to check/loop "entity_reference" types and get that data as well?
                        // Paragraphs and Taxonomy
                        $settings = $node->get($fieldId)->getSettings();

                        if (isset($settings['target_type'])) {
                            switch ($settings['target_type']) {
                                case 'paragraph':
                                    $this->paragraphFieldValues($fieldId, $node, $obj->body['values']);
                                    break;
                                case 'taxonomy_term':
                                    //@todo parse terms
                                    break;
                            }
                        }

                        // Drupal core fields
                        if (!empty($type = $node->get($fieldId)->getFieldDefinition()->getType())) {
                            $name  = $node->get($fieldId)->getName();
                            $value = $node->get($fieldId)->value;

                            if ($this->shouldIndex($type, $name) && !empty($value)) {
                                $obj->body['values'][$name] = preg_replace('/\r|\n/', '', strip_tags($node->get($fieldId)->value));
                            }
                        }
                    }
                }

                $documents[] = $obj;
            }
        }

        if (!empty($documents)) {
            Drupal::service('step.elastic_search_service')->indexDocuments($documents);
        }
    }

    /**
     * Limit the field tyepes returned. We only want the editable data.
     * Excludes fields uuid, changed, promote, sticky, etc
     *
     * @param $contentType
     * @param $entityManager
     * @return mixed
     */
    private function contentTypeFields($contentType, $entityManager)
    {
        $fields = [];

        if (!empty($contentType)) {
            $fields = array_filter(
                $entityManager->getFieldDefinitions('node', $contentType), function ($field_definition) {
                    return $field_definition instanceof FieldConfigInterface;
                }
            );
        }

        return $fields;
    }

    /**
     * @param $fieldId
     * @param $node
     * @param $data
     */
    private function paragraphFieldValues($fieldId, $node, &$data)
    {
        $paragraph = $node->get($fieldId)->entity;

        if (!is_null($paragraph) && !in_array($paragraph->bundle(), $this->excludeBundles)) {
            $fields = $this->contentTypeFields($paragraph->getType(), $paragraph);

            foreach ($fields as $fieldId => $field) {
                $type = $field->getType();

                // Loop if we have a repeater, paragraph, etc
                if ($this->isLoopable($type)) {
                    $this->paragraphFieldValues($fieldId, $paragraph, $data);
                } else {

                    $value = $paragraph->get($field->getName());
                    $name  = $field->getName();

                    // Only index specific fields
                    if ($this->shouldIndex($type, $name)) {
                        if ($type == 'link') {
                            if (!empty($value->title)) {
                                $data[$name][] = preg_replace('/\r|\n/', '', strip_tags($value->title));
                            }
                        } else {
                            if (!empty($value->value)) {
                                $data[$name][] = preg_replace('/\r|\n/', '', strip_tags($value->value));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $type
     */
    private function isLoopable($type)
    {
        return in_array($type, $this->requiresLoop);
    }

    /**
     * @param $filedType
     * @param $fieldName
     */
    private function shouldIndex($filedType, $fieldName)
    {
        return (in_array($filedType, $this->allowedTypes) && !in_array($fieldName, $this->excludeFields));
    }
}
