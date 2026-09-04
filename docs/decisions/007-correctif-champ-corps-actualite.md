# 007 — Correctif : champ « corps » manquant sur `actualite`

## Contexte

Le cahier des charges du Lot 2 spécifie pour `actualite` : « chapo, corps,
image, type, date de publication, flux RSS ». Lors de l'implémentation du
Lot 2, le champ **corps** (`body`, `text_with_summary`) avait été omis —
seuls `field_chapo`, `field_image`, `field_type_actualite` et
`field_date_publication` avaient été créés.

## Correctif (appliqué pendant le Lot 3)

Ajout du champ standard `body` (traduisible, requis) au type de contenu
`actualite`, avec ses displays de formulaire (`text_textarea_with_summary`)
et d'affichage (`text_default`).

## Conséquences

- Aucune donnée existante affectée (aucun contenu `actualite` n'avait
  encore été créé).
- Le gabarit `node--actualite.html.twig` (Lot 3) affiche désormais le
  corps de l'actualité après le chapô.
