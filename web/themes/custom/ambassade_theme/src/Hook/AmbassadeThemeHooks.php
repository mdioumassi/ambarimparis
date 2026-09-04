<?php

namespace Drupal\ambassade_theme\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\views\Plugin\views\exposed_form\ExposedFormPluginBase;
use Drupal\views\Views;

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

  /**
   * Implements hook_preprocess_block().
   *
   * Block_content entities have no dedicated "block_content" theme hook in
   * Drupal core (block_content.module never registers one — they render as
   * plain field arrays inside the "block" hook's `content` variable, see
   * \Drupal\block_content\Hook\BlockContentHooks::themeSuggestionsBlockAlter()
   * for the `block--block-content--type--BUNDLE` suggestions this theme
   * overrides). This exposes the block_content entity under `block_content`
   * so those overrides can read its plain field values, and — for the
   * homepage hero — builds the search form (exposed filter of the
   * "recherche" view) so it can sit between the intro text and the action
   * cards, a position no block region ordering could reach on its own since
   * all three come from the same block.
   *
   * @param array<string, mixed> $variables
   *   The template variables.
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(array &$variables): void {
    /** @var \Drupal\block_content\BlockContentInterface|null $block_content */
    $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
    if (!$block_content) {
      return;
    }
    $variables['block_content'] = $block_content;

    if ($block_content->bundle() !== 'hero_accueil') {
      return;
    }

    $view = Views::getView('recherche');
    if (!$view || !$view->access('page_1')) {
      return;
    }
    $view->setDisplay('page_1');
    $view->initHandlers();
    $exposed_form_plugin = $view->display_handler->getPlugin('exposed_form');
    if (!$exposed_form_plugin instanceof ExposedFormPluginBase) {
      return;
    }
    $form = $exposed_form_plugin->renderExposedForm(TRUE);
    unset($form['reset']);
    $variables['content']['search_form'] = $form;
  }

}
