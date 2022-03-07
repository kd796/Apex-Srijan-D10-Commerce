(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentProductBrand = {
    attach: function (context, settings) {
      $('.view-product-category:not(.view-product-category--js-initialized)').once('product-brand-filter').each(function (index) {
        // Scroll down to the products when loading the category page.
        $('.view-product-category')[0].scrollIntoView();
      });
    }
  };

})(jQuery, Drupal);
