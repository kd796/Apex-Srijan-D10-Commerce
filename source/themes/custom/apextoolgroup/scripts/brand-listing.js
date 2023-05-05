(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.apextoolgroup = {
    attach: function (context, settings) {
      // Brand Filter Toggle hamburger.
      const filterHamburger = $('.brands__filter-icon');
      const brandSidebar = $('.brands__sidebar');
      filterHamburger
        .once('filterHamburger')
        .on('click', function () {
          brandSidebar.addClass('is-active');
        });

      // Brand Filter Close Filter.
      const filterClose = $('.brands__filter-close-icon');
      filterClose
        .once('filterClose')
        .on('click', function () {
          brandSidebar.removeClass('is-active');
        });

      /* end */
    }
  };
})(jQuery, Drupal);
