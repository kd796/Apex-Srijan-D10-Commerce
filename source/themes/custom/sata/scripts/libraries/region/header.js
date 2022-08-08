(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.regionHeader = {
    attach: function (context, settings) {
      var behavior_object = this;
      var selector_header_menu_button = '.block--header-menu-main .block__menu-toggle';
      var selector_header_menu_items_with_children = '.block--header-menu-main .menu-item--has-children';
      var selector_header_search_button = '.region-header .block--header-search .block__content-toggle';
      var selector_header_country_switch_button = '.block--country-switch .block__content-toggle';
      var lastScrollTop = 0;

      // Mobile selectors
      var selector_header_mobile_search = '.search-toggle';
      var selector_mobile_search_box = '.mobile-search input.search-box';
      var selector_mobile_search_icon = '.mobile-search .search-icon';
      var selector_mobile_search_reset = '.mobile-search .search-reset';

      // Build the search form.
      var search_text = Drupal.t('Search');
      var submit_text = Drupal.t('Submit');
      var mobile_search_form = '' +
        '<div class="mobile-search">' +
        ' <form action="/search" method="GET">' +
        '   <div class="search-input-set">' +
        '     <input class="search-box" name="search" type="text" placeholder="' + search_text + '">' +
        '     <span class="search-icon"></span>' +
        '     <span class="search-reset"></span>' +
        '   </div>' +
        '   <button class="btn">' + submit_text + '</button>' +
        ' </form>' +
        '</div>';

      // Add the search form to mobile.
      $('.block__menu').find(selector_header_mobile_search).parent().append(mobile_search_form);

      // Hide Search Toggle Text.
      $('.block__menu').find(selector_header_mobile_search).remove();

      // Mobile controls.
      $('.block__menu').find(selector_mobile_search_box).keyup(function () {
        if ($(this).val().length > 0) {
          $('.block__menu').find(selector_mobile_search_icon).hide();
          $('.block__menu').find(selector_mobile_search_reset).show();
        }
      });

      $('.block__menu').find(selector_mobile_search_reset).click(function () {
        $('.block__menu').find(selector_mobile_search_box).val('');
        $('.block__menu').find(selector_mobile_search_icon).show();
        $('.block__menu').find(selector_mobile_search_reset).hide();
      });

      // Open search panel.
      if ($(selector_header_search_button)) {
        $(selector_header_search_button).once('header').on({
          click: function () {
            var $this = $(this);

            // Either expand or collapse the search panel.
            behavior_object.togglePanel($this);
          }
        });
      }

      if ($(selector_header_country_switch_button).length > 0) {
        // Hide Country Toggle Text.
        $('.block__menu').find('.country-toggle').empty();

        // Open country switch panel.
        $(selector_header_country_switch_button).once('header').on({
          click: function () {
            var $this = $(this);

            // Either expand or collapse the search panel.
            behavior_object.togglePanel($this);
          }
        });
      }

      // Open mobile menu.
      $(selector_header_menu_button).once('header').on({
        click: function () {
          var $this = $(this);
          var $menu = $this.parent().find('.menu--depth-0');

          // Either expand or collapse the menu.
          behavior_object.togglePanel($this);

          // Close all sub menus.
          $menu.removeClass('menu--menu-item-expanded');
          $menu.find('.menu-item').removeClass('menu-item--expanded');
          $menu.find('.menu-item__button').attr('aria-expanded', 'false');
          $('.region-header').removeClass('region--menu-item-expanded');

        }
      });

      // Trigger button panel to open based on hover (done here instead of css
      // :hover to reduce amount of code and make issues easier to troubleshoot.
      $(selector_header_menu_items_with_children).once('header').on({
        click: function () {
          var $menu_item = $(this);
          var $button = $menu_item.find('.menu-item__button').first();
          // Only toggle panel for desktop and if not already expanded.
          behavior_object.toggleMenuPanel($button);
        }
      });

      // Make view-port-related adjustments based on if header is expanded.
      $(window).once('header').on('resize', function () {
        var $menu_item = $('.region-header .menu-item--depth-0.menu-item--expanded');
        behavior_object.updateHeaderPlaceholder($menu_item);
      });

      // If desktop menu expanded and click outside, close
      $(document).once('header').on('click', function (e) {
        if ($(selector_header_menu_items_with_children).hasClass('menu-item--expanded')) {
          if (!(($(e.target).closest(selector_header_menu_items_with_children).length > 0))) {
            behavior_object.toggleMenuPanel($(selector_header_menu_items_with_children).find('.menu-item__button').first());
          }
        }
      });

      // Sets large logo with tall performance device
      $('header .block--header-branding').addClass('block--header-branding-large');
      $('header .region-header__content').addClass('region-header__content-large');

      // On scroll down, header and expanded navigation disappears, scroll up re-appears, logo area resizes
      window.addEventListener('scroll', function () {
        var $header = $('header');
        var scrollTopVal = window.pageYOffset || document.documentElement.scrollTop;
        var $menu_item = $('.region-header .menu-item--depth-0');
        var $button = $menu_item.find('.menu-item__button').first();

        $header.addClass('region-header--ease-in-out');

        if (scrollTopVal <= 10 || scrollTopVal < lastScrollTop) {
          $header.removeClass('region-header--hide');

          if (scrollTopVal === 0) {
            $('header .block--header-branding').addClass('block--header-branding-large');
            $('header .region-header__content').addClass('region-header__content-large');
          }
        }
        else {
          $header.addClass('region-header--hide');
          $('header .block--header-branding').removeClass('block--header-branding-large');
          $('header .region-header__content').removeClass('region-header__content-large');

          if ($button.attr('aria-expanded') === 'true') {
            behavior_object.toggleMenuPanel($button);
          }
        }
        lastScrollTop = scrollTopVal <= 0 ? 0 : scrollTopVal;
      });
    },
    isDesktop: function () {
      return ($('body').width() >= 1280);
    },
    isMobile: function () {
      return ($('body').width() < 1280);
    },
    toggleMenuPanel: function ($button, to_expand) {
      to_expand = (typeof to_expand === 'boolean') ? to_expand : ($button.attr('aria-expanded') !== 'true');
      var top_level_menu_item = $button.parent().hasClass('menu-item--depth-0');
      var $menu = $button.parents('.menu--depth-0');
      var $menu_item = $button.parent();
      var $region_header = $button.parents('.region-header');

      // Collapse sibling and sibling children menus.
      $menu_item.siblings('.menu-item--has-children').removeClass('menu-item--expanded');
      $menu_item.siblings('.menu-item--has-children').find('.menu-item__button').attr('aria-expanded', 'false');

      // Either expand or collapse the component's panel.
      $button.attr('aria-expanded', to_expand ? 'true' : 'false');
      $menu_item.toggleClass('menu-item--expanded', to_expand);

      if (top_level_menu_item) {
        $menu.toggleClass('menu--menu-item-expanded', to_expand);

        if (this.isDesktop()) {
          $region_header.toggleClass('region--menu-item-expanded', to_expand);
          this.updateHeaderPlaceholder($menu_item);
        }
      }

      // Revalidate Blazy.
      if (typeof Drupal.blazy !== 'undefined') {
        Drupal.blazy.init.revalidate();
      }

      return to_expand;
    },
    togglePanel: function ($button, to_expand) {
      to_expand = (typeof to_expand === 'boolean') ? to_expand : ($button.attr('aria-expanded') !== 'true');
      var $body = $('body');
      var current_panel_id = $body.attr('data-panel-open');
      var panel_id = $button.attr('aria-controls');

      // Close panel if one is currently open.
      if (current_panel_id && current_panel_id !== panel_id) {
        var $current_panel_button = $('button[aria-controls="' + current_panel_id + '"]');
        $current_panel_button.attr('aria-expanded', 'false');
        $body.attr('data-panel-open', null);
      }

      // Either expand or collapse the panel.
      $button.attr('aria-expanded', to_expand ? 'true' : 'false');
      $body.attr('data-panel-open', (to_expand) ? panel_id : null);
      $body.toggleClass('jsa-body-lock', to_expand);

      // Revalidate Blazy.
      if (typeof Drupal.blazy !== 'undefined') {
        Drupal.blazy.init.revalidate();
      }

      // @todo event to track for if focus leaves element. destroy event when triggered
    },
    updateHeaderPlaceholder: function ($menu_item) {
      var height = 0;
      if (this.isDesktop() && $menu_item.length > 0 && $('.region-header').hasClass('region--menu-item-expanded')) {
        if ($menu_item.hasClass('menu-item--toggle-block')) {
          var block_id = $menu_item.attr('data-toggle-block');
          height = $('#' + block_id).outerHeight();
        }
        else {
          height = $menu_item.find('.menu-item__panel--depth-0').outerHeight();
        }
      }

      $('.region-header__inner').css('padding-bottom', height + 'px');
    }
  };

})(jQuery, Drupal);
