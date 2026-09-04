# 006 — Modèle de contenus (Lot 2)

## Contexte

Le Lot 2 demande quatre types de contenu (`demarche`, `actualite`, `page`,
`alerte`), deux taxonomies, et trois vues.

## Décisions

### Paragraphes plutôt que champs répétés

Les composants de la page éditoriale (texte, image, accordéon, mise en
avant, grille de cartes) et la FAQ des démarches utilisent le module
`paragraphs` (`entity_reference_revisions`) plutôt que des champs multiples
sur le nœud. Justification : permet l'ajout/réordonnancement libre de blocs
hétérogènes, standard de facto pour ce type de besoin dans l'écosystème
Drupal, et prépare le rendu Twig par composant (Lot 3).

Sous-paragraphes créés en complément du cahier des charges pour rester
cohérent :
- `accordeon_item` (titre + contenu), rattaché à `accordeon` via
  `field_elements`.
- `carte` (titre, texte, image, lien), rattaché à `grille_de_cartes` via
  `field_cartes`.

### Médias

Les bundles `image` et `document` du module `media` (config standard
core, importée depuis `core/profiles/standard/config/optional/`) sont
utilisés pour les visuels et les formulaires PDF joints aux démarches,
plutôt que des champs `file`/`image` bruts : cohérent avec la médiathèque
(Media Library) et réutilisable entre types de contenu.

### Recherche (Search API)

- Serveur `database_server` (backend `search_api_db`).
- Index `contenu_site` couvrant `demarche`, `actualite`, `page` (titre,
  corps, chapô, public concerné, statut, date, type, langue).
- Vue `recherche` (`/recherche`) basée sur l'index, filtre plein texte
  exposé (identifiant `recherche`), résultats en mode d'affichage
  `teaser`.
- Autocomplétion (`search_api_autocomplete`) activée sur cette vue
  (`views_recherche`), suggéreur `server`, seuil 3 caractères. Permission
  `use search_api_autocomplete for views_recherche` accordée à
  `anonymous`/`authenticated` (recherche publique).

`[À VALIDER]` : l'indexation ne couvre pas encore le contenu traduit en
arabe de façon distincte par langue au niveau du classement de pertinence
(configuration par défaut du backend DB) — à revoir si le volume de
contenu AR le justifie.

### Vues

- `actualites` (`/actualites`) : page paginée (10/page) triée par
  `field_date_publication` décroissant + affichage `feed_1`
  (`/actualites/rss.xml`).
- `demarches` (`/demarches`) : page paginée (20/page) avec filtre exposé
  sur `field_service` (taxonomie `services`).
- `recherche` (`/recherche`) : voir ci-dessus.

### Traduction de contenu

`content_translation` est activé sur les 4 types de contenu et les 2
vocabulaires, langue source `fr`. Les champs sont marqués `translatable:
true` par défaut (y compris les références media/taxonomie/paragraphes,
pour permettre par exemple une FAQ ou des visuels distincts par langue si
besoin — un contributeur peut choisir de synchroniser ces champs plus tard
via les réglages de traduction si l'on préfère les garder identiques
FR/AR).

### Workflow éditorial

Le workflow `editorial` (créé au Lot 1) est rattaché aux 4 types de
contenu : toute création de nœud passe par le cycle
Brouillon → Relecture → Publié.

## Conséquences

- Les displays de formulaire/affichage par défaut (`default` et `teaser`)
  sont explicitement enregistrés en configuration (module `field_ui`
  activé) pour que le Lot 3 dispose de gabarits Twig stables
  (`node--demarche--teaser.html.twig`, etc.).
- Aucune donnée de démonstration n'est incluse dans ce lot : elle sera
  livrée au Lot 6 (`default_content` ou recipe).
