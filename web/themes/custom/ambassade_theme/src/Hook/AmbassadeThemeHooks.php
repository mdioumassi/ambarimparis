<?php

namespace Drupal\ambassade_theme\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for ambassade_theme.
 */
class AmbassadeThemeHooks {
  /**
   * @file
   * Functions to support theming.
   */

  /**
   * Implements hook_preprocess_image_widget().
   *
   * @param array<string, mixed> $variables
   *   The template variables.
   */
  #[Hook('preprocess_image_widget')]
  public function preprocessImageWidget(array &$variables): void {
    $data = &$variables['data'];
    // This prevents image widget templates from rendering preview container
    // HTML to users that do not have permission to access these previews.
    // @todo revisit in https://drupal.org/node/953034
    // @todo revisit in https://drupal.org/node/3114318
    if (isset($data['preview']['#access']) && $data['preview']['#access'] === FALSE) {
      unset($data['preview']);
    }
  }

  /**
   * Implements hook_preprocess_node().
   *
   * Exposes the site name so node templates can build correct JSON-LD
   * structured data (NewsArticle publisher, etc.) without hardcoding it.
   *
   * @param array<string, mixed> $variables
   *   The template variables.
   */
  #[Hook('preprocess_node')]
  public function preprocessNode(array &$variables): void {
    $variables['site_name'] = \Drupal::config('system.site')->get('name');
    /** @var \Drupal\node\NodeInterface $node */
    $node = $variables['node'];
    $variables['canonical_url'] = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  }

}
