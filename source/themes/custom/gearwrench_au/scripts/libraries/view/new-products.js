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
              var $newProductView = $('#views-exposed-form-new-products-new-products-block');
              $newProductView.find('input[type=submit]').click();
            });
          }
        }
      });
    }
  };

})(jQuery, Drupal);
