# 016 — Intégration de la maquette Figma « Accueil — Desktop 1440 »

## Contexte

Un nouveau prompt (`docs/prompt-maquette.md`) fournit une spécification
pixel-précise de la page d'accueil, référencée à un fichier Figma
(`MhU8WPYnv8uQMxWXTYIeUp`, nœud `1:2`). Le contexte MCP Figma a été récupéré
via `get_design_context` pour confirmer les mesures : les valeurs obtenues
correspondent à celles du prompt (couleurs, tailles, espacements), qui fait
donc foi telle quelle.

Le thème `ambassade_theme` livre déjà une page d'accueil fonctionnelle et
testée (Lots 3 à 6 : tokens, blocs `hero_accueil`/`acces_rapides`/
`contact_accueil`, accessibilité RGAA, SEO/RGPD/perf, contenu de
démonstration). La maquette introduit cependant des éléments absents de cette
première itération : bandeau topbar, recherche dans le hero, grille de 6
démarches, section actualités sur l'accueil, bloc infos pratiques à 3
colonnes, pied de page à 4 colonnes.

## Décision

On **étend** l'architecture existante plutôt que de la remplacer :

- **Tokens** : les valeurs de la maquette correspondent déjà aux tokens
  sémantiques existants (`--color-primary` = `#006233`, `--color-accent` =
  `#ffc400`, etc., cf. `009-design-tokens-polices.md`). Seuls des tokens de
  rayon/ombre supplémentaires (`--radius-card`, `--shadow-card`,
  `--shadow-card-soft`) sont ajoutés en alias pour coller aux valeurs exactes
  de la maquette (10px / ombres spécifiques) sans renommer les tokens
  existants ni casser les composants déjà en place.
- **Régions** : ajout de `topbar` (urgences consulaires + langue) et `news`
  (actualités accueil) dans `ambassade_theme.info.yml`. Les régions
  `hero`, `quick_access`, `contact_block` existantes sont conservées et
  réutilisées.
- **Hero / grille de démarches** : on conserve le choix de la décision 010
  (blocs personnalisés + paragraphes `carte`/`grille_de_cartes`, éditables
  sans toucher au code) plutôt que de basculer sur une vue, pour rester
  cohérent avec l'architecture déjà validée. Deux champs sont ajoutés :
  `field_titre` sur le bundle `hero_accueil` (le H1 n'existait pas encore —
  seul le titre de nœud, masqué visuellement, en tenait lieu) et
  `field_soustitre` sur `acces_rapides` (sous-titre de la section « Toutes
  vos démarches »). Le contenu des 6 cartes est mis à jour pour correspondre
  aux 6 catégories de la maquette (Visas, Passeports & CNI, État civil,
  Procurations & légalisations, Laissez-passer, Espace étudiants).
- **Recherche** : la barre de recherche du hero réutilise la vue `recherche`
  (Search API + autocomplétion, déjà en place) via son formulaire exposé,
  plutôt qu'un nouveau widget.
- **Actualités sur l'accueil** : ajout d'un affichage `block_1` (3 dernières,
  mode teaser, sans pager) sur `views.view.actualites`, placé dans la
  nouvelle région `news`. C'est le seul endroit où une vue (plutôt qu'un bloc
  personnalisé) alimente une section d'accueil, car le contenu est par nature
  une liste chronologique de nœuds `actualite`.
- **Infos pratiques** : plutôt que d'ajouter trois nouveaux champs au bundle
  `contact_accueil` (Horaires/Adresse/Contact), le champ enrichi existant
  (`field_texte`) est restructuré en trois blocs `<div>` avec sous-titres —
  évite une migration de champ pour un gain éditorial marginal, au prix d'une
  structure HTML un peu plus rigide dans l'éditeur WYSIWYG.
- **Pied de page à 4 colonnes** : les deux blocs `basic` existants
  (`ambassade_footer_liens`, `ambassade_footer_horaires`) sont restructurés
  en **deux colonnes chacun** (balisage `<div class="footer-column">`, le
  bloc englobant passant en `display: contents` pour laisser chaque colonne
  s'aligner dans la grille CSS). Un troisième bloc `basic`
  (`ambassade_footer_mauritanie`) est créé pour la colonne « La Mauritanie ».
  Le menu `footer-legal` (auparavant affiché en ligne dans `footer_bottom`)
  devient la 4e colonne titrée « Informations légales » ; `footer_bottom` ne
  conserve que le copyright et un second sélecteur de langue, conformément à
  la maquette.
- **En-tête** : le bloc-marque combine le logo du thème (`logo.svg`,
  remplacé par un sceau rond provisoire vert/or — **[À VALIDER]** en attente
  du sceau officiel) avec le nom du site (deux lignes FR) et le slogan
  (ligne AR, `lang="ar" dir="rtl"`), tous deux pilotés par la configuration
  standard `system.site` plutôt que codés en dur. Un lien « Prendre RDV »
  est ajouté au menu principal via un second bloc de menu stylé en CTA.
- **Topbar** : nouveau bloc `basic` pour le numéro d'urgence
  (**[À VALIDER]** — numéro réel à confirmer), et déplacement du sélecteur de
  langue du header vers la topbar pour coller à la maquette.

## Conséquences

- Aucune régression sur les décisions 006/009/010/011/013/015 : les mêmes
  entités de contenu, vues et blocs sont réutilisés et complétés, pas
  remplacés.
- Deux nouveaux champs de contenu (`field_titre` sur `hero_accueil`,
  `field_soustitre` sur `acces_rapides`) à exporter en configuration.
- Contenu de démonstration mis à jour (`scripts/create-demo-content.php`
  reste inchangé pour les nœuds ; les blocs personnalisés et menus sont
  mis à jour directement en base puis exportés en configuration).
- **[À VALIDER]** : sceau officiel de l'ambassade (image), numéro
  d'urgence consulaire, libellés exacts des colonnes « L'Ambassade » /
  « La Mauritanie » du pied de page (contenu institutionnel non fourni).
