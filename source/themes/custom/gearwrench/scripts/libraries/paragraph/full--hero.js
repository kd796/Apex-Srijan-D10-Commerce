(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentHero = {
    attach: function (context, settings) {
      $('.component-hero:not(.component-hero--js-initialized)').each(function (index) {
        var $component = $(this);
        var $heroWrapper = $component.find('.component-hero__inner');
        var $hero = $component.find('.component-hero__content');
        var $heroItems = $component.find('.component-hero__content article');

        // Track that this component has been initialized.
        $component.addClass('component-hero--js-initialized');

        // Add swiper classes and elements.
        $heroWrapper.addClass('swiper-container');
        $heroItems.addClass('swiper-slide');
        $hero.addClass('swiper-wrapper');
        // $hero.after('<div class="component-hero__pagination swiper-pagination"></div>');

        // Initialize swiper.
        if ($component.find('article').length > 1) {
          // $hero.after('<button class="component-hero__button-next swiper-button-next"></button>');
          // $hero.before('<button class="component-hero__button-prev swiper-button-prev"></button>');

          // eslint-disable-next-line
          new Swiper($heroWrapper, {
            effect: 'fade',
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev'
            },
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
        }
      });
    }
  };

})(jQuery, Drupal);
