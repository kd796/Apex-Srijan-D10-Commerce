(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.campbell = {
    attach: function (context, settings) {
      // Toggle search for mobile.
      $(function () {
        const searchIcon = $('.mobile-search-icon');
        const closeSearchIcon = $('.m-s-close');
        const SearchBlock = $('.mobile-search-block');
        // Open the Search block on click of Icon.
        searchIcon.click(function () {
          SearchBlock.removeClass('d-none');
        });
        // Close the Search block on click of close.
        closeSearchIcon.click(function () {
          SearchBlock.addClass('d-none');
        });
      });
      // Toggle hamburger menumobile.
      $(function () {
        const hamburgerMenuIcon = $('.mobile-nav-icon');
        const hamburgerMenu = $('.mobile-header');
        hamburgerMenuIcon
          .once('hamburgerMenu')
          .on('click', function () {
            hamburgerMenu.toggle('d-none');
          });
      });
      //  Responsive menu
      $(function () {
        const secondLevel_li = $('.navbar-we-mega-menu.navbar ul>li>ul');
        const thirdLevel_li = $('.navbar-we-mega-menu.navbar ul>li>ul li>ul');
        secondLevel_li.siblings().addClass('second-dropdown');
        thirdLevel_li.siblings().addClass('third-dropdown').removeClass('second-dropdown');
        $('.second-dropdown')
          .once('second-dropdown')
          .on('click', function (e) {
            // $('.second-dropdown').not(this).siblings('ul').removeClass('open-second-level');
            $(this).siblings('ul').toggleClass('open-second-level');
          });
        $('.third-dropdown')
          .once('third-dropdown')
          .on('click', function (e) {
            // $('.third-dropdown').not(this).siblings('ul').removeClass('open-third-level');
            $(this).siblings('ul').toggleClass('open-third-level');
          });
      });

      /* end */
    }
  };
})(jQuery, Drupal);
