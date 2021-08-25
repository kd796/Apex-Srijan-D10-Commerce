(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentTabs = {
    attach: function (context, settings) {
      $('.component-tabs:not(.component-tabs--js-initialized)').each(function (index) {
        // Initialize variables.
        var $tabsWidget = $(this);
        var $tablist = $tabsWidget.children('.component-tabs__nav');
        var $tabs = $tablist.find('.component-tabs__nav-item');
        var $links = $tablist.find('a');
        var $panelContainer = $tabsWidget.children('.component-tabs__content');
        var $panels = $panelContainer.children('.component-tabs-tab');

        // Mark that the tabs component has been initialized.
        $tabsWidget.addClass('component-tabs--js-initialized');

        // Add static roles to elements.
        $tablist.attr('role', 'tablist');
        $tabs.attr('role', 'tab');
        $panels.attr('role', 'tabpanel');
        $links.attr('role', 'presentation');

        // Default to first item as selected.
        $tabs.attr('aria-selected', 'false')
          .first()
          .attr('tabindex', '0')
          .attr('aria-selected', 'true');
        $panels.prop('hidden', true)
          .first()
          .prop('hidden', false);

        // Label panel and mark that each is controlled by their respective tab.
        $tabs.each(function (tabIndex) {
          var $tab = $(this);
          var $tabLink = $tab.find('a');
          var panelId = $tabLink.attr('href').substring($tabLink.attr('href').indexOf('#') + 1);
          var tabId = panelId + '-tab';

          // Remove link from href.
          $tabLink.removeAttr('href');

          // Link tab to panel.
          $tab.attr('id', tabId)
            .attr('aria-controls', panelId);

          // Link panel to tab.
          $panels.eq(tabIndex)
            .attr('id', panelId)
            .attr('aria-labelledby', tabId);
        });

        // Initialize the roving tabindex.
        $tablist.rovingTabindex('[role=tab]');

        // Track when tab is changed.
        $tablist.on('rovingTabindexChange', '[role=tab]', function (e, data) {
          var $tab = $(this);

          $tabs.attr('aria-selected', 'false');
          $tab.attr('aria-selected', 'true');

          $panels.prop('hidden', true);
          $panels.filter('#' + $tab.attr('aria-controls')).prop('hidden', false);

          Drupal.blazy.init.revalidate();
        });

        // Slide clicked on tab to active position
        if ($('body').width() < 768) {
          $('.component-tabs__nav .component-tabs__nav-item').click(function () {
            const tabUl = $('.component-tabs__nav');
            const tabPosition = $(this).position();
            const scrollValue = $(tabUl).scrollLeft();

            $(tabUl).animate({
              scrollLeft: scrollValue + tabPosition.left - 30
            }, 200, 'linear');
          });
        }
      });

    }
  };

})(jQuery, Drupal);
