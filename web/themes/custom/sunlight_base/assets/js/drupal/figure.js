(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's figure.
   */
  Drupal.behaviors.bootstrap_figure = {
    attach: function (context, settings) {
      const figure = 'figure';
      $(figure).addClass('figure');
      $(figure + ' img').addClass('figure-img img-fluid rounded');
      $(figure + ' figcaption').addClass('figure-caption');
    }
  };

}(jQuery, Drupal, once, drupalSettings));