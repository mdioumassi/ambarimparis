# PROMPT — Refonte du site de l'Ambassade de Mauritanie en France (Drupal 11)

## Contexte

Tu es développeur senior Drupal fullstack. Tu implémentes la refonte complète du site institutionnel de l'Ambassade de la République Islamique de Mauritanie en France (remplacement de ambarimparis.fr, actuellement sous WordPress 5.0 obsolète). Tu travailles de manière autonome : tu prends les décisions techniques, tu les documentes dans `docs/decisions/`, et tu ne me poses une question que si un choix est bloquant.

## Objectif

Livrer un site Drupal 11 institutionnel, bilingue FR/AR (RTL), orienté démarches consulaires, conforme RGAA 4.1 AA et RGPD, avec un thème sur mesure et un pipeline CI/CD.

## Stack imposée

- Drupal 11 (Composer, `drupal/recommended-project`), PHP 8.3, MariaDB 10.11
- Environnement local : DDEV
- Thème custom `ambassade_theme` basé sur Radix ou starterkit natif (pas de page builder, pas de Bootstrap complet — CSS custom avec design tokens)
- Gestion de configuration : `config/sync` versionné, `drush cex/cim`
- CI : GitLab CI (lint PHP/Twig/CSS, PHPStan niveau 6, tests, build, déploiement)
- Git : commits conventionnels, une branche par lot

## Lot 1 — Socle technique

1. Initialiser le projet Composer + DDEV, README d'installation.
2. Modules contrib : `admin_toolbar`, `pathauto`, `redirect`, `metatag`, `schema_metatag`, `simple_sitemap`, `search_api` + `search_api_autocomplete`, `webform`, `honeypot`, `antibot`, `smtp` (ou `symfony_mailer`), `eu_cookie_compliance` (ou `klaro`), `easy_responsive_images` (ou styles d'images responsive natifs + WebP).
3. Multilingue : activer `language`, `content_translation`, `config_translation`, `locale`. Langues : `fr` (défaut), `ar` (direction RTL). Sélecteur de langue persistant, hreflang.
4. Sécurité : `seckit` (CSP, X-Frame-Options, Referrer-Policy), désactivation des routes inutiles, rôles Contributeur / Éditeur / Webmestre avec permissions minimales, workflow éditorial (`content_moderation` : brouillon → relecture → publié).

## Lot 2 — Modèle de contenus

Créer les types de contenus suivants (champs traduisibles, pathauto configuré) :

- **Démarche consulaire** (`demarche`) : public concerné, pièces à fournir (multivalué), délais, tarifs, modalités de dépôt/retrait, formulaires PDF joints (media), FAQ (paragraphe question/réponse), service de rattachement (taxonomie).
- **Actualité / Communiqué** (`actualite`) : chapo, corps, image, type (taxonomie : communiqué, événement, presse), date de publication, flux RSS.
- **Page éditoriale** (`page`) : corps riche + composants (paragraphes : texte, image, accordéon, mise en avant, grille de cartes).
- **Alerte consulaire** (`alerte`) : message court, niveau (info/important/urgent), dates de début/fin — affichée en bandeau global tant qu'active.
- Taxonomies : `services`, `types_actualite`.
- Vues : liste actualités avec pagination + RSS, liste démarches par service, résultats de recherche avec autocomplétion.

## Lot 3 — Thème et design system

1. Design tokens en CSS custom properties : vert `#006233`, or `#FFC400`, rouge accent `#CE1126`, neutres ; typographies Source Sans 3 (latin) et Noto Naskh Arabic (arabe) auto-hébergées ; échelle typographique fluide (`clamp`).
2. Composants Twig + CSS (BEM) : bouton, carte démarche, carte actualité, bandeau d'alerte, fil d'Ariane, accordéon, pagination, formulaires. Chaque composant : états hover/focus visible/erreur, et support RTL via propriétés logiques (`margin-inline`, `padding-inline`...).
3. Gabarits : accueil (hero 3 actions : Prendre RDV, Demander un visa, Renouveler passeport/CNI ; bandeau alerte ; bloc actualités ; accès rapides ; bloc contact), fiche démarche, fiche actualité, page éditoriale, contact (webform + carte + horaires), 404/403.
4. Icônes : sprite SVG inline uniquement — aucune police d'icônes.
5. Footer complet : horaires, adresse, liens officiels, mentions légales, politique de confidentialité, déclaration d'accessibilité, plan du site.

## Lot 4 — Accessibilité et responsive

- Mobile-first, breakpoints 360/768/1024/1440.
- Interdits : `maximum-scale`, `user-scalable=0`. Zoom 200 % fonctionnel.
- Menu mobile : bouton burger avec `aria-expanded`, panneau avec piège de focus, fermeture Échap ; navigation clavier complète, liens d'évitement, landmarks.
- Cibles tactiles ≥ 44 px, contrastes AA vérifiés sur tous les tokens.
- Ajouter un audit automatisé (pa11y-ci ou axe-core) dans la CI sur les gabarits clés.

## Lot 5 — SEO, RGPD, performance

- Metatag + Schema.org : `GovernmentOffice` (site), `FAQPage` (démarches), `NewsArticle` (actualités). Sitemap XML, robots.txt.
- Module `redirect` : préparer le fichier de mapping des 301 depuis les anciennes URLs WordPress (je fournirai l'export ; crée la structure d'import).
- RGPD : pages mentions légales et confidentialité (contenu placeholder structuré), bandeau cookies bloquant les traceurs non essentiels, Matomo en mesure d'audience exemptée.
- Performance : agrégation CSS/JS, images responsive WebP, lazy loading, cache pages anonymes, cibles Lighthouse ≥ 90 mobile sur accueil/démarche/actualité — ajouter Lighthouse CI.

## Lot 6 — Livraison

- `docs/` : installation, guide contributeur back-office, architecture, décisions.
- Contenu de démonstration réaliste (module `default_content` ou recipe) : 6 démarches, 5 actualités, 1 alerte, pages institutionnelles.
- Pipeline GitLab CI complet ; script de déploiement (`drush deploy`).

## Méthode de travail

- Avance lot par lot dans l'ordre. À la fin de chaque lot : exécute les vérifications (site fonctionnel via DDEV, `drush cex` propre, lint OK), commite, puis fais un résumé de 5 lignes maximum et enchaîne.
- Toute décision non spécifiée : tranche selon les bonnes pratiques Drupal, note-la dans `docs/decisions/NNN-titre.md`.
- Ne génère jamais de contenu juridique définitif (mentions légales, RGPD) : structure + placeholders marqués `[À VALIDER]`.

Commence par le Lot 1.