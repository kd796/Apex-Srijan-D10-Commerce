(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.swiper = {
    updateSlideAria: function () {
      $(this.slides).each(function () {
        if ($(this).hasClass('swiper-slide-visible') || $(this).hasClass('swiper-slide-active')) {
          $(this).removeAttr('aria-hidden').removeAttr('tabindex');
        }
        else {
          $(this).attr('aria-hidden', 'true').attr('tabindex', '-1');
        }
      });
    }
  };

})(jQuery, Drupal, once);
