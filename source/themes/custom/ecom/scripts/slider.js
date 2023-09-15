(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.slider = {
    attach: function (context, settings) {
      // slider.
      $(function () {
        // eslint-disable-next-line no-unused-vars, no-undef
        const featuredProducts = new Swiper('.featured-products', {
          slidesPerView: 2,
          navigation: {
            nextEl: '.feature-products-next',
            prevEl: '.feature-products-prev'
          },
          watchSlidesVisibility: true,
          breakpoints: {
            766.9: {
              slidesPerView: 3
            },
            959: {
              slidesPerView: 5
            }
          }
        });
      });

      /* end */
    }
  };
})(jQuery, Drupal, once);
