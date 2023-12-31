(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentBannerCTASlider = {
    initBannerSlider: function ($sliderContainer, $swiperPagination, $buttonPrev, $buttonNext, $thumbsContainer) {
      // eslint-disable-next-line
      var $galleryThumbs = new Swiper($thumbsContainer, {
        slidesPerView: 6,
        direction: 'vertical',
        spaceBetween: 10,
        breakpoints: {
          1023: {
            slidesPerView: 1,
            direction: 'horizontal',
            spaceBetween: 0
          }
        },
        loop: false,
        freeMode: false,
        nested: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
        momentum: false
      });

      // eslint-disable-next-line
      return new Swiper($sliderContainer, {
        loop: false,
        direction: 'horizontal',
        navigation: {
          nextEl: $buttonNext,
          prevEl: $buttonPrev
        },
        on: {
          init: function () {
            // Use a timeout on init to make sure to catch contextual links.
            setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);
          },
          resize: function () {
            Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          },
          slideChangeTransitionEnd: function () {
            Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          }
        },
        slidesPerGroup: 1,
        slidesPerView: 1,
        momentum: false,
        thumbs: {
          swiper: $galleryThumbs
        },
        pagination: {
          el: $swiperPagination,
          clickable: true
        }
      });
    },
    attach: function (context, settings) {
      $('.component-banner-cta-slider:not(.component-banner-cta-slider--js-initialized)').each(function (index) {
        // Main Slider.
        var $component = $(this);
        var $sliderContainer = $component.find('.component-banner-cta-slider__container');
        var $sliderWrapper = $sliderContainer.find('.component-banner-cta-slider__content');
        var $sliderItems = $sliderContainer.find('.component-banner-cta-slide');

        // Thumb Slider
        var $thumbsContainer = $component.find('.component-banner-cta-slider__thumbs-container');
        var $thumbsWrapper = $thumbsContainer.find('.component-banner-cta-slider__thumbs-wrapper');
        var $thumbsItems = $thumbsContainer.find('.component-banner-cta-slide');

        // Track that this component has been initialized.
        $component.addClass('component-banner-cta-slider--js-initialized');

        // Add swiper classes and elements.
        $sliderContainer.addClass('swiper-container');
        $sliderItems.addClass('swiper-slide');
        $sliderWrapper.addClass('swiper-wrapper');
        $thumbsContainer.addClass('swiper-container');
        $thumbsItems.addClass('swiper-slide');
        $thumbsWrapper.addClass('swiper-wrapper');

        // Initialize swiper.
        if ($component.find('.component-banner-cta-slider__container .component-banner-cta-slide').length > 1) {
          $sliderWrapper.after('<div class="component-banner-cta-slider__controls"><div class="component-banner-cta-slider__pagination swiper-pagination"></div></div>');
          var $buttonPrev = $component.find('.component-banner-cta-slider__button-prev');
          var $buttonNext = $component.find('.component-banner-cta-slider__button-next');
          var $swiperPagination = $component.find('.swiper-pagination');
          Drupal.behaviors.componentBannerCTASlider.initBannerSlider($sliderContainer, $swiperPagination, $buttonPrev, $buttonNext, $thumbsContainer);
        }

        $($component).on('click', '.component-banner-cta-slider__pseudo-prev-button', function () {
          $component.find('.component-banner-cta-slider__button-prev').trigger('click');
        });

        $($component).on('click', '.component-banner-cta-slider__pseudo-next-button', function () {
          $component.find('.component-banner-cta-slider__button-next').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal, once);
