(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentMediaLayoutSlider = {
    initListSlider: function ($listWrapper, $swiperPagination) {
      if (window.matchMedia('(max-width: 768px)').matches) {
        // eslint-disable-next-line
        return new Swiper($listWrapper, {
          slidesPerView: 1,
          slidesPerGroup: 1,
          navigation: {
            nextEl: '.component-featured-media__button-next',
            prevEl: '.component-featured-media__button-prev'
          },
          on: {
            init: function () {
              Drupal.behaviors.componentMediaLayoutSlider.updatePointerEvents();
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            resize: function () {
              Drupal.behaviors.componentMediaLayoutSlider.updatePointerEvents();
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              // Load lazyloaded images in tabs.
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              Drupal.behaviors.componentMediaLayoutSlider.updatePointerEvents();
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            }
          },
          pagination: {
            el: $swiperPagination,
            clickable: true
          },
          touchEventsTarget: 'container'
        });
      }
      else {
        // eslint-disable-next-line
        return new Swiper($listWrapper, {
          slidesPerView: 1,
          slidesPerGroup: 1,
          navigation: {
            nextEl: '.component-featured-media__button-next',
            prevEl: '.component-featured-media__button-prev'
          },
          on: {
            init: function () {
              Drupal.behaviors.componentMediaLayoutSlider.updatePointerEvents();
              // Use a timeout on init to make sure to catch contextual links.
              setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            resize: function () {
              Drupal.behaviors.componentMediaLayoutSlider.updatePointerEvents();
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            },
            slideChangeTransitionEnd: function () {
              Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
              Drupal.behaviors.componentMediaLayoutSlider.updatePointerEvents();
              if (typeof Drupal.blazy !== 'undefined') {
                Drupal.blazy.init.revalidate();
              }
            }
          },
          pagination: {
            el: $swiperPagination,
            clickable: true
          },
          touchEventsTarget: 'container'
        });
      }
    },
    updatePointerEvents: function () {
      $('.swiper-slide').removeClass('active-pointer');
      $('.swiper-slide-visible').addClass('active-pointer');
    },
    attach: function (context, settings) {
      $('.component-featured-media--layout-slider:not(.component-featured-media--js-initialized)').each(function (index) {
        var $listSwiper;
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
        $listWrapper.after('<div class="component-featured-media__controls"><button class="component-featured-media__button component-featured-media__pseudo-button-prev"></button><div class="component-featured-media__pagination swiper-pagination"></div><button class="component-featured-media__button component-featured-media__pseudo-button-next"></button></div>');

        // Assign pagination to current slider
        var $swiperPagination = $component.find('.swiper-pagination');

        // Initialize swiper.
        if ($component.find('figure').length > 1) {
          $list.after('<button class="component-featured-media__button component-featured-media__button-next swiper-button-next"></button>');
          $list.before('<button class="component-featured-media__button component-featured-media__button-prev swiper-button-prev"></button>');
          $listSwiper = Drupal.behaviors.componentMediaLayoutSlider.initListSlider($listWrapper, $swiperPagination);

          $(window).resize(function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
              if (typeof $listSwiper !== 'undefined') {
                $listSwiper.destroy(true, true);
                $listSwiper = Drupal.behaviors.componentMediaLayoutSlider.initListSlider($listWrapper, $swiperPagination);
              }
            }
            else {
              if (typeof $listSwiper !== 'undefined') {
                $listSwiper.destroy(true, true);
                $listSwiper = Drupal.behaviors.componentMediaLayoutSlider.initListSlider($listWrapper, $swiperPagination);
              }
            }
          });
        }

        $($component).on('click', '.component-featured-media__pseudo-button-prev', function () {
          $('.component-featured-media__button-prev').trigger('click');
        });

        $($component).on('click', '.component-featured-media__pseudo-button-next', function () {
          $('.component-featured-media__button-next').trigger('click');
        });
      });
    }
  };

})(jQuery, Drupal);
