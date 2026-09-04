# 015 — Page /contact dédiée + paragraphe « formulaire »

## Contexte

Le Lot 3 demande un gabarit « contact (webform + carte + horaires) ».
Au Lot 3, seul un **bloc** de la page d'accueil (`contact_accueil`)
avait été implémenté pour cet usage — aucune page `/contact` autonome
n'existait, alors que la grille d'accès rapides et plusieurs contenus y
font référence. Vérification en fin de Lot 6 : `/contact` retournait 404.

## Correctif

- Nouveau paragraphe **`formulaire`** (`field_webform`, type de champ
  `webform`) réutilisable dans `field_composants` (types `page`).
- Page `/contact` (type `page`) composée de trois paragraphes : `texte`
  (horaires/adresse, placeholder `[À VALIDER]`), `texte` (accès/carte,
  placeholder — même réserve RGPD que le bloc d'accueil : intégration
  d'une carte tierce à valider avec le service concerné avant
  production), `formulaire` (référence au webform `contact`).

## Conséquences

Le bloc `contact_accueil` de la page d'accueil (Lot 3) et la page
`/contact` sont deux implémentations distinctes du même besoin
(l'un en bloc de région pour l'accueil, l'autre en page autonome
linkable) ; elles partagent le même webform `contact`. Pas de
duplication de logique de rendu au-delà de ce qui est nécessaire.
