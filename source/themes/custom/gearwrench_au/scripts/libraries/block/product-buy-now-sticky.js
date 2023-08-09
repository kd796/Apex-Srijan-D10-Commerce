(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.productBuyNowSticky = {
    attach: function (context, settings) {
      var lastScrollTop = 0;
      var buyNowSticky = $('.block-product-buy-now-sticky__content');
      var topofDiv = $('.product-detail-content-container__utility-container').offset().top;

      // On scroll down, header and expanded navigation disappears, scroll up re-appears, logo area resizes.
      window.addEventListener('scroll', function () {
        var st = $(this).scrollTop();
        topofDiv = $('.product-detail-content-container__utility-container').offset().top;

        if (window.scrollY > topofDiv) {
          if (st > lastScrollTop) {
            buyNowSticky.removeClass('show--with-nav');
            if (!buyNowSticky.hasClass('show--no-nav')) {
              buyNowSticky.addClass('show--no-nav');
            }
          }
          else {
            buyNowSticky.removeClass('show--no-nav');
            if (!buyNowSticky.hasClass('show--with-nav')) {
              buyNowSticky.addClass('show--with-nav');
            }
          }
        }
        else {
          buyNowSticky.removeClass('show--no-nav');
          buyNowSticky.removeClass('show--with-nav');
        }
        lastScrollTop = st;
      });
    }
  };

})(jQuery, Drupal, once);
