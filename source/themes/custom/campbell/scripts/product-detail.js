// import {Swiper} from 'swiper';
(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productdetail = {
    attach: function (context, settings) {
      // slider.
      $(function () {
        // eslint-disable-next-line no-undef
        var galleryThumbs = new Swiper('.gallery-thumbs', {
          spaceBetween: 10,
          slidesPerView: 4,
          freeMode: true,
          watchSlidesVisibility: true,
          watchSlidesProgress: true
        });

        // eslint-disable-next-line no-unused-vars, no-undef
        const galleryTop = new Swiper('.gallery-top', {
          spaceBetween: 10,
          effect: 'fade',
          navigation: {
            nextEl: '.image-next',
            prevEl: '.image-prev'
          },
          thumbs: {
            swiper: galleryThumbs
          }
        });
      });
      // Remove empty div for Specifications.
      var $specskey = $('.specs-key');
      $specskey.once('specskey').each(function () {
        var $keylength = $(this).text().replace(/^\s+|\s+$/g, '').length;
        if ($keylength === 0) {
          $(this).parent('.field__item').remove();
        }
      });
      // To seperate the image and videos.
      $('.product-detail-slider').find('.video-field').remove();
      $('.vedio-gallery').find('.image-field').remove();
      // video pop-up.
      $('.watch-video, .video-image-thumbnail').once('video-popup').magnificPopup({
        type: 'inline',
        mainClass: 'mfp-video-gallery',
        gallery: {
          enabled: false
        }
      });
      // video slider.
      // eslint-disable-next-line no-undef
      var videoThumbs = new Swiper('.video-thumbs', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true
      });

      // eslint-disable-next-line no-unused-vars, no-undef
      const videoTop = new Swiper('.video-top', {
        spaceBetween: 10,
        effect: 'fade',
        navigation: {
          nextEl: '.video-next',
          prevEl: '.video-prev'
        },
        thumbs: {
          swiper: videoThumbs
        }
      });

      // end
    }
  };
})(jQuery, Drupal);
