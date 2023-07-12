(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.componentWebform = {
    attach: function (context, settings) {
      $('.webform-accordion:not(.component-accordion--js-initialized)').each(function (index) {
        // Initialize variables.
        var $widget = $(this);
        var $accordionContainer = $widget.children('.component-accordion__content');
        var $accordions = $accordionContainer.children('.component-accordion-item');
        var $accordionPersonalInformation = $accordionContainer.children('.form-accordion-item-personal-information');
        var $accordionHeaders = $accordions.children('.component-accordion-item__title');
        var $nextButton = $('.form-input-file-next-button');

        // Mark that the tabs component has been initialized.
        $widget.addClass('component-accordion--js-initialized');

        // Add static roles to elements.
        $widget.attr('role', 'tablist');

        // Attach each accordion item header to its content and hide content
        // that should be hidden.
        $accordions.each(function (accordionIndex) {
          var $accordion = $(this);
          var $accordionHeader = $accordion.children('.component-accordion-item__title');
          var $accordionContent = $accordion.children('.component-accordion-item__content');

          // Generate the accordion tab (header) and panel (content) IDs.
          var accordionId = $accordion.attr('data-entity-id');
          var headerId = 'component-accordion-item-' + accordionId + '__header';
          var panelId = 'component-accordion-item-' + accordionId + '__panel';

          // Determine whether this accordion needs to be open by default.
          var openByDefault = $accordion.hasClass('component-accordion-item--open');

          // Link tab to panel.
          $accordionHeader
            .attr('aria-controls', panelId)
            .attr('aria-selected', (openByDefault) ? 'true' : 'false')
            .attr('id', headerId)
            .attr('role', 'tab')
            .attr('tabindex', (accordionIndex === 0) ? '0' : '-1');

          // Link panel to tab.
          $accordionContent
            .attr('aria-hidden', (!openByDefault) ? 'true' : 'false')
            .attr('aria-labelledby', headerId)
            .attr('hidden', (!openByDefault))
            .attr('id', panelId)
            .attr('role', 'tabpanel');
        });

        // On Next button click, simulate click on Personal Information accordion
        $($nextButton).on('click', function () {
          $($accordionPersonalInformation).children()[0].click();
        });

        // Initialize the roving tabindex.
        $accordionContainer.rovingTabindex('[role=tab]');

        // Track when tab is changed.
        $accordionContainer.on('rovingTabindexChange', '[role=tab]', function (e, data) {
          var $accordion = $(this);
          var $accordionHeader = $accordion.children('.component-accordion-item__title');

          $accordionHeaders.attr('aria-selected', 'false');
          $accordionHeader.attr('aria-selected', 'true');
        });

        // Track when tab needs to be opened or closed.
        $accordionHeaders.on('click keyup', function (e) {
          if (e.type === 'click' || (e.type === 'keyup' && [13, 32].indexOf(e.keyCode) !== -1)) {
            var $accordionHeader = $(this);
            var $accordionContent = $accordionHeader.siblings('.component-accordion-item__content');
            var $accordion = $accordionHeader.parent();
            var open = $accordion.hasClass('component-accordion-item--open');

            $accordion.toggleClass('component-accordion-item--open', (!open));
            $accordionHeader.attr('aria-selected', (open) ? 'false' : 'true');
            $accordionContent
              .attr('aria-hidden', (open) ? 'true' : 'false')
              .attr('hidden', open);

            Drupal.blazy.init.revalidate();
          }
        });
      });
    }
  };

})(jQuery, Drupal, 'once');
