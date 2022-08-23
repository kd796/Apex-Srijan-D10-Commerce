(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentContentDriverItem = {
    attach: function (context, settings) {
      $(window).resize(function () {
        // Fill in actions here.
      });

      $(window).trigger('resize');
    }
  };

})(jQuery, Drupal);
