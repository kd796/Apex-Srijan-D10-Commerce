(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentFeaturedMediaLayoutMasonry = {
    attach: function (context, settings) {
      $('.component-featured-media--layout-masonry:not(.component-featured-media--js-initialized)').each(function (index) {
        var $component = $(this);

        // Set placeholder height of each img based on current width.
        $component.find('img').each(function (imgIndex) {
          var $img = $(this);

          var attrHeight = parseInt($img.attr('height'));
          var attrWidth = parseInt($img.attr('width'));
          var styleWidth = parseInt($img.width());

          // Determine placeholder style height based on aspect ratio,
          if ($.isNumeric(attrHeight) && $.isNumeric(attrWidth) && $.isNumeric(styleWidth)) {
            var styleHeight = (attrHeight / attrWidth) * styleWidth;
            $img.css('height', styleHeight + 'px');
          }
        });

        // Initialize each list-item.
        $component.find('.component-featured-media__list-item').each(function (itemIndex) {
          var $element = $(this).find('figure');

          // Get ratio of inner content.
          var ratio = $element.outerHeight(true) / $element.outerWidth(true);

          // Double it and round it to next biggest integer to get size.
          var size = Math.ceil(ratio * 4);

          // Max size is 8.
          if (size > 16) {
            size = 16;
          }

          // Add size class.
          $(this).addClass('component-featured-media__list-item--size-' + size);
        });

        // Run back through images and unset placeholder height.
        $component.find('img').each(function (imgIndex) {
          $(this).css('height', '');
        });

        // Track that this component has been initialized.
        $component.addClass('.component-featured-media--js-initialized');
      });
    }
  };

})(jQuery, Drupal, once);
