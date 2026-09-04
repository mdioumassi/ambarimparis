# 009 — Design tokens et polices auto-hébergées

## Décision

- Design tokens en custom properties CSS (`css/base/tokens.css`) :
  couleurs institutionnelles (vert `#006233`, or `#FFC400`, rouge `#CE1126`),
  neutres, rôles sémantiques (`--color-text`, `--color-primary`, etc.),
  échelle typographique fluide (`clamp()`), espacements, rayons, ombres,
  cible tactile minimale (`--tap-target-min: 44px`).
- Polices auto-hébergées (`css/base/fonts.css`, fichiers dans
  `fonts/`) : **Source Sans 3** (variable, sous-ensemble latin uniquement)
  pour le français, **Noto Naskh Arabic** (variable, sous-ensemble arabe
  uniquement) pour l'arabe. Les deux sont sous licence SIL Open Font
  License, compatible avec l'auto-hébergement.
- `html[lang="ar"]` bascule `--font-body` vers la police arabe ; le reste
  des caractères (ponctuation latine mélangée, etc.) retombe sur Source
  Sans 3 via la couverture `unicode-range` de chaque `@font-face`.

## Justification

Sous-ensembler les polices (un seul fichier variable par langue, limité
aux plages Unicode réellement utilisées) réduit fortement le poids
téléchargé par rapport aux fichiers complets multi-scripts fournis par
défaut par Google Fonts, tout en respectant l'exigence d'auto-hébergement
(RGPD : pas de requête vers fonts.googleapis.com).

## Conséquences

- Si du contenu cyrillique/grec/vietnamien devait un jour être nécessaire,
  il faudra retélécharger et ajouter les sous-ensembles correspondants.
- Les valeurs exactes des tokens de couleur devront être revalidées avec
  la charte graphique officielle de l'Ambassade `[À VALIDER]` — les teintes
  actuelles respectent les couleurs demandées dans le cahier des charges
  mais les nuances dérivées (hover, fonds clairs) sont des choix de
  développement.
