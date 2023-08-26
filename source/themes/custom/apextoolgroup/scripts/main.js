(function ($, Drupal, once) {
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
        $(once('hamburgerMenu', hamburgerMenuIcon, context)).on('click', function () {
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
        $(once('first-dropdown', firstLevel_li, context)).on('click', function (e) {
          firstLevel_li.not(this).removeClass('active');
          $(this).toggleClass('active');
          e.stopPropagation();
        });
        // second level menu collapse.
        const secondLevel_li = $('.region-header .navbar-nav > li.dropdown > ul > li');
        $(once('second-dropdown', secondLevel_li, context)).on('click', function (e) {
          secondLevel_li.not(this).removeClass('active');
          $(this).toggleClass('active');
          e.stopPropagation();
        });
        // prevent dropdown on anchor click.
        const menu_a = $('.region-header .navbar-nav a');
        $(once('menu-link', menu_a, context)).on('click', function (e) {
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
        $(once('trigger', '.menu-trigger', context)).click(function (event) {
          event.preventDefault();
          event.stopPropagation();
          $(this).toggleClass('active');
          $(this).next('.menu-dropdown').toggleClass('open');
        });
      }
      menuGroup();
      // Set active menu
      var $navLinks = $('.navbar-nav li a');
      var current = location.pathname;
      $navLinks.each(function () {
        var $this = $(this);
        // if the current path is like this link, make it active
        if ($this.attr('href').indexOf(current) !== -1) {
          $this.addClass('active');
        }
      });
      $navLinks.removeClass('active');

      // Set window width.
      var windowWidth = $(window).width();
      $('.market-type  .market-container').css({width: windowWidth + 'px'});

      // Clear search.
      var $searchPageClose = $('.search-page-form .close');
      $(once('clear-data', $searchPageClose, context)).on('click', function (e) {
        $('.search-page-form input').val('');
      });

      /* end */
    }
  };
})(jQuery, Drupal, once);
