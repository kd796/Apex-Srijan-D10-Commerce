(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentMediaPages = {
    attach: function (context, settings) {
      $('.view-product-category').once().each(function () {
        // Scroll down to the products when loading the category page.
        $('.view-product-category').attr('style', 'scroll-margin: 50px !important;');
        $('.view-product-category')[0].scrollIntoView();
      });
    }
  };

})(jQuery, Drupal);
