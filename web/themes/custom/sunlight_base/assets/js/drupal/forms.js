// CSS
import '../../scss/drupal/forms.scss';

// JS
(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for bootstrap's form.
   */
  Drupal.behaviors.bootstrap_forms = {
    attach: function (context, settings) {
      $('form .container-inline').addClass('row');
      $('form .container-inline .form-item').addClass('col');
    }
  };

}(jQuery, Drupal, once, drupalSettings));