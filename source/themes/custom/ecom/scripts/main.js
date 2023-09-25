(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.campbell = {
    attach: function (context, settings) {
      // Prevent the opening of the submenu on click of parent link.
      const $link = $('.mobile-header .we-mega-menu-li.dropdown-menu a');
      $link.on('click', function (e) {
        e.stopPropagation();
      });
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
          hamburgerMenuIcon.toggleClass('close-nav');
        });
      });
      // Responsive menu.
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
      // Tabs.
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
      // Accordions.
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
      // News Accordions.
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
      // To align the Add address field if the address is empty.
      if ($('.address-book__container .address-book__empty-text').length > 0) {
        $('.address-book__container .address-book__add-link').css('position', 'unset');
      }
      // Add Class for checkbox on change.
      $(once('addIconOnChange', '.form-checkbox', context)).on('change', function (e) {
        if ($(this).is(':checked')) {
          $(this).addClass('checked-on');
        }
        else {
          $(this).addClass('checked-off');
        }
      });
      // County field append.
      function checkoutCountyField() {
        const appendElement = $('.commerce-checkout-flow').find('div[data-drupal-selector$="shipping-profile-address-0-address-container4"],[data-drupal-selector$="billing-information-address-0-address-container4"]');
        const shippingCounty = $('.commerce-checkout-flow .checkout-pane-shipping-information [data-drupal-selector$="shipping-profile-address-0-address-container4"]');
        const shippingCountyField = $('.commerce-checkout-flow .checkout-pane-shipping-information .field--name-field-county');
        const paymentCountyField = $('.commerce-checkout-flow .checkout-pane-payment-information .field--name-field-county');
        const paymentCounty = $('.commerce-checkout-flow .checkout-pane-payment-information [data-drupal-selector$="billing-information-address-0-address-container4"]');
        if (appendElement.length < 1) {
          return false;
        }
        if ($(shippingCounty).find('.field--name-field-county').length < 1) {
          $(shippingCountyField).appendTo($(shippingCounty));
        }
        if ($(paymentCounty).find('.field--name-field-county').length < 1) {
          $(paymentCountyField).appendTo($(paymentCounty));
        }
      }
      checkoutCountyField();

      /* end */
    }
  };
})(jQuery, Drupal, once);
