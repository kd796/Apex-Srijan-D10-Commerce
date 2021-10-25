(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentProductSlider = {
    initProductSlider: function ($productSliderWrapper, $swiperPagination, $buttonPrev, $buttonNext) {
      if (window.matchMedia('(max-width: 768px)').matches) {
        // eslint-disable-next-line
        return new Swiper($productSliderWrapper, {
          slidesPerView: 2,
          slidesPerGroup: 2,
          slidesPerColumn: 2,
          slidesPerColumnFill: 'column',
          spaceBetween: 24,
          loop: true,
          navigation: {
            nextEl: $buttonNext,
            prevEl: $buttonPrev
          },
          on: {
            init: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            resize: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              // Load lazyloaded images in tabs.
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            }
          },
          pagination: {
            el: $swiperPagination,
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
            nextEl: $buttonNext,
            prevEl: $buttonPrev
          },
          on: {
            init: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            resize: function () {
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              Drupal.behaviors.componentProductSlider.updatePointerEvents();
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            }
          },
          pagination: {
            el: $swiperPagination,
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
        // Initialize swiper.
        if ($component.find('article').length > 4) {
          // Add swiper classes and elements.
          $productSliderWrapper.addClass('swiper-container');
          $productSliderItems.addClass('swiper-slide');
          $productSlider.addClass('swiper-wrapper');
          $productSliderWrapper.after('<div class="component-product-slider__controls"><button class="component-product-slider__button component-product-slider__pseudo-button-prev"></button><div class="component-product-slider__pagination swiper-pagination"></div><button class="component-product-slider__button component-product-slider__pseudo-button-next"></button></div>');
          $productSlider.after('<button class="component-product-slider__button component-product-slider__button-next swiper-button-next"></button>');
          $productSlider.before('<button class="component-product-slider__button component-product-slider__button-prev swiper-button-prev"></button>');

          // Assign pagination to current slider
          var $swiperPagination = $component.find('.swiper-pagination');

          var $buttonPrev = $component.find('.component-product-slider__button-prev');
          var $buttonNext = $component.find('.component-product-slider__button-next');

          $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper, $swiperPagination, $buttonPrev, $buttonNext);
          $(window).resize(function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
              if (typeof $productSwiper !== 'undefined') {
                $productSwiper.destroy(true, true);
                $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper, $swiperPagination, $buttonPrev, $buttonNext);
              }
            }
            else {
              if (typeof $productSwiper !== 'undefined') {
                $productSwiper.destroy(true, true);
                $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper, $swiperPagination, $buttonPrev, $buttonNext);
              }
            }
          });

          // On tab click initialize Swiper in tab to activate.
          if ($($component).parents('.component-tabs').length > 0) {
            $($component).parents().find('.component-tabs__nav-item').once().click(function () {
              var $navItemControls = $(this).attr('aria-controls');
              var $tabsTab = $($component).parents('.component-tabs__content').find('#' + $navItemControls);
              $productSliderWrapper = $tabsTab.find('.component-product-slider__list-wrapper');
              $swiperPagination = $tabsTab.find('.swiper-pagination');
              $buttonPrev = $tabsTab.find('.component-product-slider__button-prev');
              $buttonNext = $tabsTab.find('.component-product-slider__button-next');

              $productSwiper.destroy(true, true);
              setTimeout(function () { $productSwiper = Drupal.behaviors.componentProductSlider.initProductSlider($productSliderWrapper, $swiperPagination, $buttonPrev, $buttonNext); }, 100);
            });
          }
        }

        $($component).on('click', '.component-product-slider__pseudo-button-prev', function () {
          $component.find('.component-product-slider__button-prev').trigger('click');
        });

        $($component).on('click', '.component-product-slider__pseudo-button-next', function () {
          $component.find('.component-product-slider__button-next').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal);
