(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.crescenttool = {
    attach: function (context, settings) {
      function focusKeyDown(e) {
        if ([9, 13, 32, 38, 40].indexOf(e.keyCode) !== -1) {
          $('body').addClass('keyboard-activated');
          $(window).on('mousedown.keyboard', focusMouseDown);
          $(window).off('keydown.keyboard');
        }
      }

      function focusMouseDown(e) {
        $('body').removeClass('keyboard-activated');
        $(window).on('keydown.keyboard', focusKeyDown);
        $(window).off('mousedown.keyboard');
      }

      $(window).on('keydown.keyboard', focusKeyDown);

      // Remove all svg IDs.
      $('svg').removeAttr('id');

      // Toggle class on body for if modal is open.
      $(window).on({
        'dialog:aftercreate': function (dialog, $element, settings) {
          $('body').addClass('ui-dialog-open');
        },
        'dialog:afterclose': function (dialog, $element, settings) {
          $('body').removeClass('ui-dialog-open');
        }
      });
    }
  };
})(jQuery, Drupal);
