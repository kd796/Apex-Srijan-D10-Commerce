(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentMediaPages = {
    attach: function (context, settings) {
      $(once('view-product-category', '.view-product-category', context)).each(function () {
        // Scroll down to the products when loading the category page.
        $('.view-product-category').attr('style', 'scroll-margin: 50px !important;');
        $('.view-product-category')[0].scrollIntoView();
      });
    }
  };

})(jQuery, Drupal, once);
