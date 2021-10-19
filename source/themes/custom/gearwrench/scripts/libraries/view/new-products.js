(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentNewProducts = {
    attach: function (context, settings) {
      $('.view-new-products:not(.view-new-products--js-initialized)').each(function (index) {
        var $new_products_view = $(this);
        var $viewFilterIcon = $new_products_view.children('.view-header .filter-icon');

        // Track when tab needs to be opened or closed.
        $viewFilterIcon.on('click keyup', function (e) {
          if (e.type === 'click' || (e.type === 'keyup' && [13, 32].indexOf(e.keyCode) !== -1)) {
            var $filterIcon = $(this);
            var $filterList = $filterIcon.parent('view-header').siblings('.view-filters');

            var open = $filterIcon.hasClass('component-filter--open');

            $filterIcon.toggleClass('component-filter--open', (!open));
            $filterIcon.attr('aria-selected', (open) ? 'false' : 'true');
            $filterList
              .attr('aria-hidden', (open) ? 'true' : 'false')
              .attr('hidden', open)
              .toggle();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
