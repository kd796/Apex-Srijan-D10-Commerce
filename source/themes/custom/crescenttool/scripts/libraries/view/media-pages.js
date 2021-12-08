(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentMediaPages = {
    attach: function (context, settings) {
      $('.view-media-pages:not(.view-media-pages--js-initialized)').once('media-filter').each(function (index) {

        if (window.innerWidth <= 768) {
          var $viewContainer = $('.views-element-container');

          $viewContainer.prepend('<div class="mobile-view-filter-header"><span class="filter-icon"></span></div>');

          var $mobileMenuIcon = $('.filter-icon');
          var $mobileCloseIcon;
          var $mobileMediaFilters = $('.view-filters');
          var $viewHeader = $('.mobile-view-filter-header');

          $mobileMediaFilters.append('<div class="mobile-filter-header"><div class="mobile-filter-header-inner"><span>Filter</span><span class="mobile-close-icon"></span></div></div>');
          $mobileCloseIcon = $('.mobile-close-icon');
          $viewHeader.after('<div class="chip-container"><ul class="chips" role="list"></ul></div>');

          $mobileMenuIcon.click(function () {
            $mobileMediaFilters.addClass('mobile-show');
          });

          $mobileCloseIcon.click(function () {
            $mobileMediaFilters.removeClass('mobile-show');
          });
        }

        // Logic for the mobile filtering chips
        var $chipList = $('.chips');
        var cleanedArray = [];
        var checkedFilters = $('input[type=checkbox]:checked');

        // Loop through all checked filters
        for (var indx = 0; indx < checkedFilters.length; indx++) {
          cleanedArray[indx] = checkedFilters[indx].value;
          cleanedArray[indx] = cleanedArray[indx].split('(');
          cleanedArray[indx][0] = cleanedArray[indx][0].trim();
          cleanedArray[indx][1] = checkedFilters[indx].id;
          cleanedArray[indx][2] = 'attribute';
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
              var $mediaView = $('#views-exposed-form-media-pages-default');
              $mediaView.find('input[type=submit]').click();
            });
          }
        }
      });
    }
  };

})(jQuery, Drupal);
