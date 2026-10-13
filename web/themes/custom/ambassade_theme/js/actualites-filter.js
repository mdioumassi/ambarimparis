/**
 * @file
 * Applique le filtre des actualités et des démarches dès que la sélection change.
 */
((Drupal, once) => {
  Drupal.behaviors.ambassadeActualitesFilter = {
    attach(context) {
      once('actualites-filter', '.view-id-actualites .views-exposed-form select, .view-id-demarches .views-exposed-form select', context).forEach((select) => {
        select.addEventListener('change', () => select.form.submit());
      });
    },
  };
})(Drupal, once);
