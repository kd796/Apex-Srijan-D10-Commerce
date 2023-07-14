
(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.instagramFeed = {
    attach: function (context, settings) {
      var behavior_object = this;
      var $instagramRoot = $('.block-apex-tools-instagram-feed-social-feed');
      var $instagramContainer = $instagramRoot.find('.view-social-feed');
      let mobile = window.matchMedia('(min-width: 0px) and (max-width: 768px)');

      $(once('.block-apex-tools-instagram-feed-social-feed__content', window, context)).on('resize', function () {
        if (!$instagramRoot.hasClass('instagram-init')) {
          var $instagramSwiper;
          $instagramRoot.addClass('instagram-init');
          behavior_object.initSliderClasses($instagramRoot);
          if (mobile.matches) {
            $instagramSwiper = behavior_object.swiper($instagramContainer);
          }
          else if ($instagramSwiper) {
            $instagramSwiper.destroy(true, true);
          }
          $(window).on('resize', function () {
            if (typeof $instagramSwiper !== 'undefined') {
              $instagramSwiper.destroy(true, true);
            }

            if (mobile.matches) {
              $instagramSwiper = behavior_object.swiper($instagramContainer);
            }
          });
        }
      });

      $(window).trigger('resize');
    },
    initSliderClasses: function ($rootEl) {
      var $instagramContainer = $rootEl.find('view-social-feed');
      var $instagramWrapper = $rootEl.find('.view-content');
      var $instagramSlides = $rootEl.find('.views-row');

      $instagramContainer.addClass('swiper-container');
      $instagramWrapper.addClass('swiper-wrapper');
      $instagramSlides.addClass('swiper-slide');
      $instagramWrapper.after('<div class="view-instagram-slider__pagination swiper-pagination"></div>');
    },
    swiper: function ($targetEl) {
    // eslint-disable-next-line
      return new Swiper($targetEl, {
        slidesPerView: 1,
        spaceBetween: 0,
        direction: 'horizontal',
        resize: function () {
          if (typeof Drupal.blazy !== 'undefined') {
            Drupal.blazy.init.revalidate();
          }
        },
        pagination: {
          el: '.view-instagram-slider__pagination',
          clickable: true
        }
      });
    }
  };

})(jQuery, Drupal, once);
