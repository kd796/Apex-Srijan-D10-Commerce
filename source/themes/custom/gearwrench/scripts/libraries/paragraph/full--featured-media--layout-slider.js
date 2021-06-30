(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentMediaLayoutSlider = {
    attach: function (context, settings) {
      $('.component-featured-media--layout-slider:not(.component-featured-media--js-initialized)').each(function (index) {
        var $component = $(this);
        var $listWrapper = $component.find('.component-featured-media__list-wrapper');
        var $list = $component.find('.component-featured-media__list');
        var $listItems = $component.find('.component-featured-media__list-item');

        // Track that this component has been initialized.
        $component.addClass('component-featured-media--js-initialized');

        // Add swiper classes and elements.
        $listWrapper.addClass('swiper-container');
        $listItems.addClass('swiper-slide');
        $list.addClass('swiper-wrapper');
        $list.after('<div class="component-featured-media__pagination swiper-pagination"></div>');

        // Initialize swiper.
        // eslint-disable-next-line
        new Swiper($listWrapper, {
          on: {
            init: function () {
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), 500);
            },
            resize: function () {
              Drupal.behaviors.swiper.updateSlideAria.apply(this);
              Drupal.blazy.init.revalidate();
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.swiper.updateSlideAria.apply(this);
              Drupal.blazy.init.revalidate();
            }
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true
          },
          slidesPerGroup: 1,
          slidesPerView: 1,
          touchEventsTarget: 'container'
        });
      });
    }
  };

})(jQuery, Drupal);
