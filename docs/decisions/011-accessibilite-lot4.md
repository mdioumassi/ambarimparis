# 011 — Accessibilité et responsive (Lot 4)

## Contexte

Le Lot 4 demande une vérification systématique de l'accessibilité et du
responsive, en plus d'un audit automatisé en CI.

## État à l'issue du Lot 4

La majorité des exigences avaient déjà été construites nativement dans le
design system du Lot 3 plutôt qu'ajoutées après coup :

- **Mobile-first**, points de rupture 360 / 768 / 1024 / 1440px (grilles de
  cartes, hero, en-tête). Le conteneur passe à `--content-max-width-wide`
  et les grilles de listes à 4 colonnes à partir de 1440px.
- **Zoom** : aucune balise `maximum-scale` ni `user-scalable=0` n'est
  émise (meta viewport par défaut de Drupal core, non modifiée). Mise en
  page en unités relatives (`rem`, `%`, `fr`, `clamp()`) sans largeur fixe
  en pixels, pour un zoom 200 % sans perte de contenu ni défilement
  horizontal.
- **Menu mobile** : bouton burger avec `aria-expanded`, panneau de
  navigation avec piège de focus et fermeture au clavier (Échap) ou au
  clic extérieur (`js/nav.js`, sans dépendance jQuery).
- **Navigation clavier / landmarks** : lien d'évitement vers `#main-content`,
  repères `header`/`nav`/`main`/`footer`, accordéons en `<details>/<summary>`
  natifs (aucun JS requis pour leur accessibilité clavier).
- **Cibles tactiles ≥ 44px** : token `--tap-target-min`, appliqué aux
  boutons, à la pagination, au bouton burger, aux liens du sélecteur de
  langue et aux champs de formulaire.
- **Contrastes AA** : les paires de couleurs texte/fond des tokens ont été
  calculées (ratio WCAG) ; toutes atteignent ≥ 4.5:1 pour le texte normal.
  Un correctif a été nécessaire : `.button--accent:hover` utilisait du
  texte blanc sur fond `--color-gold-600` (ratio 3.3:1, insuffisant) — le
  texte a été changé pour `--color-accent-contrast` (encre foncée,
  ratio 5.1:1).

## Audit automatisé (CI)

Job `test:a11y` (`.gitlab-ci.yml`) : installe un site Drupal jetable
(SQLite) à partir de la configuration versionnée (`config/sync`), le sert
en local (`php -S`), puis exécute **pa11y-ci** (runners `axe` + `htmlcs`,
niveau WCAG2AA) sur les gabarits clés (`.pa11yci.json` : accueil,
démarches, actualités, recherche, mentions légales, accueil en arabe).

`allow_failure: true` en l'état : l'exécution de Chromium headless dans
l'image `php:8.3-cli` (dépendances système ajoutées manuellement) n'a pas
pu être validée dans cet environnement de développement (absence de
runner GitLab CI disponible ici). **Action de suivi** : vérifier ce job au
premier pipeline réel et retirer `allow_failure` une fois confirmé stable ;
à défaut, basculer vers une image dédiée avec Chromium préinstallé
(ex. `markhobson/node-chrome`).

## Limites connues / à valider

- Aucun test n'a pu être exécuté dans un navigateur réel avec un lecteur
  d'écran (NVDA/VoiceOver) dans cet environnement — validation manuelle
  recommandée avant mise en production.
- Le contraste des tokens est vérifié mathématiquement (formule WCAG),
  pas par capture d'écran.
