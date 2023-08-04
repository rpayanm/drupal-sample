(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's alerts.
   */
  Drupal.behaviors.bootstrap_alerts = {
    attach: function (context, settings) {
      // Drupal Messages
      const message = '.messages';
      $(message + '.messages--warning').addClass('alert alert-warning');
      $(message + '.messages--error').addClass('alert alert-danger');
      $(message + '.messages--status').addClass('alert alert-success');

      const alert = '.alert';
      $(alert + ' a').addClass('alert-link');

      // Dismissible
      $(message).addClass('alert-dismissible fade show');
      $(message).append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
    }
  };

}(jQuery, Drupal, once, drupalSettings));