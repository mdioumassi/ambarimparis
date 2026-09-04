# 003 — Rôles éditoriaux et workflow de modération

## Contexte

Le cahier des charges demande trois rôles (Contributeur / Éditeur /
Webmestre) avec permissions minimales, et un workflow éditorial
brouillon → relecture → publié.

## Décision

- Workflow `content_moderation` nommé **`editorial`**, états `draft`
  (Brouillon), `review` (Relecture), `published` (Publié, révision par
  défaut).
- Rôles créés : `contributeur`, `editeur`, `webmestre`, avec permissions
  additives (aucun rôle n'hérite des permissions d'un autre — chaque
  permission est accordée explicitement).

| Rôle | Peut | Ne peut pas |
|---|---|---|
| `contributeur` | Créer/modifier son contenu, le soumettre en relecture | Publier, voir le contenu des autres non publié |
| `editeur` | Tout ce que fait le contributeur + publier, voir tout contenu non publié, gérer les traductions et alias d'URL | Administrer les rôles, les types de contenu, le thème |
| `webmestre` | Administration complète (configuration, utilisateurs, permissions, modules) | — |

## Justification

Séparation des responsabilités conforme au principe du moindre privilège :
un contributeur ne doit pas pouvoir publier directement (contrôle éditorial),
un éditeur ne doit pas pouvoir modifier la structure du site.

## Conséquences

Le workflow `editorial` doit être explicitement rattaché aux types de contenu
`demarche`, `actualite`, `page` et `alerte` lors de leur création (Lot 2) via
`workflows.workflow.editorial` (`bundles` et `entity_types`).
