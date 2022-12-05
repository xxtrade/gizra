<?php

namespace Drupal\pevb_demo;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;

/**
 * Trait UserGreetingTrait.
 *
 * Helper method for building a user greeting message.
 */
trait UserGreetingTrait {

  /**
   * Build a list of tags out of a field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The referencing entity.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user object.
   *
   * @return array
   *   Render array.
   */
  public function buildGreeting(FieldableEntityInterface $entity, AccountInterface $user): array {
    $url =  Url::fromRoute(
      'og.subscribe', 
      [
        'group' => $entity->id(), 
        'entity_type_id' => $entity->getEntityTypeId()
      ]
    );
    $link = Link::fromTextAndUrl(
      t('Hi @username, click here if you would like to subscribe to this group called @label', 
        [
          '@username' => $user->getAccountName(),
          '@label' => $entity->label(),
        ]
      ),
      $url
     );
    return [
      '#theme' => 'status_messages',
      '#message_list' => [
        'status' => [
          $link->toString(),
        ]
      ],
    ];
  }

}
