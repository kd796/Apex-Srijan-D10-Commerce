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
          loop: false,
          navigation: {
            nextEl: '.view-related-products-slider__button-next',
            prevEl: '.view-related-products-slider__button-prev'
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
            el: '.view-related-products-slider__pagination',
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
          loop: true,
          navigation: {
            nextEl: '.view-related-products-slider__button-next',
            prevEl: '.view-related-products-slider__button-prev'
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
            el: '.view-related-products-slider__pagination',
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
      $('.view-related-products:not(.view-related-products--js-initialized)').once('related-products-slider').each(function (index) {
        var $productSwiper;
        var $component = $(this);
        var $productSliderWrapper = $component.find('.view-related-products-slider__list-wrapper');
        var $productSlider = $component.find('.view-related-products-slider__list');
        var $productSliderItems = $component.find('.view-related-products-slider__list-item');
        // Track that this component has been initialized.
        $component.addClass('view-related-products-slider--js-initialized');
        // Initialize swiper.
        if ($component.find('article').length > 1) {
          // Add swiper classes and elements.
          $productSliderWrapper.addClass('swiper-container');
          $productSliderItems.addClass('swiper-slide');
          $productSlider.addClass('swiper-wrapper');
          $productSliderWrapper.after('<div class="view-related-products-slider__controls"><button class="view-related-products-slider__button view-related-products-slider__pseudo-button-prev"></button><div class="view-related-products-slider__pagination swiper-pagination"></div><button class="view-related-products-slider__button view-related-products-slider__pseudo-button-next"></button></div>');
          $productSlider.after('<button class="view-related-products-slider__button view-related-products-slider__button-next swiper-button-next"></button>');
          $productSlider.before('<button class="view-related-products-slider__button view-related-products-slider__button-prev swiper-button-prev"></button>');
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

        $(document).on('click', '.view-related-products-slider__pseudo-button-prev', function () {
          $('.view-related-products-slider__button-prev').trigger('click');
        });

        $(document).on('click', '.view-related-products-slider__pseudo-button-next', function () {
          $('.view-related-products-slider__button-next').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal);
