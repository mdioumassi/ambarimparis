# 010 — Page d'accueil pilotée par des blocs personnalisés

## Contexte

Le gabarit d'accueil (hero 3 actions, bandeau d'alerte, bloc actualités,
accès rapides, bloc contact) mélange des éléments globaux (menu, alerte)
et des éléments propres à la page d'accueil, sans qu'aucun page builder ne
soit autorisé.

## Décision

- Trois nouveaux types de blocs personnalisés (`block_content`) :
  `hero_accueil`, `acces_rapides`, `contact_accueil` — champs réutilisant
  au maximum le modèle de paragraphes du Lot 2 (le paragraphe `carte` sert
  à la fois aux 3 actions du hero — via un mode d'affichage dédié
  `hero_action` — et aux cartes de la grille d'accès rapides).
- Ces blocs sont placés dans les régions `hero`, `quick_access` et
  `contact_block` d'`ambassade_theme`, avec une visibilité restreinte à
  `<front>` : les régions existent globalement dans `page.html.twig` mais
  ne s'affichent (via `{% if page.hero %}`) que sur les pages où un bloc y
  est effectivement placé.
- Un nœud `page` intitulé « Accueil », au corps vide, est créé et défini
  comme page de front (`system.site:page.front`). Il ne porte aucun
  contenu métier : c'est une ancre technique pour la route `<front>`,
  distincte du contenu de démonstration du Lot 6.
- Bloc contact : réutilise le champ de type `webform` (module `webform`)
  pour intégrer le formulaire directement dans le bloc plutôt que de créer
  une page dédiée séparée.
- La carte (Google Maps ou équivalent) n'est **pas intégrée** : un texte de
  repli `[À VALIDER]` est affiché à la place. Un embed de carte tierce
  charge généralement des cookies/traceurs non essentiels, ce qui doit être
  arbitré avec le bandeau de consentement cookies (Lot 5) avant intégration.

## Conséquences

- Le contenu du hero, des accès rapides et du bloc contact est modifiable
  par un webmestre via l'UI standard des blocs personnalisés
  (`/admin/content/block`), sans toucher au code.
- `[À VALIDER]` : adresse postale, horaires, téléphone (bloc contact et
  pied de page) — placeholders explicites en attendant les informations
  officielles.
