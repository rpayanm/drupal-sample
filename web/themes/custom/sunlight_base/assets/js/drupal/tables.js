(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's tables.
   */
  Drupal.behaviors.bootstrap_tables = {
    attach: function (context, settings) {
      const table = 'table';
      $(table).each(function () {
        if (!$(this).hasClass('table')) {
          $(this).addClass('table table-hover table-bordered');
          $(this).find('thead').addClass('table-light');
          $(this).find('tbody').addClass('table-group-divider');
          $(this).wrap("<div class='table-responsive'></div>");
        }
      });
    }
  };

}(jQuery, Drupal, once, drupalSettings));