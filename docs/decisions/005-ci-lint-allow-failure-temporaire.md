# 005 — `allow_failure` temporaire sur les jobs de lint PHP

> **Résolu au Lot 3** : `web/themes/custom/ambassade_theme` contient désormais
> du code PHP (`src/Hook/AmbassadeThemeHooks.php`). `lint:phpcs` et
> `lint:phpstan` sont repassés bloquants (`allow_failure` retiré) dans
> `.gitlab-ci.yml`. Le contenu ci-dessous est conservé à titre historique.

## Contexte

`web/modules/custom` et `web/themes/custom` sont vides à l'issue du Lot 1
(aucun module ni thème métier n'existe encore). Or `phpcs` et `phpstan`
retournent une erreur (« No files were checked » / « No files found to
analyse ») lorsqu'aucun fichier PHP n'est trouvé dans les chemins scannés,
ce qui ferait échouer la CI sans raison liée à la qualité du code.

## Décision

Les jobs `lint:phpcs` et `lint:phpstan` du pipeline GitLab CI sont marqués
`allow_failure: true` jusqu'à l'arrivée du premier code custom (Lot 2 pour
les modules, Lot 3 pour le thème).

## Conséquences

**Action de suivi obligatoire** : retirer `allow_failure: true` de ces deux
jobs dans `.gitlab-ci.yml` dès que `web/modules/custom` ou
`web/themes/custom` contiennent du code PHP/Twig, afin que le lint redevienne
bloquant.
