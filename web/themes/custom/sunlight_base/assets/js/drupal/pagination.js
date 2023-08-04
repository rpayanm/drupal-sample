(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's pagination.
   */
  Drupal.behaviors.bootstrap_pagination = {
    attach: function (context, settings) {
      const pagination = 'nav.pager';
      $(pagination + ' ul').addClass('pagination tw-justify-center');
      $(pagination + ' ul li').addClass('page-item');
      $(pagination + ' ul li a').addClass('page-link');
      $(pagination + ' ul li.is-active').addClass('active');
    }
  };

}(jQuery, Drupal, once, drupalSettings));