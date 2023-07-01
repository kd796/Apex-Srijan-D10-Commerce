(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.multiStepForm = {
    attach: function (context, settings) {
      // Grouping the radio buttons.
      $(window, context).once('multiStepForm').on('load', function () {
        $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').attr('name', 'screwdriver-style');
      });
      // Add change event handler to radio buttons.
      $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').on('change', function () {
        $('#edit-actions').removeAttr('formnovalidate');
      });
      // Trigger back button.
      $('.multi-steps-label').once('multi-steps-label').on('click', '.step-label', function (e) {
        var $next = $(this).closest('.step-label').next('.step-label');
        if($next.hasClass('active')) {
          $('#edit-back-button').trigger('click');
        }
        return false;
      });

      // end
    }
  };
})(jQuery, Drupal);

