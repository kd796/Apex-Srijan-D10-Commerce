(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentBannerCTASlider = {
    initBannerSlider: function ($sliderContainer, $buttonPrev, $buttonNext, $thumbsContainer, $thumbsItems, $button) {
      var $galleryThumbs;
      if (window.matchMedia('(max-width: 768px)').matches) {
        // eslint-disable-next-line
        $galleryThumbs = new Swiper($thumbsContainer, {
          slidesPerView: 1,
          loop: false,
          freeMode: true,
          watchSlidesVisibility: true,
          watchSlidesProgress: true
        });
      }
      else {
        // eslint-disable-next-line
        $galleryThumbs = new Swiper($thumbsContainer, {
          spaceBetween: 24,
          slidesPerView: 6,
          direction: 'vertical',
          loop: false,
          freeMode: true,
          watchSlidesVisibility: true,
          watchSlidesProgress: true
        });
      }
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
            Drupal.behaviors.componentBannerCTASlider.updatePointerEvents();
            // Use a timeout on init to make sure to catch contextual links.
            setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);
          },
          resize: function () {
            Drupal.behaviors.componentBannerCTASlider.updatePointerEvents();
            Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          },
          slideChangeTransitionEnd: function () {
            Drupal.behaviors.componentBannerCTASlider.updatePointerEvents();
            Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          }
        },
        slidesPerGroup: 1,
        slidesPerView: 1,
        touchEventsTarget: 'container',
        thumbs: {
          swiper: $galleryThumbs
        }
      });
    },
    updatePointerEvents: function () {
      $('.swiper-slide').removeClass('active-pointer');
      $('.swiper-slide-visible').addClass('active-pointer');
    },
    attach: function (context, settings) {
      $('.component-banner-cta-slider:not(.component-banner-cta-slider--js-initialized)').each(function (index) {
        // Main Slider.
        var $bannerSwiper;
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
        if ($component.find('.component-banner-cta-slide').length > 1) {
          var $buttonPrev = $component.find('.component-banner-cta-slider__button-prev');
          var $buttonNext = $component.find('.component-banner-cta-slider__button-next');
          var $button = $component.find('.component-banner-cta-slider__button');

          $bannerSwiper = Drupal.behaviors.componentBannerCTASlider.initBannerSlider($sliderContainer, $buttonPrev, $buttonNext, $thumbsContainer, $thumbsItems, $button);
          $(window).resize(function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
              if (typeof $productSwiper !== 'undefined') {
                $bannerSwiper.destroy(true, true);
                $bannerSwiper = Drupal.behaviors.componentBannerCTASlider.initBannerSlider($sliderContainer, $buttonPrev, $buttonNext, $thumbsContainer, $thumbsItems, $button);
              }
            }
            else {
              if (typeof $bannerSwiper !== 'undefined') {
                $bannerSwiper.destroy(true, true);
                $bannerSwiper = Drupal.behaviors.componentBannerCTASlider.initBannerSlider($sliderContainer, $buttonPrev, $buttonNext, $thumbsContainer, $thumbsItems, $button);
              }
            }
          });
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

})(jQuery, Drupal);
