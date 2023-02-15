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
      //  Responsive menu.
      $(function () {
        // first level menu collapse.
        const firstLevel_li = $('.navbar-we-mega-menu.navbar li.we-mega-menu-li');
        firstLevel_li.removeClass('active active-trail open');
        firstLevel_li.once('first-dropdown')
          .on('click', function (e) {
            $(this).toggleClass('active active-trail open');
            e.stopPropagation();
          });
        // second level menu collapse.
        const secondLevel_li = $('.navbar-we-mega-menu.navbar ul>li>ul');
        secondLevel_li.addClass('second-ul');
        secondLevel_li.prev().addClass('second-dropdown');
        $('.second-dropdown')
          .once('second-dropdown')
          .on('click', function (e) {
            $(this).toggleClass('open');
            $(this).next('ul').toggleClass('open-second-level');
            e.stopPropagation();
          });
        // third level menu collapse.
        const thirdLevel_li = $('.navbar-we-mega-menu.navbar ul>li>ul li>ul');
        thirdLevel_li.addClass('third-ul').removeClass('second-ul');
        thirdLevel_li.prev().addClass('third-dropdown').removeClass('second-dropdown');
        $('.third-dropdown')
          .once('third-dropdown')
          .on('click', function (e) {
            if ($(this).hasClass('third-ul')) {
              $(this).toggleClass('open');
            }
            $(this).next('ul').toggleClass('open-third-level');
            e.stopPropagation();
          });
      });
      // slider.
      $(function () {
        var homeSlider = $('.slider');
        var activeLink = $('.home-text');
        homeSlider.once('homeSlider').owlCarousel({
          items: 1,
          loop: false,
          rewind: true,
          margin: 10,
          autoplay: 5000,
          nav: false,
          dots: false,
          animateOut: 'fadeOut',
          animateIn: 'fadeIn'
        });
        activeLink.once('activeLink').owlCarousel({
          items: 1,
          loop: false,
          rewind: true,
          autoplay: false,
          nav: false,
          dots: false,
          animateOut: 'fadeOut',
          animateIn: 'fadeIn'
        });
        homeSlider.once('homeSliderChange').on('changed.owl.carousel', function (e) {
          activeLink.trigger('next.owl.carousel');
        });
      });

      /* end */
    }
  };
})(jQuery, Drupal);
