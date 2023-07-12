(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.topMessageCTABar = {
    attach: function (context, settings) {
      var lastScrollTop = 0;
      var topMessageCTABar = $('.component-top-message-cta-bar__content');
      var topofDiv = $('.block-system-main-block').offset().top;

      // On scroll down, header and expanded navigation disappears, scroll up re-appears, logo area resizes.
      window.addEventListener('scroll', function () {
        var st = $(this).scrollTop();
        topofDiv = $('.block-system-main-block').offset().top;

        if (window.scrollY > topofDiv) {
          if (st > lastScrollTop) {
            topMessageCTABar.removeClass('show--with-nav');
            if (!topMessageCTABar.hasClass('show--no-nav')) {
              topMessageCTABar.addClass('show--no-nav');
            }
          }
          else {
            topMessageCTABar.removeClass('show--no-nav');
            if (!topMessageCTABar.hasClass('show--with-nav')) {
              topMessageCTABar.addClass('show--with-nav');
            }
          }
        }
        else {
          topMessageCTABar.removeClass('show--no-nav');
          topMessageCTABar.removeClass('show--with-nav');
        }
        lastScrollTop = st;
      });
    }
  };

})(jQuery, Drupal, 'once');
