(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.gallery = {
    attach: function (context, settings) {
      // Accordian for the gallery.
      var galleryCardImage = $('.gallery-grid__item .image');
      var closeCard = $('.gallery-grid__item .close');
      $(once('accordion', galleryCardImage, context)).on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        var galleryCard = $this.parent('.gallery-grid__item');

        if (galleryCard.hasClass('active')) {
          galleryCard.removeClass('active');
          galleryCard.find('.bottom').removeClass('active');
        }
        else {
          $('.gallery-grid__item').removeClass('active');
          galleryCard.addClass('active');
          $('.bottom').removeClass('active');
          galleryCard.find('.bottom').addClass('active');
          // To set the height for the description block.
          var descHeight = galleryCard.find('.description').innerHeight();
          $('.bottom').css('height', '');
          galleryCard.find('.bottom').css({height: descHeight + 'px'});
        }
      });
      // Close the card on click of icon.
      $(once('accordion', closeCard, context)).on('click', function (e) {
        var galleryCard = $('.gallery-grid__item');
        galleryCard.removeClass('active');
        $('.bottom').removeClass('active').css('height', '');
      });
    }
  };
})(jQuery, Drupal, once);
