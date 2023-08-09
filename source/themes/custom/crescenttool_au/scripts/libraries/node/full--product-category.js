(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.productCategoryFilters = {
    filtering: function ($item, $filterType) {
      let newVal = '';
      let newArray = [];

      if (typeof drupalSettings.selectedCategories == 'undefined') {
        drupalSettings.selectedCategories = [];
      }

      if (typeof drupalSettings.selectedAttributes == 'undefined') {
        drupalSettings.selectedAttributes = [];
      }

      if (typeof drupalSettings.selectedSetFilterValue == 'undefined') {
        drupalSettings.selectedSetFilterValue = 'All';
      }

      if ($item.prop('checked')) {
        var $filterVal = $item.val();

        // Add Items
        switch ($filterType) {
          case 'category':
            drupalSettings.selectedCategories.push($filterVal);
            break;
          case 'attribute':
            newVal = drupalSettings.selectedAttributes + ', ' + $item.val();
            drupalSettings.selectedAttributes = newVal;
            break;
          case 'set':
            drupalSettings.selectedSetFilterValue = $filterVal;
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
            newVal = drupalSettings.selectedAttributes.split(', ');
            newArray = newVal.filter(function (item) {
              return item !== $item.val();
            });
            drupalSettings.selectedAttributes = newArray.join(', ');
            break;
        }
      }

      // Load the Product Category view DOM element from the page.
      var $productCategoryView = $('.view-product-category');

      // Load the product attributes filter that is hidden on the page.
      var attributeTextField = $productCategoryView.find('.form-item-field-product-specifications-target-id').find('.form-text');

      // Now load the category filter that is hidden on the page.
      var classificationSelect = $productCategoryView.find('.form-item-field-product-classifications-target-id').find('.form-select');

      // Now load the Set? filter that is hidden on the page.
      var setFilterSelect = $productCategoryView.find('.form-item-field-set-value').find('.form-select');

      // Now set all the values for categories and attributes.
      classificationSelect.val(drupalSettings.selectedCategories);
      attributeTextField.val(drupalSettings.selectedAttributes);
      setFilterSelect.val(drupalSettings.selectedSetFilterValue);

      // Finally trigger the hidden submit button.
      $productCategoryView.find('input[type=submit]').click();
    },
    attach: function (context, settings) {
      drupalSettings.selectedAttributes = $('#edit-field-product-specifications-target-id').val();

      let catArray = [];
      let setArray = [];
      let specArray = drupalSettings.selectedAttributes.split(', ');

      $(once('setCatFilters', '#edit-field-product-classifications-target-id option', context)).each(function (index) {
        if ($(this).is(':selected')) {
          $('#edit-category-filter-' + $(this).val()).prop('checked', true);
          catArray.push($(this).val());
          drupalSettings.selectedCategories = catArray;
        }
      });

      $(once('setSetFilters', '#edit-field-set-value option', context)).each(function (index) {
        if ($(this).is(':selected')) {
          $('#edit-set-filter-' + $(this).val()).prop('checked', true);
          setArray.push($(this).val());
          drupalSettings.selectedSetFilterValue = setArray;
        }
      });

      $.each(specArray, function (index, value) {
        $('.node--type-product-category__attribute-filter').find('input[value="' + value + '"]').prop('checked', true);
      });

      $(once('product-category-filters', '.crescenttool-product-category-filters:not(.crescenttool-product-category-filters--js-initialized)', context)).each(function (index) {
        var $categoryFilter = $('.node--type-product-category__category-filter');
        var $attributeFilter = $('.node--type-product-category__attribute-filter');
        var $setFilter = $('.node--type-product-category__set-filter');

        // Track that this component has been initialized.
        $(this).addClass('crescenttool-product-category-filters--js-initialized');

        $categoryFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'category');
          e.stopImmediatePropagation();
        });

        $attributeFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'attribute');
          e.stopImmediatePropagation();
        });

        $setFilter.bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'set');
          e.stopImmediatePropagation();
        });

        $(document).ajaxComplete(function () {
          // eslint-disable-next-line
          PriceSpider.rebind();
        });
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
  Drupal.behaviors.productCategoryFilterTabs = {
    attach: function (context, settings) {
      $(once('product-category-filter-tabs', '.crescenttool-product-category-filters', context)).each(function (index) {
        // Scroll down to the products when setting a filter option.
        if (window.location.search.length > 0) {
          $('.product-category-view-section')[0].scrollIntoView();
        }

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
            $accordion.addClass('product-category-filter-item---open');
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

        // Logic for mobile filtering menu.
        if (window.innerWidth < 768) {
          var $mobileCategoryFilters = $('.crescenttool-product-category-filters');
          var $viewHeader = $('.view-header');

          $viewHeader.append('<div class="mobile-view-filter-header"><span class="filter-icon"></span></div>');
          $mobileCategoryFilters.append('<div class="mobile-filter-header"><div class="mobile-filter-header-inner"><span>Filter</span><span class="mobile-close-icon"></span></div></div>');
          $viewHeader.after('<div class="chip-container"><ul class="chips" role="list"></ul></div>');

          var $mobileMenuIcon = $('.filter-icon');
          var $mobileCloseIcon = $('.mobile-close-icon');

          $mobileMenuIcon.click(function () {
            $mobileCategoryFilters.addClass('mobile-show');
          });

          $mobileCloseIcon.click(function () {
            $mobileCategoryFilters.removeClass('mobile-show');
          });

          // Logic for the mobile filtering chips
          var $chipList = $('.chips');
          var cleanedArray = [];
          var cleanedRadioArray = [];
          var checkedFilters = $('input[type=checkbox]:checked');
          var checkedRadios = $('input[type=radio]:checked');

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

          // Loop through all checked radio buttons
          for (var idx = 0; idx < checkedRadios.length; idx++) {
            if (checkedRadios[idx].id.includes('edit-set')) {
              cleanedRadioArray[idx] = [];

              if (checkedRadios[idx].value === '1') {
                cleanedRadioArray[idx][1] = 'Set : Yes';
              }
              else if (checkedRadios[idx].value === '0') {
                cleanedRadioArray[idx][1] = 'Set : No';
              }
              else {
                cleanedRadioArray[idx][1] = '';
              }
              cleanedRadioArray[idx][0] = checkedRadios;
            }
          }

          // Build chips for radio buttons
          for (var indexNum = 0; indexNum < cleanedRadioArray.length; indexNum++) {
            if (cleanedRadioArray[indexNum][1].length > 0) {
              $chipList.append('<li class="chip">' + cleanedRadioArray[indexNum][1] + '<span  data-radioinfo="' + checkedRadios[indexNum].id + '_set' + '"class="chip-close-icon" id="chip-' + checkedRadios[indexNum].value + '"></span></li>');

              jQuery('#chip-' + checkedRadios[indexNum].value + '').click(function () {
                // Scope is weird here, have to put the id in the data-attribute and then find the element again instead of just using it. Same on line 302.
                var radioInfo = this.dataset.radioinfo.split('_');
                var checkedRadio = $('#' + radioInfo[0]);

                checkedRadio.val('all');
                Drupal.behaviors.productCategoryFilters.filtering($(checkedRadio), 'set');
              });
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
                Drupal.behaviors.productCategoryFilters.filtering($(checkedBox[0]), boxInfo[1]);
              });
            }
          }
        }
      });
    }
  };

})(jQuery, Drupal, once);
