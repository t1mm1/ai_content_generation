<?php

namespace Drupal\ai_content_generation;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Service for entity process.
 */
class EntityService {

  /**
   * The current user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * The creation datetime.
   *
   * @var string
   */
  protected $datetime;

  /**
   * Construct of EntityService class.
   *
   * @param AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    AccountInterface $current_user
  ) {
    $this->currentUser = $current_user;
  }

  /**
   * Create a node with AI generated content.
   *
   * @param array $content
   * @param string $type
   *
   * @return string
   *
   * @throws EntityStorageException
   */
  public function create(array $content, string $type): string {
    $datetime = time();

    $array = [
      'title' => $content['title'],
      'body' => [
        'value' => $content['body'],
        'format' => 'full_html',
      ],
      'status' => NodeInterface::PUBLISHED,
      'langcode' => static::getCurrentLanguage(),
      'promote' => 0,
      'sticky' => 0,
      'type' => $type,
      'uid' => $this->currentUser->id(),
      'created' => $datetime,
      'updated' => $datetime,
    ];

    $entity = Node::create($array);
    $entity->save();

    return $entity->getTitle();
  }

  /**
   * Get current language.
   *
   * @return string
   */
  static public function getCurrentLanguage(): string {
    return \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

}
