<?php

namespace Drupal\pevb_demo\Plugin\EntityViewBuilder;

use Drupal\intl_date\IntlDate;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\server_general\EntityDateTrait;
use Drupal\server_general\EntityViewBuilder\NodeViewBuilderAbstract;
use Drupal\server_general\LineSeparatorTrait;
use Drupal\server_general\SocialShareTrait;
use Drupal\server_general\TitleAndLabelsTrait;
use Drupal\pevb_demo\UserGreetingTrait;
use Drupal\og\Og;
use Drupal\og\OgMembershipInterface;

/**
 * The "Node Group" plugin.
 *
 * @EntityViewBuilder(
 *   id = "node.group",
 *   label = @Translation("Node - Group"),
 *   description = "Node view builder for Group bundle."
 * )
 */
class NodeGroup extends NodeViewBuilderAbstract {

  use EntityDateTrait;
  use LineSeparatorTrait;
  use SocialShareTrait;
  use TitleAndLabelsTrait;
  use UserGreetingTrait;

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, NodeInterface $entity) {
    $elements = [];

    // Header.
    $element = $this->buildHeader($entity);
    $elements[] = $this->wrapContainerWide($element);

    // Main content and sidebar.
    $element = $this->buildMainAndSidebar($entity);
    $elements[] = $this->wrapContainerWide($element);

    $elements = $this->wrapContainerVerticalSpacingBig($elements);
    $build[] = $this->wrapContainerBottomPadding($elements);

    return $build;
  }

  /**
   * Build the header.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array
   *
   * @throws \IntlException
   */
  protected function buildHeader(NodeInterface $entity): array {
    $elements = [];

    // Show greeting if user has access
    $user = \Drupal::currentUser();
    if (!Og::isMember($entity, $user, [
      OgMembershipInterface::STATE_ACTIVE,
      OgMembershipInterface::STATE_PENDING,
    ])) { 
        $access = \Drupal::service('og.access')->userAccess($entity, 'subscribe', $user);
        if ($access->isAllowed()) {
          $elements[] = $this->buildGreeting($entity, $user);
        }
    }

    $elements[] = $this->buildConditionalPageTitle($entity);

    // Date.
    $timestamp = $this->getFieldOrCreatedTimestamp($entity, 'field_publish_date');
    $element = IntlDate::formatPattern($timestamp, 'long');
    // Make text bigger.
    $elements[] = $this->wrapTextDecorations($element, FALSE, FALSE, 'lg');

    $elements = $this->wrapContainerVerticalSpacing($elements);
    return $this->wrapContainerNarrow($elements);
  }

  /**
   * Build the Main content and the sidebar.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array
   *
   * @throws \IntlException
   */
  protected function buildMainAndSidebar(NodeInterface $entity): array {
    $main_elements = [];
    $sidebar_elements = [];
    $social_share_elements = [];

    // Get the body text
    $main_elements[] = $this->buildProcessedText($entity, 'body');

    // Get the og group field, and social share.
    $sidebar_elements[] = $entity->og_group->view('full');

    // Add a line separator above the social share buttons.
    $social_share_elements[] = $this->buildLineSeparator();
    $social_share_elements[] = $this->buildSocialShare($entity);

    $sidebar_elements[] = $this->wrapContainerVerticalSpacing($social_share_elements);

    return [
      '#theme' => 'server_theme_main_and_sidebar',
      '#main' => $this->wrapContainerVerticalSpacingBig($main_elements),
      '#sidebar' => $this->wrapContainerVerticalSpacingBig($sidebar_elements),
    ];

  }

  /**
   * Build Teaser view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildTeaser(array $build, NodeInterface $entity) {
    $timestamp = $this->getFieldOrCreatedTimestamp($entity, 'field_publish_date');

    $element = [
      '#theme' => 'server_theme_card',
      '#title' => $entity->label(),
      '#image' => NULL,
      '#date' => IntlDate::formatPattern($timestamp, 'short'),
      '#url' => $entity->toUrl(),
    ];
    $build[] = $element;

    return $build;
  }

  /**
   * Build "Search index" view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildSearchIndex(array $build, NodeInterface $entity) {
    $timestamp = $this->getFieldOrCreatedTimestamp($entity, 'field_publish_date');

    $element = [
      '#theme' => 'server_theme_search_result',
      '#labels' => $this->buildLabelsFromText(['Group']),
      '#title' => $entity->label(),
      '#summary' => $this->buildProcessedText($entity, 'body', FALSE),
      '#date' => IntlDate::formatPattern($timestamp, 'short'),
      '#url' => $entity->toUrl(),
    ];

    $build[] = $element;

    return $build;
  }

}
