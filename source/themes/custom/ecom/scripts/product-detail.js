(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.productdetail = {
    attach: function (context, settings) {
      // slider.
      $(function () {
        // eslint-disable-next-line no-undef
        var galleryThumbs = new Swiper('.gallery-thumbs', {
          spaceBetween: 10,
          slidesPerView: 4,
          observer: true,
          observeParents: true,
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
      $(once('specskey', $specskey, context)).each(function () {
        var $keylength = $(this).text().replace(/^\s+|\s+$/g, '').length;
        if ($keylength === 0) {
          $(this).parent('.field__item').remove();
        }
      });
      // To seperate the image and videos.
      $('.product-detail-slider').find('.video-field').remove();
      $('.vedio-gallery').find('.image-field').remove();
      // video pop-up.
      $(once('video-popup', '.watch-video, .video-image-thumbnail', context)).magnificPopup({
        type: 'inline',
        mainClass: 'mfp-video-gallery',
        gallery: {
          enabled: false
        }
      });
      // video slider.
      $(function () {
        // eslint-disable-next-line no-undef
        var videoThumbs = new Swiper('.video-thumbs', {
          spaceBetween: 10,
          slidesPerView: 4,
          observer: true,
          observeParents: true,
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
      });

      let videoCount = $('.vedio-gallery .field--name-field-product-images').children().length;
      if (videoCount > 1) {
        $('#video-popup').addClass('multiple-video');
      }

      // end
    }
  };
})(jQuery, Drupal, once);
