(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentMediaPages = {
    attach: function (context, settings) {
      $('.view-media-pages').once().each(function () {
        // Scroll down to the products when loading the category page.
        $('.view-media-pages')[0].scrollIntoView();
      });
    }
  };

})(jQuery, Drupal);
