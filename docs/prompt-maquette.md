# PROMPT — Intégration de la maquette « Accueil » dans le thème Drupal

---

## Contexte

Tu intègres la maquette validée de la page d'accueil dans le thème custom `ambassade_theme` du site de l'Ambassade de Mauritanie en France (Drupal 11, DDEV). Tu travailles en autonomie : décisions documentées dans `docs/decisions/`, question uniquement si bloquant.

**Maquette de référence :** https://www.figma.com/design/MhU8WPYnv8uQMxWXTYIeUp — frame « Accueil — Desktop 1440 » (node `1:2`).
Si tu as accès au MCP Figma, utilise `get_design_context` sur ce node pour extraire les mesures exactes. Sinon, applique la spécification ci-dessous, qui fait foi.

## Design tokens (CSS custom properties, fichier `css/tokens.css`)

```css
:root {
  /* Couleurs */
  --color-primary: #006233;        /* vert institutionnel */
  --color-primary-dark: #003B26;   /* hero gradient fin / footer #003A1F */
  --color-footer: #003B1F;
  --color-accent: #FFC400;         /* or — alertes, badges, CTA secondaire */
  --color-danger: #CE1126;         /* rouge — accents uniquement */
  --color-ink: #1A1F1C;            /* texte principal */
  --color-muted: #6B7370;          /* texte secondaire */
  --color-surface: #FFFFFF;
  --color-surface-alt: #F2F7F5;    /* fonds de sections et cartes démarches */
  --color-border: #D9E6DE;

  /* Typographie */
  --font-sans: "Source Sans 3", system-ui, sans-serif;
  --font-arabic: "Noto Naskh Arabic", serif;
  --text-hero: clamp(1.9rem, 4vw, 2.5rem);   /* 40px desktop, bold */
  --text-h2: clamp(1.5rem, 3vw, 1.875rem);   /* 30px */
  --text-h3: 1.0625rem;                       /* 17px cartes */
  --text-body: 0.9375rem;
  --text-small: 0.8125rem;

  /* Espacements & rayons */
  --space-section-y: 4rem;         /* 64px */
  --space-section-x: 5rem;         /* 80px desktop, 1rem mobile */
  --gap-cards: 1.5rem;             /* 24px */
  --radius-card: 10px;
  --radius-btn: 6px;
  --shadow-card: 0 4px 16px rgb(0 0 0 / 0.12);
  --shadow-card-soft: 0 2px 10px rgb(0 0 0 / 0.08);
}
```

Polices auto-hébergées (pas de Google Fonts CDN) : télécharge Source Sans 3 et Noto Naskh Arabic en woff2, `font-display: swap`.

## Structure de la page (de haut en bas)

Chaque section = un template Twig dans `templates/` + un fichier CSS BEM dans `css/components/`, agrégés par la library `ambassade_theme/global`.

1. **Topbar** (`region--topbar`) — fond `--color-ink`, texte blanc 12px. Gauche : « Urgences consulaires : +33 1 XX XX XX XX ». Droite : sélecteur de langue « FR | العربية » (bloc Drupal Language Switcher stylé, langue active en `--color-accent` gras).
2. **Header** (`region--header`) — fond blanc, padding 20px 80px. Gauche : bloc-marque = sceau rond 52px (vert, bordure or 3px, image du sceau en media à terme) + libellé sur 3 lignes : 2 lignes FR bold 15px vertes, 1 ligne arabe 12px `--color-muted` (`lang="ar" dir="rtl"`). Droite : menu principal 6 entrées (Démarches consulaires, Espace étudiants, L'Ambassade, La Mauritanie, Actualités, Contact) 14px semi-bold + bouton « Prendre RDV » (fond vert, texte blanc bold, radius 6px).
3. **Bandeau d'alerte** — fond `--color-accent`, texte `--color-ink` 13px semi-bold, icône info. Rendu par une vue sur le type `alerte` (uniquement si une alerte est active) ; `role="status"`.
4. **Hero** — dégradé vertical `#006233 → #004A26`, padding 64px, contenu centré. H1 blanc `--text-hero` : « Vos démarches consulaires, simplifiées ». Sous-titre 17px `#D9EBDE`. Barre de recherche : input blanc radius 8px (placeholder « Rechercher une démarche… ») + bouton « Rechercher » fond or (branchée sur Search API avec autocomplétion). Puis 3 cartes d'action blanches (310px, radius 10px, `--shadow-card`, gap 24px) : pastille verte 40px bordure or, titre 17px bold, description 13px muted, lien « Accéder → » vert semi-bold. Cartes : Prendre rendez-vous / Demander un visa / Passeport & CNI.
5. **Section Démarches** — fond blanc, centré. H2 « Toutes vos démarches » + sous-titre muted. Grille 3×2 (gap 24px) de cartes 400px : fond `--color-surface-alt`, bordure `--color-border` 1px, radius 10px, pastille verte 38px, titre 16px bold, description 13px, lien « Consulter la fiche → ». Contenu : vue sur le type `demarche` (6 éléments épinglés). Cartes : Visas / Passeports & CNI / État civil / Procurations & légalisations / Laissez-passer / Espace étudiants.
6. **Section Actualités** — fond `--color-surface-alt`, padding 56px 80px. En-tête : H2 « Actualités & communiqués » à gauche, lien « Toutes les actualités → » à droite. 3 cartes (vue sur `actualite`, 3 dernières) : image 16:9 (style d'image responsive, coins hauts arrondis), badge or uppercase 10px (terme de taxonomie), date 12px muted, titre 16px bold sur 2 lignes max (line-clamp).
7. **Bloc infos pratiques** — fond blanc, 3 colonnes (Horaires d'ouverture / Adresse / Contact), titres 17px bold verts, corps 14px muted interligne 1.6.
8. **Footer** — fond `--color-footer`, texte `#B8CCC0`. 4 colonnes de liens (titres or 15px bold) : Démarches, L'Ambassade, La Mauritanie, Informations légales (mentions légales, confidentialité, déclaration d'accessibilité, plan du site). Séparateur blanc 15 % d'opacité, puis ligne basse : copyright 12px à gauche, « FR | العربية » à droite.

## Exigences d'intégration

- **Twig/Drupal proprement** : régions déclarées dans `ambassade_theme.info.yml` (topbar, header, alerte, content, footer_1..4, footer_bottom) ; sections 4–6 alimentées par des vues et blocs, jamais de contenu en dur dans les templates (les textes ci-dessus servent au contenu de démonstration).
- **CSS** : BEM, propriétés logiques exclusivement (`margin-inline`, `padding-inline`, `inset-inline`) pour le support RTL ; zéro framework CSS ; un fichier par composant.
- **RTL** : la version arabe doit fonctionner sans CSS additionnel hormis `[dir="rtl"]` pour les rares cas non couverts par les propriétés logiques. Vérifie header, cartes et footer en AR.
- **Responsive** (mobile-first, breakpoints 360/768/1024/1440) : `--space-section-x: 1rem` sous 768px ; hero cards et grille démarches en 1 colonne mobile, 2 colonnes tablette ; actualités en scroll horizontal contrôlé ou empilement ; menu principal remplacé par burger accessible sous 1024px (`aria-expanded`, piège de focus, fermeture Échap) ; topbar réduite au sélecteur de langue sur mobile.
- **Accessibilité RGAA** : jamais de `maximum-scale`/`user-scalable=0` ; focus visible sur tous les interactifs (outline 2px `--color-accent` offset 2px) ; contrastes AA — sur fond or utiliser `--color-ink`, jamais de blanc ; H1 unique (hero) ; landmarks (`header`, `nav`, `main`, `footer`) ; lien d'évitement ; cibles tactiles ≥ 44px.
- **Icônes** : sprite SVG inline (`images/sprite.svg`), aucune police d'icônes. Les pastilles recevront des icônes SVG par démarche (calendrier, tampon visa, carte d'identité, etc.) — crée des placeholders SVG simples cohérents.
- **Performance** : CSS/JS agrégés, images lazy-load, styles d'images WebP.

## Livraison

1. Commence par `docs/decisions/NNN-integration-accueil.md` listant tes choix.
2. Ordre : tokens → composants (topbar, header, alerte, hero, carte-demarche, carte-actualite, infos, footer) → templates de page → vues/blocs → contenu de démo → responsive → passe RTL → passe accessibilité.
3. À la fin : capture Lighthouse mobile (cible ≥ 90 partout), vérification pa11y sur l'accueil, commit conventionnel par composant, et résumé de 5 lignes max.

Commence par les tokens.