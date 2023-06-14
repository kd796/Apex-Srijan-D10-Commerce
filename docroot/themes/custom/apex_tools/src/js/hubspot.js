(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.hubspot = {
    attach: function (context, settings) {
      var $contentlist = $('.tabs-content li');
      var $tabslist = $('.tabs li');

      $('.tabs').on('click', 'li', function (e) {
        var $current = $(this);
        var index = $current.index();

        if ($current.hasClass('active-tab')) {
          $current.removeClass('active-tab');
        } else {
          $tabslist.removeClass('active-tab');
          $current.addClass('active-tab');
        }

        if ($contentlist.eq(index).hasClass('active')) {
          $contentlist.eq(index).removeClass('active');
        } else {
          $contentlist.removeClass('active');
          $contentlist.eq(index).addClass('active');
        }
      });
      // end
    }
  };
})(jQuery, Drupal);
