/**
 * @file
 * Menu mobile accessible : bouton burger (aria-expanded), piège de focus,
 * fermeture au clavier (Échap) et au clic extérieur.
 */

(function (Drupal) {
  'use strict';

  Drupal.behaviors.ambassadeThemeNav = {
    attach(context) {
      const headers = context.querySelectorAll
        ? context.querySelectorAll('[data-nav-header]')
        : [];

      headers.forEach((header) => {
        if (header.dataset.navInitialized) {
          return;
        }
        header.dataset.navInitialized = 'true';

        const toggle = header.querySelector('[data-nav-toggle]');
        const panel = header.querySelector('[data-nav-panel]');
        if (!toggle || !panel) {
          return;
        }

        const getFocusable = () =>
          Array.from(
            panel.querySelectorAll(
              'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])'
            )
          );

        const openMenu = () => {
          header.setAttribute('data-nav-open', '');
          toggle.setAttribute('aria-expanded', 'true');
          const focusable = getFocusable();
          if (focusable.length) {
            focusable[0].focus();
          }
          document.addEventListener('keydown', onKeydown);
          document.addEventListener('click', onClickOutside);
        };

        const closeMenu = ({ returnFocus = true } = {}) => {
          header.removeAttribute('data-nav-open');
          toggle.setAttribute('aria-expanded', 'false');
          document.removeEventListener('keydown', onKeydown);
          document.removeEventListener('click', onClickOutside);
          if (returnFocus) {
            toggle.focus();
          }
        };

        function onKeydown(event) {
          if (event.key === 'Escape') {
            closeMenu();
            return;
          }
          if (event.key !== 'Tab') {
            return;
          }
          const focusable = getFocusable();
          if (!focusable.length) {
            return;
          }
          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
          } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
          }
        }

        function onClickOutside(event) {
          if (!header.contains(event.target)) {
            closeMenu({ returnFocus: false });
          }
        }

        toggle.setAttribute('aria-expanded', 'false');
        toggle.addEventListener('click', () => {
          if (header.hasAttribute('data-nav-open')) {
            closeMenu();
          } else {
            openMenu();
          }
        });

        // Le panneau redevient statique en desktop : on ferme l'état mobile.
        const mediaQuery = window.matchMedia('(min-width: 1024px)');
        const handleBreakpointChange = (event) => {
          if (event.matches) {
            closeMenu({ returnFocus: false });
          }
        };
        if (mediaQuery.addEventListener) {
          mediaQuery.addEventListener('change', handleBreakpointChange);
        }
      });
    },
  };
})(Drupal);
