<?php

/**
 * @file
 * Creates minimal demo content for automated CI audits.
 *
 * Used by pa11y-ci and Lighthouse CI so they have real démarche/actualité
 * pages to test, not just empty listings.
 *
 * Usage: drush php:script scripts/ci-demo-content.php.
 */

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

$service = Term::create(['vid' => 'services', 'name' => 'Service CI']);
$service->save();

$type = Term::create(['vid' => 'types_actualite', 'name' => 'Communiqué CI']);
$type->save();

Node::create([
  'type' => 'demarche',
  'title' => 'Démarche de démonstration (CI)',
  'path' => ['alias' => '/lighthouse-demo-demarche', 'pathauto' => FALSE],
  'field_public_concerne' => ['value' => 'Public concerné de démonstration.', 'format' => 'basic_html'],
  'field_pieces_a_fournir' => [['value' => 'Pièce 1'], ['value' => 'Pièce 2']],
  'field_delais' => '5 jours',
  'field_modalites' => ['value' => 'Modalités de démonstration.', 'format' => 'basic_html'],
  'field_service' => $service->id(),
  'moderation_state' => 'published',
])->save();

Node::create([
  'type' => 'actualite',
  'title' => 'Actualité de démonstration (CI)',
  'path' => ['alias' => '/lighthouse-demo-actualite', 'pathauto' => FALSE],
  'field_chapo' => ['value' => 'Chapô de démonstration.', 'format' => 'basic_html'],
  'body' => ['value' => 'Corps de démonstration.', 'format' => 'basic_html'],
  'field_type_actualite' => $type->id(),
  'field_date_publication' => date('Y-m-d'),
  'moderation_state' => 'published',
])->save();

echo "CI demo content created.\n";
