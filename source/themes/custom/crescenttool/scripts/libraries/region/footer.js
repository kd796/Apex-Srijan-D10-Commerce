(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.regionFooter = {
    attach: function (context, settings) {
      var behavior_object = this;
      var $footerHeader = $('.block-footer-navigation-block__header');
      var $footerNav = $('.block-footer-navigation-block__nav');
      var $footerNavContent = $footerNav.find('.block-footer-navigation-block__content');

      // Move header and children to block__content above address
      if (behavior_object.isMobile()) {
        behavior_object.elMove($footerHeader, $footerNavContent);
      }

      $(window).once('footer').on('resize', function () {
        if (behavior_object.isMobile()) {
          behavior_object.elMove($footerHeader, $footerNavContent);
        }
        else {
          behavior_object.elMove($footerHeader, $footerNav);
        }
      });
    },
    isDesktop: function () {
      return ($('body').width() > 1024);
    },
    isMobile: function () {
      return ($('body').width() <= 1024);
    },
    elMove: function ($elMove, $elMoveTo) {
      $($elMove).prependTo($elMoveTo);
    }
  };

})(jQuery, Drupal, 'once');
