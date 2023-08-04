(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Drupal JS helper for Bootstrap's Accordion.
   */
  Drupal.behaviors.sl_bs_accordion = {
    attach: function (context, settings) {
      const $accordion_item_first_child = $('.accordion[data-open-first-item="1"]').find('.accordion-item:first-child');
      $accordion_item_first_child.find('.accordion-button').removeClass('collapsed');
      $accordion_item_first_child.find('.accordion-collapse').addClass('show');
    },
  };

}(jQuery, Drupal, once, drupalSettings));