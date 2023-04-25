(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.campbellWhereToBuy = {
    attach: function (context, settings) {
      if (drupalSettings.queryPermsCount === 0) {
        const ViewContainer = $(context).find(".view-markers-map .view-container");
        ViewContainer.find('.view-content').remove();
        ViewContainer.append('<div class="view-empty">No result match.</div>');

        $(context).find(".messages--error").remove();
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
