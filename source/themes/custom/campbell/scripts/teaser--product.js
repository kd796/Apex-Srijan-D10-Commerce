(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productTeaser = {
    attach: function (context, settings) {
      $('.node--type-product.node--view-mode-teaser').each(function (index) {
        // Product Teaser
        var $component = $(this);
        var $priceSpiderBtn = $component.find('.btn.ps-widget');

        $priceSpiderBtn.click(function (e) {
          e.preventDefault();
        });
      });
    }
  };
})(jQuery, Drupal);

//# sourceMappingURL=../../maps/libraries/node/teaser--product.js.map
