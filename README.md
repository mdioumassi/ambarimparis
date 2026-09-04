# Ambarimparis — Site institutionnel de l'Ambassade de Mauritanie en France

Refonte du site institutionnel de l'Ambassade de la République Islamique de
Mauritanie en France sur Drupal 11, en remplacement de l'ancien site WordPress
(ambarimparis.fr).

## Stack technique

- **CMS** : Drupal 11 (Composer, `drupal/recommended-project`)
- **PHP** : 8.3
- **Base de données** : MariaDB 10.11
- **Environnement local** : [DDEV](https://ddev.com/)
- **Thème** : `ambassade_theme` (custom, voir `web/themes/custom/`)
- **Gestion de configuration** : `config/sync` versionné (`drush cex` / `drush cim`)
- **CI** : GitLab CI (lint PHP/Twig/CSS, PHPStan niveau 6, tests, build, déploiement)

## Prérequis

- [DDEV](https://ddev.com/get-started/) ≥ 1.24
- Docker
- Composer 2
- Git

## Installation locale

```bash
git clone <url-du-depot> ambarimparis
cd ambarimparis

# Démarrer l'environnement DDEV (PHP 8.3, MariaDB 10.11, nginx-fpm)
ddev start

# Installer les dépendances PHP
ddev composer install

# Installer le site (si base de données vide)
ddev drush site-install minimal \
  --locale=fr \
  --site-name="Ambassade de Mauritanie en France" \
  --account-name=admin \
  --account-pass=<mot-de-passe> \
  -y

# Importer la configuration versionnée
ddev drush config:import -y

# Vider les caches
ddev drush cache:rebuild
```

Le site est ensuite accessible via `ddev launch` (URL type
`https://ambarimparis.ddev.site`).

## Langues

- **Français (fr)** : langue par défaut, préfixe d'URL vide (`/actualites`).
- **Arabe (ar)** : RTL, préfixe d'URL `/ar` (`/ar/actualites`).

## Rôles éditoriaux

| Rôle | Usage |
|---|---|
| `contributeur` | Rédaction de contenu (brouillon, soumission pour relecture) |
| `editeur` | Relecture, publication, gestion des traductions |
| `webmestre` | Administration complète du site |

Workflow éditorial (`content_moderation`) : **Brouillon → Relecture → Publié**.

## Gestion de la configuration

Toute modification de configuration doit être exportée et versionnée :

```bash
ddev drush config:export -y
git add config/sync
git commit -m "config: <description>"
```

## Qualité de code

```bash
# Lint PHP (Drupal coding standards)
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom web/themes/custom

# Analyse statique PHPStan (niveau 6)
ddev exec vendor/bin/phpstan analyse

# Audit accessibilité (pa11y-ci) — voir Lot 4
```

## Documentation

- [`docs/decisions/`](docs/decisions/) — Journal des décisions techniques (ADR).
- [`docs/architecture.md`](docs/architecture.md) — Architecture du site (à venir, Lot 6).
- [`docs/guide-contributeur.md`](docs/guide-contributeur.md) — Guide back-office (à venir, Lot 6).

## Structure du dépôt

```
├── config/sync/          # Configuration Drupal versionnée
├── docs/                 # Documentation projet et décisions techniques
├── web/                  # Document root Drupal
│   ├── modules/custom/   # Modules métier
│   ├── themes/custom/    # Thème ambassade_theme
├── .ddev/                # Configuration DDEV
├── .gitlab-ci.yml        # Pipeline CI/CD
├── phpstan.neon          # Configuration PHPStan
└── composer.json
```
