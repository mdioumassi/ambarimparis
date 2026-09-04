# 012 — Correctif : alias cassé pour la déclaration d'accessibilité

## Contexte

En vérifiant les routes du Lot 5, `/declaration-accessibilite` retournait
une 404. La page avait été créée au Lot 3 avec un alias manuel
(`path.alias`), mais sans désactiver `pathauto` — Pathauto a donc régénéré
l'alias automatiquement à partir du titre (« Déclaration d'accessibilité »)
en `/declaration-daccessibilite` (l'apostrophe de « d'accessibilité »
devient « da » après translittération), écrasant l'alias voulu. Le lien de
menu du pied de page, la page « Plan du site » et ce document pointaient
tous vers `/declaration-accessibilite`, d'où la 404.

## Correctif

- Alias corrigé sur le nœud, avec `pathauto` explicitement désactivé
  (`'path' => ['alias' => '/declaration-accessibilite', 'pathauto' => FALSE]`)
  pour qu'il ne soit plus régénéré automatiquement.
- Ancien alias orphelin (`/declaration-daccessibilite`) supprimé.

## Point de vigilance pour la suite

Toute création programmatique de nœud avec un alias voulu explicite
**doit** systématiquement inclure `'pathauto' => FALSE` dans la valeur du
champ `path`, sous peine de voir Pathauto régénérer un alias différent au
premier `save()`. `scripts/ci-demo-content.php` applique déjà cette règle.
