(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentContentDriver = {
    resizeButtons: function ($heroSlideButtonContainer) {
      var $buttonHeight = $heroSlideButtonContainer.outerHeight();
      $('.component-content-driver-item__button').css('width', $buttonHeight);
    },
    attach: function (context, settings) {
      $('.component-hero:not(.component-content-driver--js-initialized)').each(function (index) {
        var $component = $(this);
        var $heroWrapper = $component.find('.component-hero__inner');
        var $hero = $component.find('.component-hero__content');
        var $heroItems = $component.find('.component-hero__content article');
        var $heroSlideButtonContainer = $heroItems.find('.component-content-driver-item__button-container');
        var $heroSlideButtonLink = $heroItems.find('.component-content-driver-item__link');
        var $buttonHeight = $heroSlideButtonContainer.outerHeight();

        // Track that this component has been initialized.
        $component.addClass('component-content-driver--js-initialized');

        // Add swiper classes and elements.
        $heroWrapper.addClass('swiper-container');
        $heroItems.addClass('swiper-slide');
        $hero.addClass('swiper-wrapper');
        $heroSlideButtonLink.after('<div class="component-content-driver-item__pagination swiper-pagination"></div>');
        $('.component-content-driver-item__button').css('width', $buttonHeight);

        // Initialize swiper.
        if ($component.find('article').length > 1) {
          $heroSlideButtonContainer.html('<button class="component-content-driver-item__button component-content-driver-item__button-prev swiper-button-prev"></button><button class="component-content-driver-item__button component-content-driver-item__button-next swiper-button-next"></button>');

          // eslint-disable-next-line
          new Swiper($heroWrapper, {
            effect: 'fade',
            loop: true,
            navigation: {
              nextEl: '.component-content-driver-item__button-next',
              prevEl: '.component-content-driver-item__button-prev'
            },
            on: {
              init: function () {
                // Use a timeout on init to make sure to catch contextual links.
                setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), Drupal.behaviors.componentHero.resizeButtons($heroSlideButtonContainer), 500);
              },
              resize: function () {
                setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), Drupal.behaviors.componentHero.resizeButtons($heroSlideButtonContainer), 500);

                if (typeof Drupal.blazy !== 'undefined') {
                  Drupal.blazy.init.revalidate();
                }
              },
              slideChangeTransitionEnd: function () {
                Drupal.behaviors.swiper.updateSlideAria.apply(this);

                if (typeof Drupal.blazy !== 'undefined') {
                  Drupal.blazy.init.revalidate();
                }
              }
            },
            pagination: {
              el: '.component-content-driver-item__pagination',
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
