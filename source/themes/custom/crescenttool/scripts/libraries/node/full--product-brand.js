(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentProductBrand = {
    attach: function (context, settings) {
      $('.node--type-product-brand').once().each(function (index) {
        // Scroll down to the products when loading the category page.
        $('.view-product-category')[0].scrollIntoView();
      });
    }
  };

})(jQuery, Drupal);
