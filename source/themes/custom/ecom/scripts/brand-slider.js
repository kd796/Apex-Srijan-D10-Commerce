(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.brandSlider = {
    attach: function (context, settings) {
      // slider.
      $(function () {
        // eslint-disable-next-line no-unused-vars, no-undef
        var brandSlider = $('.owl-carousel');
        $(once('brandSlider', brandSlider, context)).owlCarousel({
          items: 2,
          loop: true,
          rewind: true,
          autoplay: false,
          nav: true,
          dots: false,
          responsive: {
            766.9: {
              items: 5
            },
            959: {
              items: 7
            }
          }
        });
      });

      /* end */
    }
  };
})(jQuery, Drupal, once);
