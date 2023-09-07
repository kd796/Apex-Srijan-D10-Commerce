(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.productCategoryFilters = {
    filtering: function ($item, $filterType) {
      let newVal = '';
      let newArray = [];
      let newBrandVal = '';
      let newbrandArray = [];

      if (typeof drupalSettings.selectedCategories == 'undefined' || drupalSettings.selectedCategories.toString() === 'All') {
        drupalSettings.selectedCategories = [];
      }

      if (typeof drupalSettings.selectedAttributes == 'undefined') {
        drupalSettings.selectedAttributes = [];
      }

      if (typeof drupalSettings.selectedBrand == 'undefined') {
        drupalSettings.selectedBrand = [];
      }

      if (typeof drupalSettings.selectedSetFilterValue == 'undefined') {
        drupalSettings.selectedSetFilterValue = 'All';
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
          case 'brand':
            newBrandVal = drupalSettings.selectedBrand ? (drupalSettings.selectedBrand + ', ' + $item.val()) : $item.val();
            drupalSettings.selectedBrand = newBrandVal;
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
          case 'brand':
            newBrandVal = drupalSettings.selectedBrand.split(', ');
            newbrandArray = newBrandVal.filter(function (item) {
              return item !== $item.val();
            });
            drupalSettings.selectedBrand = newbrandArray.join(', ');
            break;
        }
      }

      // Load the Product Category view DOM element from the page.
      let $productCategoryView = $('.view-product-category');

      // Load the product attributes filter that is hidden on the page.
      let attributeTextField = $productCategoryView.find('.form-item-field-product-specifications-target-id').find('.form-text');

      // Now load the category filter that is hidden on the page.
      let classificationSelect = $productCategoryView.find('.form-item-field-product-classifications-target-id').find('.form-select');

      let brandField = $productCategoryView.find('.form-item-field-brand-target-id').find('.form-text');

      let setFilterSelect = $productCategoryView.find('.form-item-field-set-value').find('.form-select');

      // Now set all the values for categories and attributes.
      classificationSelect.val(drupalSettings.selectedCategories);
      attributeTextField.val(drupalSettings.selectedAttributes);
      brandField.val(drupalSettings.selectedBrand);
      setFilterSelect.val(drupalSettings.selectedSetFilterValue);
      // Finally trigger the hidden submit button.
      $productCategoryView.find('input[type=submit]').click();
    },
    attach: function (context, settings) {
      drupalSettings.selectedAttributes = $('#edit-field-product-specifications-target-id').val();
      drupalSettings.selectedBrand = $('#edit-field-brand-target-id').val();
      let catArray = [];
      let setArray = [];
      let specArray = drupalSettings.selectedAttributes.split(', ');
      let brandArray = drupalSettings.selectedBrand.split(', ');

      $(once('setCatFilters', '#edit-field-product-classifications-target-id option', context)).each(function (index) {
        if ($(this).is(':selected')) {
          $('#edit-category-filter-' + $(this).val()).prop('checked', true);
          catArray.push($(this).val());
          drupalSettings.selectedCategories = catArray;
        }
      });

      $.each(specArray, function (index, value) {
        $('.node--type-product-category__attribute-filter').find('input[value="' + value + '"]').prop('checked', true);
      });

      $.each(brandArray, function (index, value) {
        $('.node--type-brand__category-filter').find('input[value="' + value + '"]').prop('checked', true);
      });

      $(once('setSetFilters', '#edit-field-set-value option', context)).each(function (index) {
        if ($(this).is(':selected')) {
          $('#edit-set-filter-' + $(this).val()).prop('checked', true);
          setArray.push($(this).val());
          drupalSettings.selectedSetFilterValue = setArray;
        }
      });

      $(once('product-category-filters', '.ecom-product-category-filters:not(.ecom-product-category-filters--js-initialized)', context)).each(function (index) {
        let $categoryFilter = $('.node--type-product-category__category-filter');
        let $attributeFilter = $('.node--type-product-category__attribute-filter');
        let $brandFilter = $('.node--type-brand__category-filter');
        let $setFilter = $('.node--type-product-category__set-filter');

        // Track that this component has been initialized.
        $(this).addClass('ecom-product-category-filters--js-initialized');

        $categoryFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'category');
          e.stopImmediatePropagation();
        });

        $attributeFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'attribute');
          e.stopImmediatePropagation();
        });

        $brandFilter.find('.form-checkbox').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'brand');
          e.stopImmediatePropagation();
        });

        $setFilter.find('.form-radio').bind('change', function (e) {
          Drupal.behaviors.productCategoryFilters.filtering($(this), 'set');
          e.stopImmediatePropagation();
        });

      });
    }
  };

  // Accordion view of the filter.

  let filterWrapper = $('.ecom-product-category-filters .form-wrapper legend');
  let firstFilter = $('.ecom-product-category-filters .form-wrapper').first().next();
  firstFilter.addClass('filter--open');
  // placing brand lists on top of list.
  let brandLists = $('.ecom-product-category-filters .form-wrapper').first().prop('outerHTML');
  $('.view-product-category').prepend(brandLists);

  filterWrapper.each(function () {
    $(this).on('click', function () {
      if ($(this).parent().hasClass('filter--open')) {
        $(this).parent().removeClass('filter--open');
        return;
      }

      filterWrapper.each(function () {
        $(this).parent().removeClass('filter--open');
      });
      $(this).parent().addClass('filter--open');
    });
  });

  if (window.innerWidth <= 768) {
    let $mobileMenuIcon = $('.filter-icon');
    let $mobileCloseIcon;
    let $mobileCategoryFilters = $('.ecom-product-category-filters');

    $mobileCategoryFilters.append('<div class="mobile-filter-header"><div class="mobile-filter-header-inner"><span>Filter</span><span class="mobile-close-icon"></span></div></div>');
    $mobileCloseIcon = $('.mobile-close-icon');

    $mobileMenuIcon.click(function () {
      $mobileCategoryFilters.addClass('mobile-show');
    });

    $mobileCloseIcon.click(function () {
      $mobileCategoryFilters.removeClass('mobile-show');
    });
  }

  $('#edit-sort-by').on('change', function () {
    $('.view-product-category').find('input[type=submit]').click();
  });

  $(window).on('load', function () {
    $('#edit-brand-filter-image input[type=checkbox]').each(function () {
      if ($(this).is(':checked')) {
        $(this).addClass('checked');
        $('#edit-brand-filter-image input[type=checkbox]:not(:checked)').removeClass('checked').addClass('not-checked');
      }
      else {
        $('#edit-brand-filter-image input[type=checkbox]').addClass('checked');
      }
    });
  });

})(jQuery, Drupal, once);
