<?php

namespace Drupal\ai_content_generation;

use Drupal\ai\AiProviderInterface;
use Drupal\ai\OperationType\Chat\ChatInput;
use Drupal\ai\OperationType\Chat\ChatMessage;
use Drupal\ai\Plugin\ProviderProxy;
use Exception;

/**
 * Service for entity process.
 */
class AIService {

  /**
   * @var AiProviderInterface|ProviderProxy|NULL
   */
  protected AiProviderInterface|ProviderProxy|NULL $provider;

  /**
   * @var string
   */
  protected string $model;

  /**
   * Get provider data.
   *
   * @param string $model
   *
   * @return void
   * @throws Exception
   */
  public function setProvider(string $model = ''): void {
    $service = \Drupal::service('ai.provider');

    if (empty($model)) {
      $default = $service->getDefaultProviderForOperationType('chat');
      if (empty($default['provider_id'])) {
        throw new Exception('No model set.');
      }
      $this->provider = $service->createInstance($default['provider_id']);
      $this->model = $default['model_id'];
    }
    else {
      $this->provider = $service->loadProviderFromSimpleOption($model);
      $this->model = $service->getModelNameFromSimpleOption($model);
    }
  }

  /**
   * Make a requests in the chat.
   *
   * @param array $data
   *
   * @return array
   * @throws Exception
   */
  public function chat($data): array {
    $content = [];
    $messages = [];

    $this->setProvider($data['model']);
    $this->provider->setChatSystemRole($data['role']);

    foreach ($data['fields'] as $field_name => $field_prompt) {
      $content[$field_name] = $this->chatMessage($messages, $field_prompt);
    }

    return $content;
  }

  /**
   * @param array $messages
   * @param string $prompt
   *
   * @return string
   */
  public function chatMessage(array &$messages, string $prompt): string {
    $messages[] = new ChatMessage('user', $prompt);
    $message = $this->provider->chat(new ChatInput($messages), $this->model)->getNormalized()->getText();

    // Add feedback to the messages array as history.
    $messages[] = new ChatMessage('user', $message);

    return $message;
  }

  /**
   * Function for data request.
   *
   * @param array $data
   *
   * @return array
   */
  public function prepare(array $data): array {
    $data['prompt'] .= 'Write me an article as text only in 2000 characters for any theme strong HTML, without MD. Use the tags p, h2, h3, h4, ul, li for text formatting.';
    $data['fields'] = [
      'body' => $data['prompt'],
      'title' => 'Give me a title without quotes for this article',
      'tags' => 'Give me a five tags for this article, use coma for separator',
    ];
    $content = $this->chat($data);

    return [
      'title' => $content['title'],
      'body' => $content['body'],
      'tags' => $content['tags'],
    ];
  }

}
