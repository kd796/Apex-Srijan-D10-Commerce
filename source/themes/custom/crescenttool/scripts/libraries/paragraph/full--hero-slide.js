(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentHeroSlide = {
    attach: function (context, settings) {
      $(window).resize(function () {
        $('.component-hero-slide .media--type-remote-video iframe').each(function (index) {
          var $iframe = $(this);
          var $container = $iframe.parent();

          var scale_horizontal = $container.width() / $iframe.attr('width');
          var scale_vertical = $container.height() / $iframe.attr('height');
          var scale = scale_horizontal > scale_vertical ? scale_horizontal : scale_vertical;

          $iframe.width(scale * $iframe.attr('width'));
          $iframe.height(scale * $iframe.attr('height'));
        });
      });

      $(window).trigger('resize');
    }
  };

})(jQuery, Drupal, 'once');
