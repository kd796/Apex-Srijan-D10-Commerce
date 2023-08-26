(function ($, Drupal, once) {
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
        $(once('hamburgerMenu', hamburgerMenuIcon, context)).on('click', function () {
          hamburgerMenu.toggle('d-none');
        });
      });
      //  Responsive menu.
      $(function () {
        // first level menu collapse.
        const firstLevel_li = $('.navbar-we-mega-menu.navbar li.we-mega-menu-li');
        firstLevel_li.removeClass('active active-trail open');
        $(once('first-dropdown', firstLevel_li, context)).on('click', function (e) {
          $(this).toggleClass('active active-trail open');
          e.stopPropagation();
        });
        // second level menu collapse.
        const secondLevel_li = $('.navbar-we-mega-menu.navbar ul>li>ul');
        secondLevel_li.addClass('second-ul');
        secondLevel_li.prev().addClass('second-dropdown');
        $(once('second-dropdown', '.second-dropdown', context)).on('click', function (e) {
          $(this).toggleClass('open');
          $(this).next('ul').toggleClass('open-second-level');
          e.stopPropagation();
        });
        // third level menu collapse.
        const thirdLevel_li = $('.navbar-we-mega-menu.navbar ul>li>ul li>ul');
        thirdLevel_li.addClass('third-ul').removeClass('second-ul');
        thirdLevel_li.prev().addClass('third-dropdown').removeClass('second-dropdown');
        $(once('third-dropdown', '.third-dropdown', context)).on('click', function (e) {
          if ($(this).hasClass('third-ul')) {
            $(this).toggleClass('open');
          }
          $(this).next('ul').toggleClass('open-third-level');
          e.stopPropagation();
        });
      });
      // Adding empty view class for product listing pages.
      let view = $('.view');
      if (view.find('.view-empty').length !== 0) {
        view.addClass('no-result');
      }
      // tabs
      $(function () {
        $('.tabs-nav a').click(function () {
          // Check for active
          $('.tabs-nav li').removeClass('active');
          $(this).parent().addClass('active');
          // Display active tab
          let currentTab = $(this).attr('href');
          $('.tabs-content .tabs-content__inner').removeClass('active');
          $(currentTab).addClass('active');
          return false;
        });
      });
      // Remove empty div in where to buy page.
      var $noMatch = $('.where-to-buy .view-empty').text().replace(/^\s+|\s+$/g, '').length;
      if ($noMatch === 0) {
        $('.where-to-buy .view-empty').remove();
      }

      // accordions
      $(once('accordion', '.accordion-title', context)).on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $(this)
            .siblings('.accordion-content')
            .slideUp(200);
        }
        else {
          $('.accordion-title').removeClass('active');
          $(this).addClass('active');
          $('.accordion-content').slideUp(200);
          $(this)
            .siblings('.accordion-content')
            .slideDown(200);
        }
      });

      // News Accordions
      $(once('new-accordion', '.news-accordion-title', context)).on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $(this)
            .siblings('.news-accordion-content')
            .removeClass('open')
            .slideUp(200);
        }
        else {
          $('.news-accordion-title').removeClass('active');
          $(this).addClass('active');
          $('.news-accordion-content').removeClass('open').slideUp(200);
          $(this)
            .siblings('.news-accordion-content')
            .addClass('open')
            .slideDown(200);
        }
      });

      /* end */
    }
  };
})(jQuery, Drupal, once);
