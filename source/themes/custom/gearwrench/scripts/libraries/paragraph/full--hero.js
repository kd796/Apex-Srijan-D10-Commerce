(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentHero = {
    attach: function (context, settings) {
      $('.component-hero:not(.component-hero--js-initialized)').each(function (index) {
        var $component = $(this);
        var $heroWrapper = $component.find('.component-hero__inner');
        var $hero = $component.find('.component-hero__content');
        var $heroItems = $component.find('.component-hero__content article');
        var $heroSlideButtonContainer = $heroItems.find('.component-hero-slide__button-container');
        var $heroSlideFooter = $heroItems.find('.component-hero-slide__footer');
        var $buttonHeight = $heroSlideButtonContainer.outerHeight();

        // Track that this component has been initialized.
        $component.addClass('component-hero--js-initialized');

        // Add swiper classes and elements.
        $heroWrapper.addClass('swiper-container');
        $heroItems.addClass('swiper-slide');
        $hero.addClass('swiper-wrapper');
        $heroSlideFooter.after('<div class="component-hero-slide__pagination swiper-pagination"></div>');
        $('.component-hero-slide__button').css('width', $buttonHeight);

        // Initialize swiper.
        if ($component.find('article').length > 1) {
          $heroSlideButtonContainer.html('<button class="component-hero-slide__button component-hero-slide__button-prev swiper-button-prev"></button><button class="component-hero-slide__button component-hero-slide__button-next swiper-button-next"></button>');
          // eslint-disable-next-line
          new Swiper($heroWrapper, {
            effect: 'fade',
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev'
            },
            on: {
              init: function () {
                var $buttonHeight = $heroSlideButtonContainer.outerHeight();
                setTimeout($('.component-hero-slide__button').css('width', $buttonHeight), 500);
                // Use a timeout on init to make sure to catch contextual links.
                setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), 500);
              },
              resize: function () {
                var $buttonHeight = $heroSlideButtonContainer.outerHeight();
                setTimeout($('.component-hero-slide__button').css('width', $buttonHeight), 500);
                Drupal.behaviors.swiper.updateSlideAria.apply(this);
                Drupal.blazy.init.revalidate();
              },
              slideChangeTransitionEnd: function () {
                setTimeout($('.component-hero-slide__button').css('width', $buttonHeight), 500);
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
