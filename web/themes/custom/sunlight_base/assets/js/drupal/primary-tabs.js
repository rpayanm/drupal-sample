(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's primary tabs.
   */
  Drupal.behaviors.bootstrap_primary_tabs = {
    attach: function (context, settings) {
      const tabs = 'nav.tabs ul.primary';
      $(tabs).addClass('nav nav-tabs mb-2');
      $(tabs + ' li').addClass('nav-item');
      $(tabs + ' li a').addClass('nav-link');
      $(tabs + ' li.is-active a').addClass('active');
    }
  };

}(jQuery, Drupal, once, drupalSettings));