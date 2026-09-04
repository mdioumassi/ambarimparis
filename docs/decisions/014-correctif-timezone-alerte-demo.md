# 014 — Correctif : fuseau horaire des dates d'alerte dans le contenu de démonstration

## Contexte

L'alerte de démonstration n'apparaissait pas dans le bandeau, alors que
ses dates de début/fin encadraient bien la date du jour en apparence.

## Cause

Les champs `field_date_debut`/`field_date_fin` sont de type `datetime`
(module core `datetime`) : Drupal les stocke **toujours en UTC**,
indépendamment du fuseau horaire configuré pour le site (`Europe/Paris`).
Le script de contenu de démonstration utilisait `date('Y-m-d\TH:i:s')`
(heure locale du serveur PHP, donc déjà en `Europe/Paris`, soit UTC+1/UTC+2)
au lieu de `gmdate(...)` (UTC). La valeur stockée était donc décalée de
1 à 2h par rapport à l'UTC attendu. Le filtre de vue `alertes_actives`
(Lot 3) réapplique ensuite un décalage de fuseau horaire pour comparer au
« maintenant » local, ce qui amplifiait l'écart et faisait sortir l'alerte
de sa plage de dates.

## Correctif

`scripts/create-demo-content.php` utilise désormais `gmdate()` pour ces
deux champs, avec un commentaire explicite rappelant que les champs
`datetime` sont stockés en UTC.

## Point de vigilance pour la suite

Toute création programmatique de valeur pour un champ de type `datetime`
(mais pas `date`, qui est sans heure ni fuseau) doit utiliser `gmdate()`
ou une date explicitement convertie en UTC — jamais `date()` local.
