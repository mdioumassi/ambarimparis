# Architecture

Vue d'ensemble du site institutionnel de l'Ambassade de la République
Islamique de Mauritanie en France. Pour le détail des choix et leur
justification, voir [`docs/decisions/`](decisions/).

## Stack technique

| Couche | Choix |
|---|---|
| CMS | Drupal 11 (`drupal/recommended-project`, docroot `web/`) |
| Langage | PHP 8.3 |
| Base de données | MariaDB 10.11 |
| Environnement local | DDEV (nginx-fpm) |
| Thème | `ambassade_theme`, custom, basé sur `core/starterkit_theme` |
| Configuration | `config/sync/` versionné (`drush cex`/`cim`) |
| CI/CD | GitLab CI |

## Modèle de contenus

Quatre types de contenu métier, tous multilingues (FR par défaut, AR RTL)
et soumis au workflow éditorial `editorial` (`content_moderation` :
Brouillon → Relecture → Publié) :

- **`demarche`** — fiche de démarche consulaire (public concerné, pièces à
  fournir, délais, tarifs, modalités, formulaires PDF, FAQ, service de
  rattachement).
- **`actualite`** — actualité/communiqué (chapô, corps, image, type, date
  de publication).
- **`page`** — page éditoriale libre, corps riche + composants
  (paragraphes).
- **`alerte`** — bandeau d'alerte temporaire (niveau, fenêtre de dates),
  n'a pas de gabarit de page dédié : rendue uniquement en bandeau via la
  vue `alertes_actives`.

Taxonomies : `services` (rattachement des démarches), `types_actualite`
(communiqué / événement / presse).

### Paragraphes (composants réutilisables)

| Paragraphe | Usage |
|---|---|
| `question_reponse` | FAQ des démarches, bloc accordéon des pages |
| `texte` | Bloc de texte riche |
| `image` | Visuel + légende |
| `accordeon` / `accordeon_item` | Accordéon (pages) |
| `mise_en_avant` | Bandeau promotionnel image + texte + CTA |
| `carte` / `grille_de_cartes` | Grille de cartes, aussi réutilisée pour
  les 3 actions du hero d'accueil (mode d'affichage `hero_action`) |
| `formulaire` | Intègre un formulaire Webform dans une page (`/contact`) |

### Médias

Bundles `image` et `document` du module `media` (config standard core
réutilisée), avec bibliothèque de médias (`media_library`).

## Page d'accueil

Pilotée par des régions de thème dédiées (`hero`, `quick_access`,
`contact_block`) peuplées par des blocs personnalisés
(`block_content` : `hero_accueil`, `acces_rapides`, `contact_accueil`)
visibles uniquement sur `<front>`. Le nœud « Accueil » (`page`, corps
vide) n'est qu'une ancre technique pour la route `<front>` — voir
[decision 010](decisions/010-page-accueil-blocs-structures.md).

## Recherche

`search_api` (backend base de données) indexe `demarche`/`actualite`/
`page` (titre, corps, chapô, public concerné...). La vue `recherche`
(`/recherche`) expose un filtre plein texte avec autocomplétion
(`search_api_autocomplete`).

## Multilingue

`fr` (défaut, préfixe vide) / `ar` (RTL, préfixe `/ar`), négociation par
préfixe d'URL. Contenu traduisible sur les 4 types de contenu et les 2
vocabulaires. Polices auto-hébergées : Source Sans 3 (latin), Noto Naskh
Arabic (arabe) — voir [decision 009](decisions/009-design-tokens-polices.md).

## Sécurité

`seckit` (CSP, X-Frame-Options, Referrer-Policy), `honeypot` + `antibot`
(anti-spam formulaires), rôles `contributeur`/`editeur`/`webmestre` à
permissions additives et minimales.

## SEO / RGPD

Metatag + Schema.org (`GovernmentOrganization`/`WebSite` sitewide,
`FAQPage`/`NewsArticle` en JSON-LD directement dans les gabarits — voir
[decision 013](decisions/013-seo-rgpd-performance-lot5.md)), sitemap XML,
redirections 301 (structure d'import prête pour l'export WordPress),
bandeau cookies bloquant (`eu_cookie_compliance`, mode `opt_in`), Matomo
en mesure d'audience exemptée.

## Performance

Agrégation CSS/JS, cache des pages anonymes (15 min), images WebP
(`image_convert` core) + image responsive alignée sur les breakpoints du
thème (360/768/1024/1440), lazy loading, `big_pipe`. Audits automatisés
en CI : `pa11y-ci` (accessibilité) et Lighthouse CI (performance ≥ 90
mobile) sur un site jetable SQLite avec contenu de démonstration.

## Arborescence du dépôt

```
├── config/sync/              Configuration Drupal versionnée
├── docs/
│   ├── decisions/             Journal des décisions techniques (ADR)
│   ├── migration/              Gabarit d'import des redirections WordPress
│   ├── architecture.md         Ce document
│   └── guide-contributeur.md   Guide back-office
├── scripts/
│   ├── create-demo-content.php Contenu de démonstration (idempotent)
│   ├── ci-demo-content.php     Contenu minimal pour les audits CI
│   └── import-redirects.php    Import des redirections 301
├── web/
│   ├── modules/custom/         (réservé aux futurs modules métier)
│   └── themes/custom/ambassade_theme/
├── .gitlab-ci.yml              Pipeline CI/CD
├── .pa11yci.json / .lighthouserc.json
└── phpstan.neon
```
