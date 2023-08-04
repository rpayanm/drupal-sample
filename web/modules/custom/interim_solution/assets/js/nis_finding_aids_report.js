(function ($, Drupal, once, drupalSettings) {
  'use strict';

  Drupal.behaviors.nis_finding_aids_report = {
    attach: function (context, settings) {
      once('nis_finding_aids_report', 'body').forEach(function (element) {
        // Adds 'row-class' to the row whether the content is global or local
        // and the content has media assets.
        $('#views-form-finding-aids-report-page-1 table.vbo-table tbody tr').each(function () {
          let global_flag = $(this).find('td.views-field-global-flag-interim-solution').text();
          let local_flag = $(this).find('td.views-field-field-show-catalog-content').text();
          let available_content = $(this).find('td.views-field-node-available-content-field').text();
          let mids = $(this).find('td.views-field-mid').text();
          global_flag = global_flag.replace(/\s/g, '');
          local_flag = local_flag.replace(/\s/g, '');
          available_content = available_content.replace(/\s/g, '');
          mids = mids.replace(/\s/g, '');

          let can_delete_row = (global_flag === 'Yes' || local_flag === 'Yes') && available_content === 'Yes' && mids > 0;

          if (!can_delete_row) {
            $(this).addClass('row-red');
          }
        });
      });
    },
  };
})(jQuery, Drupal, once, drupalSettings);
