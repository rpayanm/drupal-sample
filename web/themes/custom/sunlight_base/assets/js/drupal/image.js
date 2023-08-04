(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's image.
   */
  Drupal.behaviors.bootstrap_image = {
    attach: function (context, settings) {
      const image = 'img';
      $(image).addClass('img-fluid');
    }
  };

}(jQuery, Drupal, once, drupalSettings));