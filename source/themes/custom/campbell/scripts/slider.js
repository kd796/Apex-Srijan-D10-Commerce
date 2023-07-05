(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.slider = {
    attach: function (context, settings) {
      // slider.
      $(function () {
        var homeSlider = $('.slider');
        var activeLink = $('.home-text');
        homeSlider.once('homeSlider').owlCarousel({
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
        activeLink.once('activeLink').owlCarousel({
          items: 1,
          loop: false,
          rewind: true,
          autoplay: false,
          nav: false,
          dots: false,
          animateOut: 'fadeOut',
          animateIn: 'fadeIn'
        });
        homeSlider.once('homeSliderChange').on('changed.owl.carousel', function (e) {
          activeLink.trigger('next.owl.carousel');
        });
      });

      /* end */
    }
  };
})(jQuery, Drupal, 'once');
