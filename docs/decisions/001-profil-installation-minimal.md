# 001 — Profil d'installation `minimal`

## Contexte

Drupal propose plusieurs profils d'installation : `standard` (avec contenus de
démonstration, types `article`/`page`, vues préconfigurées) et `minimal` (socle
nu, sans contenu ni configuration éditoriale préétablie).

## Décision

Installation avec le profil **`minimal`**.

## Justification

Le Lot 2 définit un modèle de contenu entièrement sur mesure (`demarche`,
`actualite`, `page`, `alerte`) qui ne correspond pas aux types fournis par
`standard`. Partir de `minimal` évite d'avoir à supprimer des types de contenu,
vues et blocs par défaut non utilisés, et garde la configuration exportée
(`config/sync`) propre dès le départ.

Les formats de texte `basic_html` et `full_html` ainsi que leurs éditeurs
CKEditor 5 associés (habituellement fournis par `standard`) ont été importés
manuellement depuis `core/profiles/standard/config/install/` pour disposer
d'un éditeur riche pour les contributeurs.

## Conséquences

- Le thème par défaut est `stark` (minimal), remplacé par `ambassade_theme`
  au Lot 3.
- Chaque module contrib nécessaire est activé explicitement (voir Lot 1).
