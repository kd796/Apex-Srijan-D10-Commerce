(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentMediaLayoutSlider = {
    // updateSlideAria: function (swiperInstance) {
    //   $(swiperInstance.slides).attr('aria-hidden', 'true').find('*').attr('tabindex', '-1');
    //   var index = (swiperInstance.params.slidesPerColumn * swiperInstance.realIndex);
    //   var itemsPerSlide = (swiperInstance.params.slidesPerColumn * swiperInstance.params.slidesPerView);
    //   $(swiperInstance.slides).slice(index, index + itemsPerSlide).removeAttr('aria-hidden').find('*').removeAttr('tabindex');
    // }, see if can get swiper.updateSlideAria to work
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
        $component.after('<div class="component-media__pagination-wrapper"></div>');
        $listWrapper.after('<div class="component-featured-media__pagination swiper-pagination"></div>');

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
