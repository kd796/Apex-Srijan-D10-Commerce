(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.regionUtilityArea = {
    attach: function (context, settings) {
      var behavior_object = this;
      var selector_search_button = '.region-utility-area .block--header-search .block__content-toggle';
      var selector_search_content = '.region-utility-area .block--header-search .block-header-search__content';
      var lastScrollTop = 0;

      // Open search panel.
      $(once('utilityarea', selector_search_button, context)).on({
        click: function () {
          var $this = $(this);
          // Either expand or collapse the search panel.
          behavior_object.togglePanel($this);
        }
      });

      // If search expanded and click outside, close.
      $(document).once('utilityarea').on('click', function (e) {
        if ($(selector_search_button).attr('aria-expanded') === 'true') {
          if (!(($(e.target).closest('.block--header-search').length > 0))) {
            behavior_object.togglePanel($(selector_search_content));
          }
        }
      });

      // On scroll down, utility area disappears, scroll up re-appears
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

})(jQuery, Drupal, once);
