<?php

namespace Drupal\atg_clone\Form;

use Drupal\node\NodeForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quick_node_clone\Form\QuickNodeCloneNodeForm;

class AtgCloneNodeForm extends QuickNodeCloneNodeForm
{
    /**
     * @var array
     */
    protected $records = null;

    /**
     * @var int Cloned Node ID
     */
    protected $cid = null;
    /**
     * @var int Node Source ID
     */
    protected $sid = null;

    /**
     * @var string
     */
    protected $langcode = null;

    /**
     * @var mixed
     */
    protected $sourceLangCode;

    /**
     * Get Source ID
     *
     * @return int
     */
    private function getSid()
    {
        if (is_null($this->sid)) {
            $this->sid = \Drupal::routeMatch()->getRawParameter('node');
        }

        return $this->sid;
    }

    /**
     * Get Clone ID
     *
     * @return int
     */
    private function getCid()
    {
        if (is_null($this->cid)) {
            $this->cid = md5($this->getSid());
        }

        return $this->cid;
    }

    /**
     * Get Records of Existing Clones
     *
     * @param string $sid
     * @return mixed
     */
    private function getRecords()
    {
        if (is_null($this->records)) {
            $sid           = $this->getSid();
            $db            = \Drupal::database();
            $this->records = $db->query("SELECT * FROM atg_clone WHERE nid='$sid'")->fetchAll();
        }

        return $this->records;
    }

    /**
     * Get/Set Original Source's Langcode
     *
     * @param string $val
     */
    private function getSetSourcesLangCode(string $val = '')
    {
        if (!isset($this->sourceLangCode)) {
            $this->sourceLangCode = $val;
        }

        return $this->sourceLangCode;
    }

    /**
     * Store Cloned Node
     *
     * @param $cid
     * @param $nid
     * @param $langcode
     */
    private function insert($cid, $nid, $langcode)
    {
        $db = \Drupal::database();

        $db->insert('atg_clone')
           ->fields([
               'cid'      => $cid,
               'sid'      => $this->getSid(),
               'nid'      => $nid,
               'langcode' => $langcode
           ])
           ->execute();
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        parent::save($form, $form_state);

        $records  = $this->getRecords();
        $cid      = $this->getCid();
        $sid      = $this->getSid();
        $langcode = $form_state->get('langcode');
        $node     = $this->entity;

        // If no records are found, it's the first time a cloned has been created.
        // Instead of just storing the cloned node, we'll also need to store the original source
        if (empty($records)) {
            // Create record for original source
            $this->insert($cid, $sid, $this->getSetSourcesLangCode());
            // Create record for cloned entity
            $this->insert($cid, $node->id(), $langcode);
        } else {
            // Create record for cloned entity
            $this->insert($records[0]->cid, $node->id(), $langcode);
        }
    }

    /**
     * @param FormStateInterface $form_state
     */
    public function validateLanguage(array &$form, FormStateInterface $form_state)
    {
        // $clonedLangcode = $form_state->get('langcode');
        // $sourceLangCode = $this->getSetSourcesLangCode($this->entity->get('langcode')->value);

        // // Ensure the cloned node's langcode is different from the original source's langcode
        // if ($sourceLangCode == $clonedLangcode) {
        //     $error = 'Please select a unique language. A cloned node cannot have the same langauge from the source it was cloned.';
        // }

        // // Get all related nodes from the original db query via cid
        // $records = $this->getRecords();

        // if (!empty($records)) {
        //     $cid            = $records[0]->cid;
        //     $db             = \Drupal::database();
        //     $relatedRecords = $db->query("SELECT * FROM atg_clone WHERE cid='$cid'")->fetchAll();
        //     // Prevent duplicating cloned nodes for a given language
        //     foreach ($relatedRecords as $record) {
        //         if ($record->langcode == $clonedLangcode) {
        //             $error = 'Please select a unique language. A node with the selected language has already been cloned.';
        //         }
        //     }
        // }

        // if (isset($error)) {
        //     $form_state->setErrorByName('langcode', $this->t($error));
        // }
    }
}
