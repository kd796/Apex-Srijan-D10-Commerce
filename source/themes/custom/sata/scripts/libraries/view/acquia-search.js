(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentAcquiaSearch = {
    attach: function (context, settings) {
      $('.view-acquia-search:not(.view-new-products--js-initialized)').once('new-product-filter').each(function (index) {
        // Add related products nav blade with image inside after nav content in nav wrapper
        var $component = $(this);
        var $relatedProductsNavWrapper = $component.find('.node--type-product-related-products__nav-wrapper');

        $relatedProductsNavWrapper.append('<div class="node--type-product-related-products__nav-blade"><img src="/themes/custom/sata/images/pdp-related-products-blade.png"></div>');
      });
    }
  };
})(jQuery, Drupal);
