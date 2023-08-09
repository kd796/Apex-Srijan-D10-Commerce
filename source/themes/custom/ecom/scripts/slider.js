(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.slider = {
    attach: function (context, settings) {
      // slider.
      $(function () {
        var homeSlider = $('.slider');
        var activeLink = $('.home-text');
        $(once('homeSlider', homeSlider, context)).owlCarousel({
          items: 1,
          loop: false,
          rewind: true,
          margin: 10,
          autoplay: 5000,
          nav: false,
          dots: false,
          animateOut: 'fadeOut',
          animateIn: 'fadeIn'
        });
        $(once('activeLink', activeLink, context)).owlCarousel({
          items: 1,
          loop: false,
          rewind: true,
          autoplay: false,
          nav: false,
          dots: false,
          animateOut: 'fadeOut',
          animateIn: 'fadeIn'
        });
        $(once('homeSliderChange', homeSlider, context)).on('changed.owl.carousel', function (e) {
          activeLink.trigger('next.owl.carousel');
        });
      });

      /* end */
    }
  };
})(jQuery, Drupal, once);
