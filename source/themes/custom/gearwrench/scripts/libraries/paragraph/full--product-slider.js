(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentProductSlider = {
    updateSlideAria: function (swiperInstance) {
      $(swiperInstance.slides).attr('aria-hidden', 'true').find('*').attr('tabindex', '-1');
      var index = (swiperInstance.params.slidesPerColumn * swiperInstance.realIndex);
      var itemsPerSlide = (swiperInstance.params.slidesPerColumn * swiperInstance.params.slidesPerView);
      $(swiperInstance.slides).slice(index, index + itemsPerSlide).removeAttr('aria-hidden').find('*').removeAttr('tabindex');
    },
    initProductSlider: function ($productSliderWrapper) {
      if (window.matchMedia('(max-width: 768px)').matches) {
        // eslint-disable-next-line
        return new Swiper($productSliderWrapper, {
          slidesPerView: 2,
          slidesPerGroup: 2,
          slidesPerColumn: 2,
          slidesPerColumnFill: 'column',
          spaceBetween: 24,
          navigation: {
            nextEl: '.component-product-slider__button-next',
            prevEl: '.component-product-slider__button-prev'
          },
          on: {
            init: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.componentProductSlider.updateSlideAria($(this)[0]), 500);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            resize: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              Drupal.behaviors.componentProductSlider.updateSlideAria($(this)[0]);
              // Load lazyloaded images in tabs.
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.componentProductSlider.updateSlideAria($(this)[0]);
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            }
          },
          pagination: {
            el: '.component-product-slider__pagination',
            clickable: true
          },
          watchSlidesVisibility: true,
          watchSlidesProgress: true,
          touchEventsTarget: 'container'
        });
      }
      else {
        // eslint-disable-next-line
        return new Swiper($productSliderWrapper, {
          slidesPerView: 4,
          slidesPerGroup: 4,
          spaceBetween: 24,
          navigation: {
            nextEl: '.component-product-slider__button-next',
            prevEl: '.component-product-slider__button-prev'
          },
          on: {
            init: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.componentProductSlider.updateSlideAria($(this)[0]), 500);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            resize: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              Drupal.behaviors.componentProductSlider.updateSlideAria($(this)[0]);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.componentProductSlider.updateSlideAria($(this)[0]);
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            }
          },
          pagination: {
            el: '.component-product-slider__pagination',
            clickable: true
          },
          watchSlidesVisibility: true,
          watchSlidesProgress: true,
          touchEventsTarget: 'container'
        });
      }
    },
    updatePointerEvents: function () {
      $('.swiper-slide').removeClass('active-pointer');
      $('.swiper-slide-visible').addClass('active-pointer');
    },
    attach: function (context, settings) {
      $('.component-product-slider:not(.component-product-slider--js-initialized)').each(function (index) {
        var $productSwiper;
        var $component = $(this);
        var $productSliderWrapper = $component.find('.component-product-slider__list-wrapper');
        var $productSlider = $component.find('.component-product-slider__list');
        var $productSliderItems = $component.find('.component-product-slider__list-item');
        // Track that this component has been initialized.
        $component.addClass('component-product-slider--js-initialized');

        // Add swiper classes and elements.
        $productSliderWrapper.addClass('swiper-container');
        $productSliderItems.addClass('swiper-slide');
        $productSlider.addClass('swiper-wrapper');
        $productSliderWrapper.after('<div class="component-product-slider__controls"><button class="component-product-slider__button component-product-slider__pseudo-button-prev"></button><div class="component-product-slider__pagination swiper-pagination"></div><button class="component-product-slider__button component-product-slider__pseudo-button-next"></button></div>');

        // Initialize swiper.
        if ($component.find('article').length > 1) {
          $productSlider.after('<button class="component-product-slider__button component-product-slider__button-next swiper-button-next"></button>');
          $productSlider.before('<button class="component-product-slider__button component-product-slider__button-prev swiper-button-prev"></button>');
          $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper);
          $(window).resize(function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
              if (typeof $productSwiper !== 'undefined') {
                $productSwiper.destroy(true, true);
                $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper);
              }
            }
            else {
              if (typeof $productSwiper !== 'undefined') {
                $productSwiper.destroy(true, true);
                $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper);
              }
            }
          });
        }

        $(document).on('click', '.component-product-slider__pseudo-button-prev', function () {
          $('.component-product-slider__button-prev').trigger('click');
        });

        $(document).on('click', '.component-product-slider__pseudo-button-next', function () {
          $('.component-product-slider__button-next').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal);
