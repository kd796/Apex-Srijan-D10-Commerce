<?php

namespace Drupal\sata_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;

/**
 * Defines a route controller for publishing all paragraphs.
 */
class PublishAllParagraphs extends ControllerBase {

  /**
   * Handler for process request.
   */
  public function process() {
    $query = \Drupal::entityQuery('paragraph')
      ->condition('status', 0);

    $pids = $query->execute();

    $chunks = array_chunk($pids, 1);
    $batch = [
      'title' => t('Publishing paragraphs.'),
      'operations' => [],
      'init_message' => t('Publishing now..'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('encountered an error.'),
    ];

    foreach ($chunks as $chunk) {
      $batch['operations'][] = [
        'Drupal\sata_core\Controller\PublishAllParagraphs::publish',
        [$chunk],
      ];
    }

    batch_set($batch);
    return batch_process('/');
  }

  /**
   * Tags the nodes with the category from the uploaded spreadsheet (column 2).
   */
  public static function publish($items, &$context) {
    foreach ($items as $item) {
      $paragraph = Paragraph::load($item);

      if ($paragraph) {
        $paragraph->set('status', 1);
        $paragraph->save();
      }
    }
  }

}
