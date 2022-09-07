(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.componentCalloutDiagram = {
    attach: function (context, settings) {
      $('.component-callout-diagram:not(.component-callout-diagram--js-initialized)').each(function (index) {
        var $component = $(this);
        // var $diagramItemContainer = $component.find('.field--name-field-callout-diagram-item > .field__item');

        // Track that this component has been initialized.
        $component.addClass('component-hero--js-initialized');
      });
    }
  };

})(jQuery, Drupal);
