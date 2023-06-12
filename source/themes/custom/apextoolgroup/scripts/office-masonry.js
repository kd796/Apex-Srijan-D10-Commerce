(function ($) {
  'use strict';
  Drupal.behaviors.officeMasonry = {
    attach: function (context, settings) {
      // Masonry js on page load form contact us page office tab .
      var masonryOptions = {
        itemSelector: '.offices .attachment .view-office .view-content .views-row',
        columnWidth: '.offices .attachment .view-office .view-content .views-row',
        percentPosition: true
      };
      var masonrySelector = $('.offices .attachment .view-office .view-content');
      if (masonrySelector) {
        masonrySelector.masonry(masonryOptions);
      }

      $('.offices .attachment .view-office .view-content .views-row .title .mapLocationLink').click(function (event) {
        event.preventDefault();
        $(this).parents('.views-row').addClass('is-active').siblings('.views-row').removeClass('is-active');
        if (masonrySelector) {
          $(window).trigger('resize');
          masonrySelector.masonry('destroy');
          masonrySelector.masonry(masonryOptions);
        }
      });
    }
  };
})(jQuery);
