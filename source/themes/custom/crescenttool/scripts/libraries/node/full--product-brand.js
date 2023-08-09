(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentProductBrand = {
    attach: function (context, settings) {
      $(once('node--type-product-brand', '.node--type-product-brand', context)).each(function (index) {
        // Scroll down to the products when loading the category page.
        $('.view-product-category')[0].scrollIntoView();
      });
    }
  };

})(jQuery, Drupal, once);
