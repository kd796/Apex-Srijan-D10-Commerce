(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.homeslider = {
    attach: function (context, settings) {
      // Homepage Slider.
      var homeSlider = $('.homepage-slider');

      // Initialize the homepage slider based.
      homeSlider.not('.slick-initialized').slick({
        infinite: true,
        centerMode: false,
        speed: 400,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: true,
        autoplay: true,
        fade: true,
        cssEase: 'linear',
        arrows: false
      });

      /* end */
    }
  };
})(jQuery, Drupal, once);
