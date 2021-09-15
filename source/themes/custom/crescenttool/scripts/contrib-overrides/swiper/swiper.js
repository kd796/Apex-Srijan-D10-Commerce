(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.swiper = {
    updateSlideAria: function () {
      $(this.slides).attr('aria-hidden', 'true').find('*').attr('tabindex', '-1');
      var index = (this.params.slidesPerColumn * this.realIndex);
      var itemsPerSlide = (this.params.slidesPerColumn * this.params.slidesPerView);
      $(this.slides).slice(index, index + itemsPerSlide).removeAttr('aria-hidden').find('*').removeAttr('tabindex');
    }
  };

})(jQuery, Drupal);
