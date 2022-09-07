(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentCalloutDiagram = {
    attach: function (context, settings) {
      $('.component-callout-diagram:not(.component-callout-diagram--js-initialized)').each(function (index) {
        var $component = $(this);
        var $diagramItemContainer = $component.find('.field--name-field-callout-diagram-item > .field__item');
        var $diagramItemContainerNum = $diagramItemContainer.length;
        var $diagramBottomContainer = $component.find('.component-callout-diagram__bottom-container');
        var $diagramBottomContainerWidth = '100%';

        // Track that this component has been initialized.
        $component.addClass('component-hero--js-initialized');

        // Set width of bottom container based on num of item containers
        if ($diagramItemContainerNum === 1) {
          $diagramBottomContainerWidth = '25%';
        }
        else if ($diagramItemContainerNum === 2) {
          $diagramBottomContainerWidth = '50%';
        }
        else if ($diagramItemContainerNum === 3) {
          $diagramBottomContainerWidth = '75%';
        }
        console.log($diagramBottomContainerWidth);

        $($diagramBottomContainer).css('width', $diagramBottomContainerWidth);
      });
    }
  };

})(jQuery, Drupal);
