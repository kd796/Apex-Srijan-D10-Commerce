(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.productCategoryFilters = {
    filtering: function ($item, $filterType) {
      let newVal = '';
      let newArray = [];
      if (typeof drupalSettings.selectedCategories == 'undefined' || drupalSettings.selectedCategories == 'All') {
        drupalSettings.selectedCategories = [];
      }

      if (typeof drupalSettings.selectedAttributes == 'undefined') {
        drupalSettings.selectedAttributes = [];
      }

      if ($item.prop('checked')) {
        let $filterVal = $item.val();

        // Add Items
        switch ($filterType) {
          case 'category':
            drupalSettings.selectedCategories.push($filterVal);
            break;
          case 'attribute':
            newVal = drupalSettings.selectedAttributes + ', ' + $item.val();
            drupalSettings.selectedAttributes = newVal;
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
      let $productCategoryView = $('.view-product-category');

      // Load the product attributes filter that is hidden on the page.
      let attributeTextField = $productCategoryView.find('.form-item-field-product-specifications-target-id').find('.form-text');

      // Now load the category filter that is hidden on the page.
      let classificationSelect = $productCategoryView.find('.form-item-field-product-classifications-target-id').find('.form-select');

      // Now set all the values for categories and attributes.
      classificationSelect.val(drupalSettings.selectedCategories);
      attributeTextField.val(drupalSettings.selectedAttributes);

      // Finally trigger the hidden submit button.
      $productCategoryView.find('input[type=submit]').click();
    },
    attach: function (context, settings) {
      drupalSettings.selectedAttributes = $('#edit-field-product-specifications-target-id').val();

      let catArray = [];
      let specArray = drupalSettings.selectedAttributes.split(', ');

      $('#edit-field-product-classifications-target-id option').once('setCatFilters').each(function (index) {
        if ($(this).is(':selected')) {
          $('#edit-category-filter-' + $(this).val()).prop('checked', true);
          catArray.push($(this).val());
          drupalSettings.selectedCategories = catArray;
        }
      });

      $.each(specArray, function (index, value) {
        $('.node--type-product-category__attribute-filter').find('input[value="' + value + '"]').prop('checked', true);
      });

      $('.campbell-product-category-filters:not(.campbell-product-category-filters--js-initialized)').once('product-category-filters').each(function (index) {
        let $categoryFilter = $('.node--type-product-category__category-filter');
        let $attributeFilter = $('.node--type-product-category__attribute-filter');

        // Track that this component has been initialized.
        $(this).addClass('campbell-product-category-filters--js-initialized');

        $categoryFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'category');
          e.stopImmediatePropagation();
        });

        $attributeFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'attribute');
          e.stopImmediatePropagation();
        });
      });
    }
  };

  if (window.innerWidth <= 768) {
    let $mobileMenuIcon = $('.filter-icon');
    let $mobileCloseIcon;
    let $mobileCategoryFilters = $('.campbell-product-category-filters');

    $mobileCategoryFilters.append('<div class="mobile-filter-header"><div class="mobile-filter-header-inner"><span>Filter</span><span class="mobile-close-icon"></span></div></div>');
    $mobileCloseIcon = $('.mobile-close-icon');

    $mobileMenuIcon.click(function () {
      $mobileCategoryFilters.addClass('mobile-show');
    });

    $mobileCloseIcon.click(function () {
      $mobileCategoryFilters.removeClass('mobile-show');
    });
  }

})(jQuery, Drupal);
