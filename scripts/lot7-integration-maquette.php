<?php

/**
 * @file
 * Intègre la maquette Figma « Accueil — Desktop 1440 » (016).
 *
 * Ajoute les champs manquants (titre du hero, sous-titre de la grille de
 * démarches, colonnes infos pratiques), met à jour le contenu de
 * démonstration des blocs personnalisés, le menu principal et le pied de
 * page pour correspondre à la maquette. Idempotent : peut être rejoué sans
 * dupliquer les champs ou le contenu.
 *
 * Usage: drush php:script scripts/lot7-integration-maquette.php.
 */

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Creates a field (storage + instance) on a block_content bundle if absent.
 *
 * @param array<string, mixed> $storage_settings
 *   Field storage settings, keyed by setting name.
 */
function lot7_add_field(string $bundle, string $field_name, string $type, string $label, array $storage_settings = []): void {
  if (!FieldStorageConfig::loadByName('block_content', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'block_content',
      'type' => $type,
      'settings' => $storage_settings,
    ])->save();
    echo "Field storage created: $field_name\n";
  }
  if (!FieldConfig::loadByName('block_content', $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'block_content',
      'bundle' => $bundle,
      'label' => $label,
    ])->save();
    echo "Field instance created: block_content.$bundle.$field_name\n";
  }
  \Drupal::service('entity_display.repository')
    ->getFormDisplay('block_content', $bundle)
    ->setComponent($field_name, ['type' => in_array($type, ['text_long'], true) ? 'text_textarea' : 'string_textfield'])
    ->save();
  \Drupal::service('entity_display.repository')
    ->getViewDisplay('block_content', $bundle)
    ->setComponent($field_name, ['type' => in_array($type, ['text_long'], true) ? 'text_default' : 'string'])
    ->save();
}

/**
 * Removes a field instance from a bundle if present (storage left intact).
 */
function lot7_remove_field(string $bundle, string $field_name): void {
  $instance = FieldConfig::loadByName('block_content', $bundle, $field_name);
  if ($instance) {
    $instance->delete();
    echo "Field instance removed: block_content.$bundle.$field_name\n";
  }
}

/**
 * Loads a block_content entity by its bundle (single instance expected).
 */
function lot7_block_content(string $bundle): ?BlockContent {
  $items = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['type' => $bundle]);
  return $items ? reset($items) : NULL;
}

// --- 1. Champs manquants -----------------------------------------------
lot7_add_field('hero_accueil', 'field_titre', 'string', 'Titre (H1)', ['max_length' => 255]);
lot7_add_field('acces_rapides', 'field_soustitre', 'string', 'Sous-titre', ['max_length' => 255]);
lot7_add_field('contact_accueil', 'field_horaires', 'text_long', "Horaires d'ouverture");
lot7_add_field('contact_accueil', 'field_adresse', 'text_long', 'Adresse');
lot7_add_field('contact_accueil', 'field_contact_infos', 'text_long', 'Contact');
lot7_remove_field('contact_accueil', 'field_webform');
lot7_remove_field('contact_accueil', 'field_carte_placeholder');

// --- 2. Contenu du hero ---------------------------------------------------
if ($hero = lot7_block_content('hero_accueil')) {
  $hero->set('field_titre', 'Vos démarches consulaires, simplifiées');
  $hero->set('field_texte_intro', [
    'value' => 'Visas, passeports, état civil : trouvez votre procédure et prenez rendez-vous en ligne.',
    'format' => 'basic_html',
  ]);
  $hero->save();
  echo "Hero accueil updated.\n";

  $actions = $hero->get('field_actions')->referencedEntities();
  $action_data = [
    ['Prendre rendez-vous', 'Réservez un créneau au service consulaire', '/demarches'],
    ['Demander un visa', 'Conditions, pièces à fournir et tarifs', '/demarches'],
    ['Passeport & CNI', 'Renouvellement et première demande', '/demarches'],
  ];
  foreach ($action_data as $i => [$titre, $texte, $lien]) {
    if (!isset($actions[$i])) {
      continue;
    }
    $actions[$i]->set('field_titre', $titre);
    $actions[$i]->set('field_texte_court', ['value' => $texte, 'format' => 'basic_html']);
    $actions[$i]->set('field_lien', ['uri' => 'internal:' . $lien, 'title' => 'Accéder']);
    $actions[$i]->save();
  }
  echo "Hero actions updated.\n";
}

// --- 3. Grille « Toutes vos démarches » -----------------------------------
$services = [];
foreach (['visas' => 'Service des visas', 'etat_civil' => "Service de l'état civil"] as $key => $name) {
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'services', 'name' => $name]);
  $services[$key] = $terms ? reset($terms) : NULL;
}

if ($acces = lot7_block_content('acces_rapides')) {
  $acces->set('field_soustitre', 'Chaque fiche détaille les pièces à fournir, les délais et les tarifs.');
  $acces->save();

  $grille = $acces->get('field_grille')->referencedEntities()[0] ?? NULL;
  if ($grille instanceof Paragraph) {
    // Remove the previous generic navigation cards.
    foreach ($grille->get('field_cartes')->referencedEntities() as $old_card) {
      $old_card->delete();
    }

    $demarche_cards = [
      ['Visas', 'Court séjour, affaires, tourisme', $services['visas'] ? '/demarches?service=' . $services['visas']->id() : '/demarches'],
      ['Passeports & CNI', 'Première demande et renouvellement', '/demarches'],
      ['État civil', 'Naissance, mariage, décès', $services['etat_civil'] ? '/demarches?service=' . $services['etat_civil']->id() : '/demarches'],
      ['Procurations & légalisations', 'Actes et signatures certifiés', '/demarches'],
      // "[À VALIDER]" : aucune démarche « laissez-passer » n'existe encore
      // dans le contenu de démonstration (cf. 016) ; lien générique en
      // attendant la fiche dédiée.
      ['Laissez-passer', "Documents de voyage d'urgence", '/demarches'],
      ['Espace étudiants', 'Bourses, attestations, inscriptions', '/demarches'],
    ];
    $new_cards = [];
    foreach ($demarche_cards as [$titre, $texte, $lien]) {
      $card = Paragraph::create([
        'type' => 'carte',
        'field_titre' => $titre,
        'field_texte_court' => ['value' => $texte, 'format' => 'basic_html'],
        'field_lien' => ['uri' => 'internal:' . $lien, 'title' => 'Consulter la fiche'],
      ]);
      $card->save();
      $new_cards[] = $card;
    }
    $grille->set('field_cartes', $new_cards);
    $grille->save();
    echo "Grille de démarches updated (6 cartes).\n";
  }
}

// --- 4. Infos pratiques (bloc contact_accueil) ---------------------------
if ($contact = lot7_block_content('contact_accueil')) {
  $contact->set('field_horaires', [
    'value' => '<p>Lundi – Vendredi : 9h30 – 16h30</p><p>Dépôt des dossiers : 9h30 – 12h30</p><p>Retrait des documents : 14h – 16h</p>',
    'format' => 'basic_html',
  ]);
  $contact->set('field_adresse', [
    'value' => '<p>5 rue de Montevideo</p><p>75116 Paris</p><p>Métro : Rue de la Pompe (ligne 9)</p>',
    'format' => 'basic_html',
  ]);
  $contact->set('field_contact_infos', [
    'value' => '<p>contact@ambassade-mauritanie.fr</p><p>+33 1 XX XX XX XX</p><p><a href="/contact">Formulaire de contact en ligne</a></p>',
    'format' => 'basic_html',
  ]);
  $contact->save();
  echo "Contact accueil (infos pratiques) updated.\n";
}

// --- 5. Alerte de démonstration alignée sur la maquette -------------------
$alertes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'alerte']);
if ($alerte = reset($alertes)) {
  $alerte->set('field_message', [
    'value' => "<p>L'Ambassade sera exceptionnellement fermée le 28 novembre (Fête de l'Indépendance).</p>",
    'format' => 'basic_html',
  ]);
  $alerte->set('field_niveau', 'important');
  $alerte->set('field_date_debut', gmdate('Y-m-d\TH:i:s'));
  $alerte->set('field_date_fin', gmdate('Y-m-d\TH:i:s', strtotime('+90 days')));
  $alerte->save();
  echo "Alerte updated and reactivated.\n";
}

// --- 6. Pages de menu manquantes (placeholders documentés) ---------------
foreach (['La Mauritanie', 'Espace étudiants'] as $title) {
  $existing = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'page', 'title' => $title]);
  if (!$existing) {
    Node::create([
      'type' => 'page',
      'title' => $title,
      'body' => [
        'value' => '<p>[À VALIDER] Contenu de la rubrique « ' . $title . ' » à fournir par le service concerné.</p>',
        'format' => 'basic_html',
      ],
      'moderation_state' => 'published',
    ])->save();
    echo "Page created: $title\n";
  }
}

// --- 7. Menu principal (ordre et entrées de la maquette) ------------------
function lot7_node_url(string $title): string {
  $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['title' => $title]);
  $node = reset($nodes);
  return $node ? 'internal:/node/' . $node->id() : 'internal:/';
}

$main_links = [
  ['title' => 'Démarches consulaires', 'uri' => 'internal:/demarches', 'weight' => 0],
  ['title' => 'Espace étudiants', 'uri' => lot7_node_url('Espace étudiants'), 'weight' => 1],
  ['title' => "L'Ambassade", 'uri' => lot7_node_url("Présentation de l'ambassade"), 'weight' => 2],
  ['title' => 'La Mauritanie', 'uri' => lot7_node_url('La Mauritanie'), 'weight' => 3],
  ['title' => 'Actualités', 'uri' => 'internal:/actualites', 'weight' => 4],
  ['title' => 'Contact', 'uri' => 'internal:/contact', 'weight' => 5],
];
foreach ($main_links as $data) {
  $existing = \Drupal::entityTypeManager()->getStorage('menu_link_content')
    ->loadByProperties(['menu_name' => 'main', 'title' => $data['title']]);
  $link = $existing ? reset($existing) : MenuLinkContent::create(['menu_name' => 'main']);
  $link->set('title', $data['title']);
  $link->set('link', ['uri' => $data['uri']]);
  $link->set('weight', $data['weight']);
  $link->set('enabled', TRUE);
  $link->save();
}
echo "Main menu updated (6 entries).\n";

// --- 8. Pied de page : 4 colonnes -----------------------------------------
// Une colonne = un bloc "basic" avec son propre libellé de bloc (label_display
// activé au placement, section 11) plutôt qu'un <h2> codé dans le corps :
// chaque bloc devient ainsi une cellule de .footer-grid sans artifice CSS.
$footer_columns = [
  '4ca579af-aca3-42cf-8ccc-674dfad3d681' => '<ul>'
    . '<li><a href="/demarches">Visas</a></li>'
    . '<li><a href="/demarches">Passeports &amp; CNI</a></li>'
    . '<li><a href="/demarches">État civil</a></li>'
    . '<li><a href="/demarches">Procurations</a></li>'
    . '<li><a href="/demarches">Tarifs &amp; délais</a></li>'
    . '</ul>',
  '6f6c7838-beb0-4640-b6aa-0fe90774a708' => '<ul>'
    . '<li><a href="' . str_replace('internal:', '', lot7_node_url("Présentation de l'ambassade")) . '">Présentation</a></li>'
    . '<li><a href="#">[À VALIDER] Culture &amp; tourisme</a></li>'
    . '<li><a href="#">[À VALIDER] Économie</a></li>'
    . '<li><a href="#">[À VALIDER] Diplomatie</a></li>'
    . '</ul>',
];
foreach ($footer_columns as $uuid => $body) {
  $items = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $uuid]);
  if ($bc = reset($items)) {
    $bc->set('body', ['value' => $body, 'format' => 'basic_html']);
    $bc->save();
  }
}

$existing_ambassade_col = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => "Pied de page — L'Ambassade"]);
if ($existing_ambassade_col) {
  $footer_ambassade = reset($existing_ambassade_col);
}
else {
  $footer_ambassade = BlockContent::create([
    'type' => 'basic',
    'info' => "Pied de page — L'Ambassade",
  ]);
}
$footer_ambassade->set('body', [
  'value' => '<ul>'
    . '<li><a href="' . str_replace('internal:', '', lot7_node_url("L'ambassadeur")) . '">Mot de l\'Ambassadeur</a></li>'
    . '<li><a href="#">[À VALIDER] Équipe &amp; services</a></li>'
    . '<li><a href="#">[À VALIDER] Coopération bilatérale</a></li>'
    . '<li><a href="/contact">Horaires &amp; accès</a></li>'
    . '</ul>',
  'format' => 'basic_html',
]);
$footer_ambassade->save();
echo "Footer columns updated.\n";

// --- 8bis. Copyright (bas de page) -----------------------------------------
$existing_copyright = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => 'Copyright']);
if ($existing_copyright) {
  $copyright = reset($existing_copyright);
}
else {
  $copyright = BlockContent::create(['type' => 'basic', 'info' => 'Copyright']);
}
$copyright->set('body', [
  'value' => '<p>© ' . date('Y') . ' Ambassade de la République Islamique de Mauritanie en France</p>',
  'format' => 'basic_html',
]);
$copyright->save();
echo "Copyright block updated.\n";

// --- 9. Bloc topbar « Urgences consulaires » -------------------------------
$existing_topbar = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => 'Urgences consulaires']);
if (!$existing_topbar) {
  $topbar = BlockContent::create([
    'type' => 'basic',
    'info' => 'Urgences consulaires',
    'body' => [
      // [À VALIDER] : numéro d'urgence réel à confirmer par le service
      // consulaire.
      'value' => '<p>Urgences consulaires : +33 1 XX XX XX XX</p>',
      'format' => 'basic_html',
    ],
  ]);
  $topbar->save();
  echo "Block created: Urgences consulaires (uuid=" . $topbar->uuid() . ")\n";
}
else {
  $topbar = reset($existing_topbar);
}

// --- 10. Vue « Actualités » : affichage bloc (3 dernières) -----------------
$view_storage = \Drupal::entityTypeManager()->getStorage('view');
/** @var \Drupal\views\Entity\View $actualites_view */
$actualites_view = $view_storage->load('actualites');
$displays = $actualites_view->get('display');
{
  $displays['block_1'] = [
    'id' => 'block_1',
    'display_title' => 'Bloc accueil',
    'display_plugin' => 'block',
    'position' => 3,
    'display_options' => [
      'display_extenders' => [],
      'pager' => [
        'type' => 'some',
        'options' => ['offset' => 0, 'items_per_page' => 3],
      ],
      'use_more' => TRUE,
      'use_more_always' => TRUE,
      'use_more_text' => 'Toutes les actualités',
      'link_display' => 'page_1',
      'defaults' => ['pager' => FALSE, 'use_more' => FALSE, 'use_more_always' => FALSE, 'use_more_text' => FALSE, 'link_display' => FALSE],
    ],
    'cache_metadata' => [
      'max-age' => -1,
      'contexts' => ['languages:language_interface', 'url.query_args', 'user.node_grants:view', 'user.permissions'],
      'tags' => [],
    ],
  ];
  $actualites_view->set('display', $displays);
  $actualites_view->save();
  echo "View display updated: actualites/block_1\n";
}

// --- 11. Placement des blocs (régions topbar / news / footer) -------------
/**
 * Creates or updates a block placement.
 *
 * @param array<string, mixed> $config
 *   Block entity property values (region, weight, plugin, settings, ...).
 */
function lot7_place_block(string $id, array $config): void {
  $existing = Block::load($id);
  if ($existing) {
    foreach ($config as $key => $value) {
      $existing->set($key, $value);
    }
    $existing->save();
  }
  else {
    Block::create(array_merge(['id' => $id, 'theme' => 'ambassade_theme'], $config))->save();
  }
  echo 'Block placed/updated: ' . $id . "\n";
}

lot7_place_block('ambassade_topbar_urgences', [
  'region' => 'topbar',
  'weight' => -10,
  'plugin' => 'block_content:' . $topbar->uuid(),
  'settings' => [
    'id' => 'block_content:' . $topbar->uuid(),
    'label' => 'Urgences consulaires',
    'label_display' => '0',
    'provider' => 'block_content',
    'view_mode' => 'full',
  ],
  'visibility' => [],
]);

// Le sélecteur de langue de l'en-tête migre vers la topbar.
if ($lang_block = Block::load('ambassade_language_switcher')) {
  $lang_block->set('region', 'topbar');
  $lang_block->set('weight', 0);
  $lang_block->save();
  echo "Block moved: ambassade_language_switcher -> topbar\n";
}

lot7_place_block('ambassade_language_switcher_footer', [
  'region' => 'footer_bottom',
  'weight' => 0,
  'plugin' => 'language_block:language_interface',
  'settings' => [
    'id' => 'language_block:language_interface',
    'label' => 'Langue',
    'label_display' => '0',
    'provider' => 'core',
  ],
  'visibility' => [],
]);

// Menu légal : de "footer_bottom" (ligne) vers "footer_top" (4e colonne).
if ($legal_block = Block::load('ambassade_footer_legal_menu')) {
  $legal_block->set('region', 'footer_top');
  $legal_block->set('weight', 30);
  $settings = $legal_block->get('settings');
  $settings['label'] = 'Informations légales';
  $settings['label_display'] = '1';
  $legal_block->set('settings', $settings);
  $legal_block->save();
  echo "Block moved: ambassade_footer_legal_menu -> footer_top (titled column)\n";
}

// Colonnes 1-3 du pied de page : chaque bloc porte désormais son libellé.
if ($liens_block = Block::load('ambassade_footer_liens')) {
  $settings = $liens_block->get('settings');
  $settings['label'] = 'Démarches';
  $settings['label_display'] = '1';
  $liens_block->set('settings', $settings);
  $liens_block->set('weight', 0);
  $liens_block->save();
}
lot7_place_block('ambassade_footer_ambassade', [
  'region' => 'footer_top',
  'weight' => 10,
  'plugin' => 'block_content:' . $footer_ambassade->uuid(),
  'settings' => [
    'id' => 'block_content:' . $footer_ambassade->uuid(),
    'label' => "L'Ambassade",
    'label_display' => '1',
    'provider' => 'block_content',
    'view_mode' => 'full',
  ],
  'visibility' => [],
]);
if ($horaires_block = Block::load('ambassade_footer_horaires')) {
  $settings = $horaires_block->get('settings');
  $settings['label'] = 'La Mauritanie';
  $settings['label_display'] = '1';
  $horaires_block->set('settings', $settings);
  $horaires_block->set('weight', 20);
  $horaires_block->save();
}

lot7_place_block('ambassade_footer_copyright', [
  'region' => 'footer_bottom',
  'weight' => -10,
  'plugin' => 'block_content:' . $copyright->uuid(),
  'settings' => [
    'id' => 'block_content:' . $copyright->uuid(),
    'label' => 'Copyright',
    'label_display' => '0',
    'provider' => 'block_content',
    'view_mode' => 'full',
  ],
  'visibility' => [],
]);

lot7_place_block('ambassade_actualites_accueil', [
  'region' => 'news',
  'weight' => 0,
  'plugin' => 'views_block:actualites-block_1',
  'settings' => [
    'id' => 'views_block:actualites-block_1',
    'label' => 'Actualités & communiqués',
    'label_display' => '1',
    'provider' => 'views',
    'views_label' => '',
    'items_per_page' => 'none',
  ],
  'visibility' => [
    'request_path' => [
      'id' => 'request_path',
      'negate' => FALSE,
      'context_mapping' => [],
      'pages' => '<front>',
    ],
  ],
]);

// Le hero porte son propre H1 : le bloc "Titre de la page" (H1 du nœud
// "Accueil") est masqué en visibilité sur <front> plutôt qu'en CSS, pour
// éviter un second H1 dans le DOM (016 : "H1 unique").
if ($page_title_block = Block::load('ambassade_page_title')) {
  $page_title_block->set('visibility', [
    'request_path' => [
      'id' => 'request_path',
      'negate' => TRUE,
      'context_mapping' => [],
      'pages' => '<front>',
    ],
  ]);
  $page_title_block->save();
  echo "Block updated: ambassade_page_title hidden on <front>\n";
}

// Section "Toutes vos démarches" : bloc renommé, sous-titre visible.
if ($acces_block = Block::load('ambassade_acces_rapides')) {
  $settings = $acces_block->get('settings');
  $settings['label'] = 'Toutes vos démarches';
  $settings['label_display'] = '1';
  $acces_block->set('settings', $settings);
  $acces_block->save();
  echo "Block updated: ambassade_acces_rapides label\n";
}

echo "Done.\n";
