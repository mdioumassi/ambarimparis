# 013 — SEO, RGPD, performance (Lot 5)

## SEO

- **Metatag** : défauts globaux (`metatag.metatag_defaults.global`) +
  défauts par type de contenu (`node__demarche`, `node__actualite`,
  `node__page`) — titre, description, Open Graph.
- **Schema.org** :
  - `GovernmentOrganization` et `WebSite` au niveau global, via les
    sous-modules `schema_organization`/`schema_web_site` (propriétés
    simples `@type`/`name`/`url`, sans structure imbriquée).
  - `FAQPage` (démarches) et `NewsArticle` (actualités) sont générés
    **directement en Twig** (`node--demarche.html.twig`,
    `node--actualite.html.twig`) plutôt que via les sous-modules
    `schema_metatag` correspondants : ces types requièrent des structures
    imbriquées/répétées (liste de `Question`/`Answer`, objets
    `Organization` imbriqués) que schema_metatag n'expose que via son
    formulaire d'administration (assemblage de JSON par sous-propriété) ;
    aucun sous-module « FAQPage » n'est d'ailleurs fourni dans le paquet
    `schema_metatag` installé. Générer ce JSON-LD à la main dans le
    gabarit, à partir des mêmes champs que l'affichage, garantit un
    résultat correct et vérifiable plutôt que de deviner un format de
    configuration complexe.
  - Le nom du site est exposé aux gabarits de nœud via
    `hook_preprocess_node()` (`AmbassadeThemeHooks::preprocessNode()`)
    pour éviter de le coder en dur dans le JSON-LD.
- **Sitemap XML** (`simple_sitemap`) : `demarche`/`actualite`/`page`
  indexés, `alerte` exclu (bandeau transitoire, pas une page). Le nœud
  « Accueil » (ancre technique de la route `<front>`, cf. décision 010)
  est explicitement exclu pour éviter un contenu dupliqué avec `/`.
- **robots.txt** : exclu du scaffolding Composer
  (`composer.json` → `extra.drupal-scaffold.file-mapping`) pour pouvoir le
  versionner avec une ligne `Sitemap: /sitemap.xml` ajoutée.
- **Redirections 301** : structure d'import prête
  (`docs/migration/redirects-wordpress.csv` = format attendu,
  `scripts/import-redirects.php` = script d'import idempotent) en
  attendant l'export réel des anciennes URLs WordPress.

## RGPD

- Pages Mentions légales / Politique de confidentialité / Déclaration
  d'accessibilité : contenu structuré par sections avec placeholders
  `[À VALIDER]` (responsable de traitement, hébergeur, DPO, résultats
  d'audit RGAA, etc.) — jamais de contenu juridique définitif généré.
- **Bandeau cookies** (`eu_cookie_compliance`) : mode `opt_in` (blocage
  par défaut jusqu'au consentement, déjà actif depuis le Lot 1), textes
  en français, lien « en savoir plus » vers la politique de
  confidentialité, réglages d'accessibilité renforcés
  (`better_support_for_screen_readers`), bouton de retrait du
  consentement.
- **Matomo** (`drupal/matomo`) configuré en **mesure d'audience
  exemptée** au sens CNIL : cookies désactivés
  (`privacy.disablecookies`), Do Not Track honoré
  (`privacy.donottrack`), pas de suivi d'identifiant utilisateur — ce qui
  dispense légalement du recueil de consentement pour cet outil.
  `site_id`/`url_https` sont des valeurs placeholder `[À VALIDER]` : le
  serveur Matomo réel n'est pas encore provisionné. La CSP (`seckit`) a
  été mise à jour pour autoriser ce domaine placeholder — **à corriger**
  avec le vrai domaine Matomo avant mise en production.

## Performance

- Agrégation CSS/JS : déjà active par défaut (`system.performance`).
- Cache des pages anonymes : `cache.page.max_age` porté à 900s (15 min).
  Sans risque pour le bandeau d'alerte : la vue `alertes_actives`
  force `max-age: 0` sur son propre rendu, ce qui plafonne le cache de
  toute page qui l'affiche à 0 quel que soit ce réglage global.
- `big_pipe` activé (perception de performance, notamment pour les
  utilisateurs authentifiés).
- **Images WebP** : styles d'image `card_webp` / `card_webp_small` /
  `content_webp` (effet `image_convert` natif de Drupal core, pas de
  module supplémentaire), plus un style d'image responsive
  (`card_responsive`) mappé sur les points de rupture du thème
  (`ambassade_theme.breakpoints.yml`, alignés sur 360/768/1024/1440).
  Formatters mis à jour sur `actualite` (teaser + vue complète) avec
  chargement différé (`loading="lazy"`).
- **Lighthouse CI** (`test:lighthouse` dans `.gitlab-ci.yml`,
  `.lighthouserc.json`) : audite un site jetable SQLite avec contenu de
  démonstration (`scripts/ci-demo-content.php`, réutilisé aussi par
  `test:a11y`) sur accueil/démarche/actualité, seuil 90 (mobile) en
  performance et accessibilité. `allow_failure: true` pour les mêmes
  raisons que `test:a11y` (Chromium headless non validé dans cet
  environnement de développement) — même action de suivi.

## Points de vigilance restants

- `[À VALIDER]` : domaine et identifiant Matomo réels.
- `[À VALIDER]` : contenu juridique définitif des 3 pages RGPD/légales.
- L'export réel des URLs WordPress est nécessaire pour peupler
  `docs/migration/redirects-wordpress.csv` au-delà de la ligne d'exemple.
