<?php

namespace Drupal\ai_content_generation;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Batch service class.
 */
class BatchService {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The service of AI.
   *
   * @var AIService
   */
  protected $aiSevice;

  /**
   * The service of entity.
   *
   * @var EntityService
   */
  protected $entityService;

  /**
   * Batch Builder.
   *
   * @var BatchBuilder
   */
  protected $batchBuilder;

  /**
   * Construct of BatchService class.
   *
   * @param MessengerInterface $messenger
   *   Messenger service.
   * @param AIService $ai_service
   *   AI generator service.
   * @param EntityService $entity_service
   *   Entity service.
   */
  public function __construct(
    MessengerInterface $messenger,
    AIService $ai_service,
    EntityService $entity_service,
  ) {
    $this->messenger = $messenger;
    $this->aiSevice = $ai_service;
    $this->entityService = $entity_service;

    $this->batchBuilder = new BatchBuilder();
  }

  /**
   * Process a batch operation.
   *
   * @param array $data
   *   The file name.
   */
  public function batchProcess(array $data): void {
    $this->batchBuilder
      ->setTitle(t('Processing'))
      ->setInitMessage(t('Initializing.'))
      ->setProgressMessage(t('Creation in process.'))
      ->setErrorMessage(t('An error has occurred.'))
      ->setFile(\Drupal::service('extension.list.module')->getPath('ai_content_generation') . '/src/BatchService.php');

    $this->batchBuilder->addOperation([$this, 'batchProcessEntityAIGenerate'], [$data]);
    $this->batchBuilder->addOperation([$this, 'batchProcessEntityCreate'], [$data]);

    batch_set($this->batchBuilder->toArray());
  }

  /**
   * @param array $data
   *   The form submit data.
   * @param array $context
   *   Drupal batch context array.
   *
   * @return void
   */
  public function batchProcessEntityAIGenerate(array $data, array &$context): void {
    $context['message'] = t('Prepare data for content creating');
    $context['results']['entity_content'] = $this->aiSevice->prepare($data);
  }

  /**
   * Batch processing function to content generation.
   *
   * @param array $data
   *   The form submit data.
   * @param array $context
   *   Drupal batch context array.
   *
   * @throws EntityStorageException
   */
  public function batchProcessEntityCreate(array $data, array &$context): void {
    $context['message'] = t('Content create');
    $entity_content = $context['results']['entity_content'] ?? [];
    if ($entity_content['title']) {
      $this->entityService->create($entity_content, $data['type']);
    }
  }

}
