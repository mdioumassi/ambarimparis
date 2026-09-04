# 004 — En-têtes de sécurité via SecKit

## Contexte

Le cahier des charges impose CSP, X-Frame-Options et Referrer-Policy.

## Décision

Module `seckit` activé et configuré avec une base restrictive :

- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-Content-Type-Options: nosniff`
- `Content-Security-Policy` :
  `default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';
  img-src 'self' data:; font-src 'self' data:; object-src 'none';
  base-uri 'self'; form-action 'self'; frame-ancestors 'self'`

## Justification

Base restrictive par défaut (`'self'` partout), avec `'unsafe-inline'`
uniquement sur `style-src` pour compatibilité avec certains styles inline de
Drupal core/contrib (CKEditor, messages système). À resserrer en Lot 3/4 une
fois l'inventaire des dépendances JS/CSS du thème custom stabilisé (viser un
retrait de `'unsafe-inline'` si possible, via nonce ou hash).

HSTS (`seckit_ssl.hsts`) est désactivé en développement local (DDEV sert en
HTTPS auto-signé) : à activer explicitement en production via une
configuration d'environnement dédiée, pas dans `config/sync` partagé.

## Conséquences

- Toute nouvelle librairie JS/CSS tierce (Matomo, cookies, etc., Lot 5) devra
  être ajoutée explicitement aux directives CSP correspondantes.
- `[À VALIDER]` : domaines exacts pour Matomo si hébergé hors du domaine
  principal (à ajouter à `script-src`/`img-src`/`connect-src`).
