(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.productListingFilters = {
    filtering: function ($item, $filterType) {
      if (typeof drupalSettings.selectedCategories == 'undefined') {
        drupalSettings.selectedCategories = [];
      }
      if (typeof drupalSettings.selectedAttributes == 'undefined') {
        drupalSettings.selectedAttributes = [];
      }
      if ($item.prop('checked')) {
        var $filterVal = $item.val();
        // Add Items
        switch ($filterType) {
          case 'category':
            drupalSettings.selectedCategories.push($filterVal);
            break;
          case 'attribute':
            drupalSettings.selectedAttributes.push($item.val());
            break;
        }
      }
      else {
        // Remove Items
        switch ($filterType) {
          case 'category':
            drupalSettings.selectedCategories = drupalSettings.selectedCategories.filter(function (item) {
              return item !== $item.val();
            });
            break;
          case 'attribute':
            drupalSettings.selectedAttributes = drupalSettings.selectedAttributes.filter(function (item) {
              return item !== $item.val();
            });
            break;
        }
      }

      var $productListingView = $('.view-product-listing');
      var attributeSelect = $productListingView.find('.form-item-field-product-specifications-target-id').find('.form-select');
      attributeSelect.val(drupalSettings.selectedAttributes);
      var classificationSelect = $productListingView.find('.form-item-field-product-classifications-target-id').find('.form-select');
      classificationSelect.val(drupalSettings.selectedCategories);
      attributeSelect.val(drupalSettings.selectedAttributes);
      $productListingView.find('input[type=submit]').click();
    },
    attach: function (context, settings) {
      $('.gearwrench-product-listing-filters:not(.gearwrench-product-listing-filters--js-initialized)').once('product-listing-filters').each(function (index) {
        var $categoryFilter = $('.node--type-product-listing__category-filter');
        var $attributeFilter = $('.node--type-product-listing__attribute-filter');
        // Track that this component has been initialized.
        $(this).addClass('gearwrench-product-listing-filters--js-initialized');
        $categoryFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productListingFilters.filtering($(this), 'category');
          e.stopImmediatePropagation();
        });
        $attributeFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productListingFilters.filtering($(this), 'attribute');
          e.stopImmediatePropagation();
        });

        $(document).ajaxComplete(function () {
          // eslint-disable-next-line
          PriceSpider.rebind();
        });
      });
    }
  };

  Drupal.behaviors.productListingFilterTabs = {
    attach: function (context, settings) {
      $('.gearwrench-product-listing-filters').once('product-listing-filter-tabs').each(function (index) {
        // Initialize variables.
        var $widget = $(this);
        var $accordions = $widget.find('fieldset');
        var $accordionHeaders = $accordions.find('legend');

        // Mark that the tabs component has been initialized.
        // $widget.addClass('gearwrench-product-listing-filters--js-initialized');

        // Add static roles to elements.
        $widget.attr('role', 'tablist');

        // Attach each accordion item header to its content and hide content
        // that should be hidden.
        $accordions.each(function (accordionIndex) {
          var $accordion = $(this);
          var $accordionHeader = $accordion.children('legend');
          var $accordionContent = $accordion.children('.fieldset-wrapper');

          // Generate the accordion tab (header) and panel (content) IDs.
          var accordionId = $accordion.attr('data-drupal-selector');
          var headerId = 'product-listing-filter-item-' + accordionId + '__header';
          var panelId = 'product-listing-filter-item-' + accordionId + '__panel';

          // Determine whether this accordion needs to be open by default.
          var openByDefault = $accordion.hasClass('component-accordion-item--open');

          // Link tab to panel.
          $accordionHeader
            .attr('aria-controls', panelId)
            .attr('aria-selected', (openByDefault) ? 'true' : 'false')
            .attr('id', headerId)
            .attr('role', 'tab')
            .attr('tabindex', (accordionIndex === 0) ? '0' : '-1');

          // Link panel to tab.
          $accordionContent
            .attr('aria-hidden', (!openByDefault) ? 'true' : 'false')
            .attr('aria-labelledby', headerId)
            .attr('hidden', (!openByDefault))
            .attr('id', panelId)
            .attr('role', 'tabpanel');
        });

        // Initialize the roving tabindex.
        $widget.rovingTabindex('[role=tab]');

        // Track when tab is changed.
        $widget.on('rovingTabindexChange', '[role=tab]', function (e, data) {
          var $accordion = $(this);
          var $accordionHeader = $accordion.children('legend');

          $accordionHeaders.attr('aria-selected', 'false');
          $accordionHeader.attr('aria-selected', 'true');
        });

        // Track when tab needs to be opened or closed.
        $accordionHeaders.on('click keyup', function (e) {
          if (e.type === 'click' || (e.type === 'keyup' && [13, 32].indexOf(e.keyCode) !== -1)) {
            var $accordionHeader = $(this);
            var $accordionContent = $accordionHeader.siblings('.fieldset-wrapper');
            var $accordion = $accordionHeader.parent();
            var open = $accordion.hasClass('product-listing-filter-item---open');

            $accordion.toggleClass('product-listing-filter-item---open', (!open));
            $accordionHeader.attr('aria-selected', (open) ? 'false' : 'true');
            $accordionContent
              .attr('aria-hidden', (open) ? 'true' : 'false')
              .attr('hidden', open);

            Drupal.blazy.init.revalidate();
          }
        });
      });
    }
  };
})(jQuery, Drupal);
