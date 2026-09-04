<?php

/**
 * @file
 * Seeds realistic demonstration content for the Ambassade site.
 *
 * Creates the taxonomy terms, démarches, actualités, alerte and
 * institutional pages described in the Lot 6 delivery requirements.
 * Idempotent: safe to re-run, existing items (matched by title) are left
 * untouched rather than duplicated.
 *
 * Usage: drush php:script scripts/create-demo-content.php.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

/**
 * Loads a taxonomy term by name in a vocabulary, creating it if missing.
 */
function demo_term(string $vocabulary, string $name): Term {
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $existing = $storage->loadByProperties(['vid' => $vocabulary, 'name' => $name]);
  if ($existing) {
    return reset($existing);
  }
  $term = Term::create(['vid' => $vocabulary, 'name' => $name]);
  $term->save();
  echo "Term created: $vocabulary/$name\n";
  return $term;
}

/**
 * Loads a node by title and type, or NULL if it doesn't exist yet.
 */
function demo_node_exists(string $type, string $title): bool {
  $existing = \Drupal::entityTypeManager()->getStorage('node')
    ->loadByProperties(['type' => $type, 'title' => $title]);
  return (bool) $existing;
}

// --- Taxonomies --------------------------------------------------------
$services = [
  'visas' => demo_term('services', 'Service des visas'),
  'etat_civil' => demo_term('services', "Service de l'état civil"),
  'consulaire' => demo_term('services', 'Service consulaire général'),
];

$types_actualite = [
  'communique' => demo_term('types_actualite', 'Communiqué'),
  'evenement' => demo_term('types_actualite', 'Événement'),
  'presse' => demo_term('types_actualite', 'Presse'),
];

// --- Démarches -----------------------------------------------------------
$demarches = [
  [
    'title' => 'Renouvellement de passeport',
    'service' => 'consulaire',
    'public' => 'Ressortissants mauritaniens majeurs résidant en France.',
    'pieces' => ['Ancien passeport', 'Justificatif de domicile de moins de 3 mois', '2 photos d\'identité aux normes'],
    'delais' => '10 jours ouvrés',
    'tarifs' => '<p>[À VALIDER] Tarif consulaire en vigueur.</p>',
    'modalites' => '<p>Dépôt du dossier uniquement sur rendez-vous. Retrait en personne, sur présentation d\'une pièce d\'identité.</p>',
    'faq' => [
      [
        'Le passeport de mon enfant mineur suit-il la même procédure ?',
        'Non, une procédure spécifique s\'applique aux mineurs, avec autorisation des deux parents.',
      ],
    ],
  ],
  [
    'title' => 'Demande de visa touristique',
    'service' => 'visas',
    'public' => 'Toute personne souhaitant se rendre en Mauritanie à titre touristique.',
    'pieces' => [
      'Passeport valide 6 mois après la date de retour',
      'Formulaire de demande complété',
      '2 photos d\'identité',
      'Réservation de vol aller-retour',
    ],
    'delais' => '5 jours ouvrés',
    'tarifs' => '<p>[À VALIDER] Tarif selon la nationalité.</p>',
    'modalites' => '<p>Dépôt sur rendez-vous ou par courrier avec enveloppe de retour affranchie.</p>',
    'faq' => [
      [
        'Le visa peut-il être obtenu à l\'arrivée ?',
        '[À VALIDER] Se référer aux conditions en vigueur au moment du voyage.',
      ],
    ],
  ],
  [
    'title' => "Établissement d'une carte nationale d'identité",
    'service' => 'etat_civil',
    'public' => 'Ressortissants mauritaniens inscrits au registre consulaire.',
    'pieces' => ['Acte de naissance', 'Justificatif de domicile', '2 photos d\'identité'],
    'delais' => '15 jours ouvrés',
    'tarifs' => '<p>[À VALIDER]</p>',
    'modalites' => '<p>Dépôt sur rendez-vous, retrait en personne.</p>',
    'faq' => [],
  ],
  [
    'title' => 'Certificat de vie',
    'service' => 'consulaire',
    'public' => 'Ressortissants mauritaniens résidant en France percevant une pension.',
    'pieces' => ['Pièce d\'identité', 'Justificatif de domicile'],
    'delais' => 'Délivrance immédiate sur rendez-vous',
    'tarifs' => '<p>[À VALIDER]</p>',
    'modalites' => '<p>Présentation obligatoire en personne au guichet consulaire.</p>',
    'faq' => [],
  ],
  [
    'title' => 'Légalisation de documents',
    'service' => 'consulaire',
    'public' => 'Toute personne souhaitant faire authentifier un document destiné à la Mauritanie.',
    'pieces' => ['Document original', 'Copie du document', 'Pièce d\'identité du demandeur'],
    'delais' => '3 jours ouvrés',
    'tarifs' => '<p>[À VALIDER] Tarif par document.</p>',
    'modalites' => '<p>Dépôt sur rendez-vous ou par courrier.</p>',
    'faq' => [],
  ],
  [
    'title' => "Transcription d'acte de naissance",
    'service' => 'etat_civil',
    'public' => 'Parents d\'un enfant né en France souhaitant transcrire l\'acte de naissance à l\'état civil mauritanien.',
    'pieces' => [
      'Acte de naissance français (copie intégrale)',
      'Livret de famille ou acte de mariage des parents',
      'Pièces d\'identité des parents',
    ],
    'delais' => '20 jours ouvrés',
    'tarifs' => '<p>[À VALIDER]</p>',
    'modalites' => '<p>Dépôt sur rendez-vous, retrait en personne ou par courrier.</p>',
    'faq' => [],
  ],
];

foreach ($demarches as $data) {
  if (demo_node_exists('demarche', $data['title'])) {
    continue;
  }
  $faq_paragraphs = [];
  foreach ($data['faq'] as [$question, $reponse]) {
    $p = Paragraph::create([
      'type' => 'question_reponse',
      'field_question' => $question,
      'field_reponse' => ['value' => "<p>$reponse</p>", 'format' => 'basic_html'],
    ]);
    $p->save();
    $faq_paragraphs[] = $p;
  }

  $pieces = array_map(static fn ($value) => ['value' => $value], $data['pieces']);

  Node::create([
    'type' => 'demarche',
    'title' => $data['title'],
    'field_public_concerne' => ['value' => $data['public'], 'format' => 'basic_html'],
    'field_pieces_a_fournir' => $pieces,
    'field_delais' => $data['delais'],
    'field_tarifs' => ['value' => $data['tarifs'], 'format' => 'basic_html'],
    'field_modalites' => ['value' => $data['modalites'], 'format' => 'basic_html'],
    'field_faq' => $faq_paragraphs,
    'field_service' => $services[$data['service']]->id(),
    'moderation_state' => 'published',
  ])->save();
  echo "Démarche created: {$data['title']}\n";
}

// --- Actualités ------------------------------------------------------------
$actualites = [
  [
    'title' => "Ouverture exceptionnelle le samedi 14 février",
    'type' => 'communique',
    'chapo' => "L'ambassade ouvrira exceptionnellement ses portes le samedi 14 février pour l'accueil du public.",
    'body' => '<p>[À VALIDER] Détails pratiques : horaires, services concernés, modalités de prise de rendez-vous.</p>',
    'date' => '-10 days',
  ],
  [
    'title' => 'Journée culturelle mauritanienne à Paris',
    'type' => 'evenement',
    'chapo' => "L'ambassade organise une journée culturelle mettant à l'honneur l'artisanat et la gastronomie mauritanienne.",
    'body' => '<p>[À VALIDER] Programme, lieu, horaires et modalités d\'inscription.</p>',
    'date' => '-20 days',
  ],
  [
    'title' => 'Nouveaux horaires du service des visas',
    'type' => 'communique',
    'chapo' => 'Le service des visas adapte ses horaires d\'accueil à compter du mois prochain.',
    'body' => '<p>[À VALIDER] Détail des nouveaux horaires par service.</p>',
    'date' => '-5 days',
  ],
  [
    'title' => "Communiqué de presse : visite officielle",
    'type' => 'presse',
    'chapo' => "L'ambassade annonce la tenue d'une visite officielle de délégation mauritanienne en France.",
    'body' => '<p>[À VALIDER] Contenu du communiqué de presse.</p>',
    'date' => '-30 days',
  ],
  [
    'title' => 'Mise à jour de la procédure de demande de passeport',
    'type' => 'communique',
    'chapo' => 'La procédure de demande de renouvellement de passeport évolue à compter de ce mois-ci.',
    'body' => '<p>[À VALIDER] Détail des changements de procédure.</p>',
    'date' => '-2 days',
  ],
];

foreach ($actualites as $data) {
  if (demo_node_exists('actualite', $data['title'])) {
    continue;
  }
  Node::create([
    'type' => 'actualite',
    'title' => $data['title'],
    'field_chapo' => ['value' => $data['chapo'], 'format' => 'basic_html'],
    'body' => ['value' => $data['body'], 'format' => 'basic_html'],
    'field_type_actualite' => $types_actualite[$data['type']]->id(),
    'field_date_publication' => date('Y-m-d', strtotime($data['date'])),
    'moderation_state' => 'published',
  ])->save();
  echo "Actualité created: {$data['title']}\n";
}

// --- Alerte ------------------------------------------------------------------
if (!demo_node_exists('alerte', 'Fermeture exceptionnelle des services consulaires')) {
  Node::create([
    'type' => 'alerte',
    'title' => 'Fermeture exceptionnelle des services consulaires',
    'field_message' => [
      'value' => "<p>[À VALIDER] Les services consulaires seront fermés exceptionnellement. Merci de reprogrammer vos rendez-vous.</p>",
      'format' => 'basic_html',
    ],
    'field_niveau' => 'important',
    // Datetime fields are stored in UTC regardless of the site timezone.
    'field_date_debut' => gmdate('Y-m-d\TH:i:s'),
    'field_date_fin' => gmdate('Y-m-d\TH:i:s', strtotime('+30 days')),
    'moderation_state' => 'published',
  ])->save();
  echo "Alerte created.\n";
}

// --- Pages institutionnelles -------------------------------------------------
if (!demo_node_exists('page', "Présentation de l'ambassade")) {
  $texte = Paragraph::create([
    'type' => 'texte',
    'field_texte' => [
      'value' => "<p>L'Ambassade de la République Islamique de Mauritanie en France représente les intérêts de la Mauritanie sur le territoire français. Elle assure les fonctions diplomatiques et consulaires au service de la communauté mauritanienne et des relations bilatérales entre les deux pays.</p><p>[À VALIDER] Compléter avec le mot de l'Ambassadeur et les missions détaillées.</p>",
      'format' => 'basic_html',
    ],
  ]);
  $texte->save();

  $accordeon_items = [];
  foreach ([
    ['Missions diplomatiques', "[À VALIDER] Détail des missions diplomatiques de l'ambassade."],
    [
      'Missions consulaires',
      "[À VALIDER] Détail des missions consulaires (état civil, visas, protection des ressortissants).",
    ],
  ] as [$titre, $contenu]) {
    $item = Paragraph::create([
      'type' => 'accordeon_item',
      'field_titre' => $titre,
      'field_contenu' => ['value' => "<p>$contenu</p>", 'format' => 'basic_html'],
    ]);
    $item->save();
    $accordeon_items[] = $item;
  }
  $accordeon = Paragraph::create(['type' => 'accordeon', 'field_elements' => $accordeon_items]);
  $accordeon->save();

  Node::create([
    'type' => 'page',
    'title' => "Présentation de l'ambassade",
    'body' => ['value' => '', 'format' => 'basic_html'],
    'field_composants' => [$texte, $accordeon],
    'moderation_state' => 'published',
  ])->save();
  echo "Page created: Présentation de l'ambassade\n";
}

if (!demo_node_exists('page', "L'ambassadeur")) {
  Node::create([
    'type' => 'page',
    'title' => "L'ambassadeur",
    'body' => [
      'value' => "<p>[À VALIDER] Biographie et mot de l'Ambassadeur.</p>",
      'format' => 'basic_html',
    ],
    'moderation_state' => 'published',
  ])->save();
  echo "Page created: L'ambassadeur\n";
}

echo "Done.\n";
