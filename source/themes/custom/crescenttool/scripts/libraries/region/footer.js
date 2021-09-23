(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.regionFooter = {
    attach: function (context, settings) {
      var behavior_object = this;

      if (behavior_object.isMobile()) {
        // Move header and children to block__content
        $('.block-footer-navigation-block__header').prependTo('.block-footer-navigation-block__content');
      }
    },
    isDesktop: function () {
      return ($('body').width() >= 1024);
    },
    isMobile: function () {
      return ($('body').width() < 1024);
    }
  };

})(jQuery, Drupal);
