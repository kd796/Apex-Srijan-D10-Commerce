(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.apextoolgroup = {
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
            $('footer').toggleClass('hide-content');
            $('.main-contane').toggleClass('hide-content');
          });
      });
      // Responsive menu.
      $(function () {
        // first level menu collapse.
        const firstLevel_li = $('.region-header .navbar-nav > li.dropdown');
        firstLevel_li.once('first-dropdown')
          .on('click', function (e) {
            firstLevel_li.not(this).removeClass('active');
            $(this).toggleClass('active');
            e.stopPropagation();
          });
        // second level menu collapse.
        const secondLevel_li = $('.region-header .navbar-nav > li.dropdown > ul > li');
        secondLevel_li.once('second-dropdown')
          .on('click', function (e) {
            secondLevel_li.not(this).removeClass('active');
            $(this).toggleClass('active');
            e.stopPropagation();
          });
        // prevent dropdown on anchor click.
        const menu_a = $('.region-header .navbar-nav a');
        menu_a.once('menu-link')
          .on('click', function (e) {
            e.stopPropagation();
          });
      });
      // Set the selected year for dropdown.
      var seletedLabel = $('.selected-year-display .year-label');
      var checkedValueLabel = $('.view-filters.select-view .form-radios .form-radio:checked').siblings('label').text();
      seletedLabel.text(checkedValueLabel);
      // Responsive select view of radio button.
      function menuGroup() {
        $('body').click(function () {
          if ($('.menu-trigger').hasClass('active')) {
            $('.menu-trigger').removeClass('active');
          }
          if ($('.menu-dropdown').hasClass('open')) {
            $('.menu-dropdown').removeClass('open');
          }
        });
        // Open menu
        $('.menu-trigger').once('trigger').click(function (event) {
          event.preventDefault();
          event.stopPropagation();
          $(this).toggleClass('active');
          $(this).next('.menu-dropdown').toggleClass('open');
        });
      }
      menuGroup();
      // Set active menu
      var $navLinks = $('.navbar-nav li a');
      function setActive() {
        var current = location.pathname;
        $navLinks.each(function () {
          var $this = $(this);
          // if the current path is like this link, make it active
          if ($this.attr('href').indexOf(current) !== -1) {
            $this.addClass('active');
          }
          else if ($this.attr('href') === '/') {
            $('.navbar-nav li:first-child').addClass('active');
          }
        });
      }
      $navLinks.removeClass('active');
      $navLinks.once('first-dropdown').on('click', function (e) {
        setActive();
      });
      // Set window width.
      var windowWidth = $(window).width();
      $('.market-type  .market-container').css({width: windowWidth + 'px'});

      /* end */
    }
  };
})(jQuery, Drupal);
