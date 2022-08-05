(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.regionUtilityArea = {
    attach: function (context, settings) {
      var behavior_object = this;
      var selector_header_search_button = '.region-utility-area .block--header-search .block__content-toggle';
      var lastScrollTop = 0;

      // Open search panel.
      $(selector_header_search_button).once('header').on({
        click: function () {
          var $this = $(this);
          // Either expand or collapse the search panel.
          behavior_object.togglePanel($this);
        }
      });

      // If search expanded and click outside, close
      $(document).once('header').on('click', function (e) {
        // Update code for search.
        // if ($(selector_header_menu_items_with_children).hasClass('menu-item--expanded')) {
        //   if (!(($(e.target).closest(selector_header_menu_items_with_children).length > 0))) {
        //     behavior_object.toggleMenuPanel($(selector_header_menu_items_with_children).find('.menu-item__button').first());
        //   }
        // }
      });

      // On scroll down, header and expanded navigation disappears, scroll up re-appears, logo area resizes
      window.addEventListener('scroll', function () {
        var $utilityArea = $('.region-utility-area');
        var scrollTopVal = window.pageYOffset || document.documentElement.scrollTop;

        $utilityArea.addClass('region-utility-area--ease-in-out');

        if (scrollTopVal <= 10 || scrollTopVal < lastScrollTop) {
          $utilityArea.removeClass('region-utility-area--hide');
        }
        else {
          $utilityArea.addClass('region-utility-area--hide');
        }
        lastScrollTop = scrollTopVal <= 0 ? 0 : scrollTopVal;
      });
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
    }
  };

})(jQuery, Drupal);
