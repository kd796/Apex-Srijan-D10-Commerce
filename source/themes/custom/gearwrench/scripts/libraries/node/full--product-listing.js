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
      });
    }
  };
})(jQuery, Drupal);
