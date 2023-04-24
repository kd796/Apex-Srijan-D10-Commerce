(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.campbell = {
    attach: function (context, settings) {
      // Toggle search for mobile.
      $(function () {
        const searchIcon = $('.search .icon');
        const closeSearchIcon = $('.search .close');
        const SearchBlock = $('.search-wrap');
        // Open the Search block on click of Icon.
        searchIcon.click(function () {
          SearchBlock.addClass('show-search');
        });
        // Close the Search block on click of close.
        closeSearchIcon.click(function () {
          SearchBlock.removeClass('show-search');
        });
      });
      // Toggle hamburger menumobile.
      $(function () {
        const hamburgerMenuIcon = $('.hamburger');
        const hamburgerMenu = $('.nav-mobile');
        hamburgerMenuIcon
          .once('hamburgerMenu')
          .on('click', function () {
            hamburgerMenu.toggleClass('show-menu');
            hamburgerMenuIcon.toggleClass('close-icon');
          });
      });
      // Responsive menu.
      $(function () {
        // first level menu collapse.
        const firstLevel_li = $('.navbar-nav > li.dropdown');
        firstLevel_li.once('first-dropdown')
          .on('click', function (e) {
            $(this).toggleClass('active');
            e.stopPropagation();
          });
      });

      /* end */
    }
  };
})(jQuery, Drupal);
