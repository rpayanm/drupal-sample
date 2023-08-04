import './image';
import './tables';

(function ($, Drupal, once, drupalSettings) {
  'use strict';

  Drupal.behaviors.sunlight_gutenberg_edit = {
    attach: function (context, settings) {
      once('sunlight_gutenberg_edit', 'body', context).forEach(function (element) {
        let observer = new MutationObserver(mutations => {
          const gutenberg_container = '.edit-post-visual-editor .edit-post-visual-editor__content-area';
          $(gutenberg_container).addClass('bootstrap');
        });

        // observe everything except attributes
        observer.observe(document.body, {
          childList: true, // observe direct children
          subtree: true, // lower descendants too
        });
      });

    }
  };
})(jQuery, Drupal, once, drupalSettings);
