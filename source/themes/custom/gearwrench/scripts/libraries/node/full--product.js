(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productDetailImageSlider = {
    attach: function (context, settings) {
      $('.product-detail-slider:not(.product-detail-slider--js-initialized)').each(function (index) {
        // Main Slider.
        var $component = $(this);
        var $sliderContainer = $component.find('.product-detail-slider__container');
        var $sliderWrapper = $sliderContainer.find('.field--name-field-product-images');
        var $sliderItems = $sliderContainer.find('.field--name-field-product-images .field__item');

        // Thumb Slider
        var $thumbsContainer = $component.find('.product-detail-slider__thumbs-container');
        var $thumbsWrapper = $thumbsContainer.find('.product-detail-slider__thumbs-wrapper');
        var $thumbsItems = $thumbsContainer.find('.product-detail-slider__thumbs-wrapper img');

        // Track that this component has been initialized.
        $component.addClass('product-detail-slider--js-initialized');

        // Add swiper classes and elements.
        $sliderContainer.addClass('swiper-container');
        $sliderItems.addClass('swiper-slide');
        $sliderWrapper.addClass('swiper-wrapper');
        $thumbsContainer.addClass('swiper-container');
        $thumbsItems.addClass('swiper-slide');
        $thumbsWrapper.addClass('swiper-wrapper');

        // Initialize swiper.
        if ($component.find('.field__item').length > 1) {
          // eslint-disable-next-line
          var $galleryThumbs = new Swiper($thumbsContainer, {
            spaceBetween: 24,
            slidesPerView: 4,
            breakpoints: {
              568: {
                slidesPerView: 6,
                spaceBetween: 12
              },
              1024: {
                slidesPerView: 4,
                spaceBetween: 16
              }
            },
            freeMode: true,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,

            on: {
              resize: function () {
                var $thumbHeight = $thumbsItems.outerHeight();
                var $thumbWidth = $thumbsItems.outerWidth();
                $('.product-detail-slider__button').css('height', $thumbHeight).css('width', $thumbWidth);
              },
              imagesReady: function () {
                var $thumbHeight = $thumbsItems.outerHeight();
                var $thumbWidth = $thumbsItems.outerWidth();
                $('.product-detail-slider__button').css('height', $thumbHeight).css('width', $thumbWidth);
              }
            }
          });
          // eslint-disable-next-line
          new Swiper($sliderContainer, {
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
            slidesPerGroup: 1,
            slidesPerView: 1,
            touchEventsTarget: 'container',
            thumbs: {
              swiper: $galleryThumbs
            }
          });
        }

        $(document).on('click', '.product-detail-slider__pseudo-prev-button', function () {
          $('.swiper-button-prev').trigger('click');
        });

        $(document).on('click', '.product-detail-slider__pseudo-next-button', function () {
          $('.swiper-button-next').trigger('click');
        });

      });
    }
  };

})(jQuery, Drupal);
