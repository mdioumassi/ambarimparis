# 002 — Négociation linguistique par préfixe d'URL

## Contexte

Le site doit être bilingue FR (défaut) / AR (RTL), avec un hreflang correct
pour le SEO et une expérience utilisateur prévisible.

## Décision

Négociation de langue (interface **et** contenu) basée sur le préfixe d'URL
(`language-url`), avec repli sur `language-url-fallback` :

- `fr` → préfixe vide (`/actualites`)
- `ar` → préfixe `ar` (`/ar/actualites`)

## Justification

Le préfixe d'URL est la méthode recommandée par Drupal pour le SEO
multilingue (URLs indexables séparément par langue, hreflang généré
automatiquement par le cœur lorsque des traductions existent). L'alternative
(domaines séparés) est écartée : un seul nom de domaine est prévu pour ce
projet.

## Conséquences

- Un bloc « Sélecteur de langue » (`language_block:language_interface`) est
  placé en région `header` du thème actif, en attendant son intégration
  définitive dans `ambassade_theme` (Lot 3).
- Le contenu (nœuds, taxonomies) doit être traduit explicitement pour
  apparaître sous le préfixe `/ar` ; `content_translation` est activé en
  conséquence.
