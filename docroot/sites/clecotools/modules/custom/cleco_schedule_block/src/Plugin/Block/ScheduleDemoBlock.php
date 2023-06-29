<?php

namespace Drupal\cleco_schedule_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ScheduleDemo' block.
 *
 * @Block(
 *  id = "schedule_demo_block",
 *  admin_label = @Translation("Schedule Demo block"),
 * )
 */
class ScheduleDemoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['schedule_demo_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Scheule Demo Label'),
      '#default_value' => !empty($this->configuration['schedule_demo_label']) ? $this->configuration['schedule_demo_label'] : '',
    ];

		$form['schedule_demo_link'] = [
			'#type' => 'fieldset',
			'#title' => $this->t('Schedule Demo Link Field'),
			'#collapsible' => FALSE,
			'#collapsed' => FALSE,  
		];

		$form['schedule_demo_link']['label'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Link Text'),
			'#default_value' => !empty($this->configuration['schedule_demo_link']['label']) ? $this->configuration['schedule_demo_link']['label'] : '',
		];

		$form['schedule_demo_link']['url'] = [
			'#type' => 'url',
			'#title' => $this->t('URL'),
			'#default_value' => !empty($this->configuration['schedule_demo_link']['url']) ? $this->configuration['schedule_demo_link']['url'] : '',
			'#type' => 'entity_autocomplete',
			'#target_type' => 'node',
		];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['schedule_demo_label'] = $form_state->getValue('schedule_demo_label');
    $this->configuration['schedule_demo_link']['url'] = $form_state->getValue('schedule_demo_link')['url'];
		$this->configuration['schedule_demo_link']['label'] = $form_state->getValue('schedule_demo_link')['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'schedule_demo_block',
      '#content' => [
        'label' => $this->configuration['schedule_demo_label'] ? $this->configuration['schedule_demo_label'] : '',
        'link' => $this->configuration['schedule_demo_link'] ? $this->configuration['schedule_demo_link']: '',
      ],
    ];
  }

}
