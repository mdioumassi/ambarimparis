# 008 — Thème `ambassade_theme` basé sur le starterkit natif

## Contexte

Le Lot 3 impose un thème sur mesure, sans page builder ni Bootstrap complet,
avec CSS custom et design tokens. Le cahier des charges laisse le choix
entre Radix (base theme contrib) et le starterkit natif de Drupal 11.

## Décision

`ambassade_theme` est scaffoldé à partir de **`core/themes/starterkit_theme`**
(copié dans `web/themes/custom/ambassade_theme` puis renommé), plutôt que
Radix.

## Justification

- Le générateur officiel (`core/scripts/drupal generate-theme`) ne fonctionne
  pas tel quel avec un projet Composer à docroot relocalisé
  (`drupal/recommended-project`, cœur dans `web/core`) : son calcul de
  chemin relatif vers `vendor/autoload.php` suppose `core/` à la racine du
  dépôt. Le starterkit a donc été copié et renommé manuellement
  (`starterkit_theme` → `ambassade_theme` dans les noms de fichiers,
  namespaces PHP et références de bibliothèques).
- Le starterkit fournit déjà des overrides Twig de qualité pour la quasi
  totalité des éléments de formulaire, tableaux, pager, breadcrumb, etc.,
  accessibles par défaut — un gain de temps et de fiabilité important par
  rapport à Radix, qui ajoute une couche Bootstrap-like supplémentaire à
  maintenir alors que le cahier des charges l'exclut explicitement
  (« pas de Bootstrap complet »).
- Zéro dépendance contrib supplémentaire pour le thème lui-même.

## Conséquences

- `ambassade_theme` n'est pas un thème de base (`base theme: false`) :
  aucun sous-thème n'est prévu.
- Les fichiers `css/components/{button,breadcrumb,pager,form}.css` déjà
  fournis par le starterkit ont été **réécrits en place** (mêmes chemins,
  déjà référencés dans `ambassade_theme.libraries.yml`) plutôt que dupliqués,
  pour rester alignés avec les gabarits Twig hérités.
