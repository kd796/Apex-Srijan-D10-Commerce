(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentHero = {
    resizeButtons: function ($heroSlideButtonContainer) {
      var $buttonHeight = $heroSlideButtonContainer.outerHeight();
      $('.component-hero-slide__button').css('width', $buttonHeight);
    },
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
            loop: true,
            navigation: {
              nextEl: '.component-hero-slide__button-next',
              prevEl: '.component-hero-slide__button-prev'
            },
            on: {
              init: function () {
                // Use a timeout on init to make sure to catch contextual links.
                setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), Drupal.behaviors.componentHero.resizeButtons($heroSlideButtonContainer), 500);
              },
              resize: function () {
                setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), Drupal.behaviors.componentHero.resizeButtons($heroSlideButtonContainer), 500);
                Drupal.blazy.init.revalidate();
              },
              slideChange: function (el) {
                $('.swiper-slide').each(function () {
                  var youtubePlayer = $(this).find('iframe').get(0);

                  if (youtubePlayer) {
                    youtubePlayer.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                  }
                });
              },
              slideChangeTransitionEnd: function () {
                Drupal.behaviors.swiper.updateSlideAria.apply(this);
                Drupal.blazy.init.revalidate();
              }
            },
            pagination: {
              el: '.component-hero-slide__pagination',
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

})(jQuery, Drupal, 'once');
