(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentNewProducts = {
    attach: function (context, settings) {
      $('.view-new-products').once().each(function () {
        // Scroll down to the products when loading the category page.
        $('.view-new-products').attr('style', 'scroll-margin: 50px !important;');
        $('.view-new-products')[0].scrollIntoView();
      });

      $('.view-new-products:not(.view-new-products--js-initialized)').once('new-product-filter').each(function (index) {
        if (window.innerWidth <= 768) {
          var $mobileMenuIcon = $('.filter-icon');
          var $mobileCloseIcon;
          var $mobileNewProductFilters = $('.view-filters');
          var $viewHeader = $('.view-header');

          $mobileNewProductFilters.append('<div class="mobile-filter-header"><div class="mobile-filter-header-inner"><span>Filter</span><span class="mobile-close-icon"></span></div></div>');
          $mobileCloseIcon = $('.mobile-close-icon');
          $viewHeader.after('<div class="chip-container"><ul class="chips" role="list"></ul></div>');

          $mobileMenuIcon.click(function () {
            $mobileNewProductFilters.addClass('mobile-show');
          });

          $mobileCloseIcon.click(function () {
            $mobileNewProductFilters.removeClass('mobile-show');
          });
        }

        // Logic for the mobile filtering chips
        var $chipList = $('.chips');
        var cleanedArray = [];
        var checkedFilters = $('input[type=checkbox]:checked');

        // Loop through all checked filters
        for (var indx = 0; indx < checkedFilters.length; indx++) {
          if (checkedFilters[indx].id.includes('edit-category')) {
            cleanedArray[indx] = [];
            cleanedArray[indx][1] = checkedFilters[indx].id;
            cleanedArray[indx][2] = 'category';

            var boxLabel = $(`[for="${cleanedArray[indx][1]}"]`);
            cleanedArray[indx][0] = 'Category : ' + boxLabel.text();
          }
          else {
            cleanedArray[indx] = checkedFilters[indx].value;
            cleanedArray[indx] = cleanedArray[indx].split('(');
            cleanedArray[indx][0] = cleanedArray[indx][0].trim();
            cleanedArray[indx][1] = checkedFilters[indx].id;
            cleanedArray[indx][2] = 'attribute';
          }
        }

        // Build chips for checkboxes
        for (var i = 0; i < cleanedArray.length; i++) {
          var id = cleanedArray[i][1];

          if (cleanedArray[i].length > 0) {
            $chipList.append('<li class="chip">' + cleanedArray[i][0] + '<span  data-boxinfo="' + id + '_' + cleanedArray[i][2] + '"  class="chip-close-icon" id="chip-' + id + '"></span></li>');

            jQuery('#chip-' + id + '').click(function () {
              var boxInfo = this.dataset.boxinfo.split('_');
              var checkedBox = $('#' + boxInfo[0]);
              checkedBox[0].checked = false;
              var $newProductView = $('#views-exposed-form-new-products-default, #views-exposed-form-new-products-new-products-block');
              $newProductView.find('input[type=submit]').click();
            });
          }
        }
      });
    }
  };

  /**
   * This is the Accordion functionality.
   *
   * Business Rules:
   *   - The first item will open on page load unless the user has selected a different item.
   *   - If any item is selected, its filter group will be expanded.
   *
   * @type {{attach: Drupal.behaviors.productCategoryFilterTabs.attach}}
   */
  Drupal.behaviors.componentNewProductsFilterTabs = {
    attach: function (context, settings) {
      $('.view-new-products .views-exposed-form').once().each(function (index) {
        // Initialize variables.
        var $widget = $(this);
        var $accordions = $widget.find('fieldset');
        var $accordionHeaders = $accordions.find('legend');

        // Add static roles to elements.
        $widget.attr('role', 'tablist');
        let isSomethingOpen = false;

        // The first pass will open all accordions with selected content and
        // will inform whether we need to open the first accordion.
        $accordions.each(function (accordionIndex) {
          var $accordion = $(this);
          var $accordionContent = $accordion.children('.fieldset-wrapper');

          $accordionContent.find('input').each(function () {
            let checked = $(this).prop('checked');
            let type = $(this).prop('type');

            if (type !== 'radio' && checked) {
              $accordion.addClass('component-accordion-item--open');
              isSomethingOpen = true;
              return false;
            }
          });
        });

        // Attach each accordion item header to its content and hide content
        // that should be hidden.
        $accordions.each(function (accordionIndex) {
          var $accordion = $(this);
          var $accordionHeader = $accordion.children('legend');
          var $accordionContent = $accordion.children('.fieldset-wrapper');

          // Generate the accordion tab (header) and panel (content) IDs.
          var accordionId = $accordion.attr('data-drupal-selector');
          var headerId = 'product-category-filter-item-' + accordionId + '__header';
          var panelId = 'product-category-filter-item-' + accordionId + '__panel';

          // If no accordions are open, open the first accordion.
          if (isSomethingOpen === false && accordionIndex === 0) {
            $accordion.addClass('component-accordion-item--open');
          }

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
            var open = $accordion.hasClass('product-category-filter-item---open');

            $accordion.toggleClass('product-category-filter-item---open', (!open));
            $accordion.toggleClass('component-accordion-item--open', (!open));
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
