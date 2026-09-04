# Guide contributeur — back-office

Guide d'utilisation du back-office Drupal pour les rédacteurs et éditeurs
du site de l'Ambassade. Se connecter sur `/user/login` avec le compte
fourni par le webmestre (l'auto-inscription est désactivée).

## Rôles

| Rôle | Peut faire |
|---|---|
| **Contributeur** | Créer et modifier son propre contenu, le soumettre en relecture |
| **Éditeur** | Tout ce que fait le contributeur, + publier, gérer les traductions et les alias d'URL, voir le contenu non publié des autres |
| **Webmestre** | Administration complète (utilisateurs, permissions, thème, modules, menus) |

## Workflow éditorial

Chaque contenu suit le cycle **Brouillon → Relecture → Publié** :

1. Un contributeur crée ou modifie un contenu : il est enregistré comme
   « Brouillon ».
2. Depuis le formulaire d'édition, changer l'état de modération en
   « Relecture » (bouton « Enregistrer et créer une nouvelle révision »)
   pour le soumettre à un éditeur.
3. Un éditeur relit puis passe l'état à « Publié » : le contenu devient
   visible sur le site public.

L'état de modération se change directement dans le formulaire d'édition
du contenu (bloc « État de publication »/« Enregistrer comme »).

## Créer une démarche consulaire

`Contenu > Ajouter du contenu > Démarche consulaire`. Champs :

- **Titre** : nom de la démarche.
- **Public concerné**, **Pièces à fournir** (un champ par pièce, bouton
  « Ajouter un élément »), **Délais**, **Tarifs**, **Modalités de dépôt
  et de retrait**.
- **Formulaires (PDF)** : joindre un ou plusieurs documents via la
  médiathèque (`Ajouter un média`).
- **Foire aux questions** : ajouter des paires question/réponse (bouton
  « Ajouter Question/réponse ») — affichées en accordéon sur la fiche.
- **Service de rattachement** : sélectionner un service existant, ou en
  créer un nouveau depuis `Structure > Taxonomie > Services` si
  nécessaire.

## Créer une actualité

`Contenu > Ajouter du contenu > Actualité / Communiqué`. Renseigner le
**chapô** (résumé court affiché dans les listes), le **corps** (texte
complet), une **image** (via la médiathèque), le **type** (communiqué,
événement ou presse) et la **date de publication**. Les actualités
publiées apparaissent automatiquement sur `/actualites` et dans le flux
RSS (`/actualites/rss.xml`).

## Créer une page éditoriale

`Contenu > Ajouter du contenu > Page éditoriale`. Le **corps** sert pour
un texte simple ; pour une mise en page plus riche, utiliser le champ
**Composants** et ajouter un ou plusieurs blocs :

- **Texte** — paragraphe de texte riche.
- **Image** — visuel avec légende optionnelle.
- **Accordéon** — ajouter des « Éléments d'accordéon » (titre + contenu).
- **Mise en avant** — bandeau image + texte + bouton d'appel à l'action.
- **Grille de cartes** — ajouter des « Cartes » (titre, texte, image,
  lien) affichées en grille responsive.
- **Formulaire** — intègre un formulaire (ex. le formulaire de contact)
  directement dans la page.

Les composants peuvent être réordonnés par glisser-déposer et supprimés
individuellement.

## Publier une alerte consulaire

`Contenu > Ajouter du contenu > Alerte consulaire`. Une alerte publiée
avec des dates de début/fin encadrant l'instant présent s'affiche
**automatiquement** en bandeau en haut de toutes les pages du site — pas
besoin de la placer manuellement. Le **niveau** (Information / Important
/ Urgent) détermine la couleur du bandeau. Penser à renseigner une **date
de fin** pour que l'alerte disparaisse automatiquement.

## Traduire un contenu

Sur la page d'un contenu publié, onglet **Traduire**, puis « Ajouter une
traduction » pour la langue souhaitée (arabe). Le contenu traduit est
automatiquement accessible sous le préfixe `/ar`.

## Modifier le contenu de la page d'accueil

Le hero (3 actions), les accès rapides et le bloc contact de la page
d'accueil sont des **blocs personnalisés**, pas des nœuds :
`Contenu > Blocs personnalisés` (réservé au webmestre). Modifier
directement le bloc concerné (« Hero accueil », « Accès rapides accueil »,
« Contact accueil ») ; les changements sont visibles immédiatement.

## Médiathèque

`Contenu > Médias` (ou directement depuis les champs média des
formulaires) pour gérer les images et documents PDF réutilisables entre
plusieurs contenus.

## Bon à savoir

- Les URLs (alias) sont générées automatiquement à partir du titre
  (Pathauto). Un éditeur peut la modifier manuellement dans l'onglet
  « Alias d'URL » du contenu.
- Toute modification de **configuration** (types de contenu, champs,
  vues, blocs, thème) doit être exportée et versionnée par un
  développeur (`drush config:export`) — voir le README du dépôt.
