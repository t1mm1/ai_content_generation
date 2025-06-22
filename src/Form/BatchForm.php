<?php

namespace Drupal\ai_content_generation\Form;

use Drupal\ai_content_generation\BatchService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form that triggers a batch run.
 */
class BatchForm extends FormBase {

  use StringTranslationTrait;

  /**
   * Config settings.
   */
  const CONFIG_NAME = 'ai_content_generation.settings';

  /**
   * @var BatchService $batchService
   */
  protected $batchService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    BatchService $batch_service,
  ) {
    $this->batchService = $batch_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
  ) {
    return new static(
      $container->get('ai_content_generation.batch')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ai_content_generation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);

    $form['help'] = [
      '#markup' => $this->t('This form will run a batch operation that will create Article content items based on prompt of AI.'),
    ];

    $form['model'] = [
      '#type' => 'hidden',
      '#default_value' => $config->get('model'),
    ];

    $form['role'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Role'),
      '#default_value' => $config->get('role'),
      '#description' => $this->t('The AI role.'),
      '#required' => TRUE,
    ];

    $form['prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Prompt'),
      '#default_value' => $config->get('prompt'),
      '#description' => $this->t('The AI prompt.'),
      '#required' => TRUE,
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => [
        '' => 'Select a type',
        'article' => 'Article',
        'page' => 'Basic page',
      ],
      '#description' => $this->t('The type of content pages.'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Generate'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $data = [
      'model' => $form_state->getValue('model'),
      'role' => $form_state->getValue('role'),
      'prompt' => $form_state->getValue('prompt'),
      'type' => $form_state->getValue('type'),
    ];
    $this->batchService->batchProcess($data);
    $this->messenger()->addStatus(t('Creation of new content via AI was finished.'));
  }

}
