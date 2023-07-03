(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.multiStepForm = {
    attach: function (context, settings) {
      // Grouping the radio buttons.
      $(window, context).once('multiStepForm').on('load', function () {
        $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').attr('name', 'screwdriver-style');
      });
      
      // Trigger back button.
      $('.multi-steps-label').once('multi-steps-label').on('click', '.step-label', function (e) {
        var $next = $(this).closest('.step-label').next('.step-label');
        if($next.hasClass('active')) {
          $('#edit-back-button').trigger('click');
        }
        return false;
      });

      // removing required for which are not showing.
      $('#quotation-add-form input[required], #quotation-add-form select[required], #quotation-add-form textarea[required]').each(function() {
        var field = $(this);
        if (field.is(':hidden')) {
          field.removeAttr('required');
        }
      });

      // Validating the form function.
      function validateForm() {
        event.preventDefault(); // Prevent the default button click behavior
      
        // Check if all required fields are filled
        var allFieldsFilled = true;
        var emptyFields = [];
      
        $('#quotation-add-form input[required], #quotation-add-form select[required], #quotation-add-form textarea[required]').each(function() {
          if ($(this).val() === '' || $(this).val() === 'null' || $(this).val() === '_none') {
            allFieldsFilled = false;
            var fieldNames = $(this).closest('div').find('label').text();
            emptyFields.push(fieldNames);
          }
        });
      
        // Error handling.
        if (allFieldsFilled) {
          $('#edit-actions button').off('click'); // Remove the previous event handler
          $('#edit-actions button').click(); // Trigger the button click event
        } else {
          var errorMessage = 'Please fill in all required fields: ' + emptyFields.join(', ');
          alert(errorMessage);
        }
      }
      
      $('#edit-actions button').once('validation').click(validateForm);
      
      $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').once('removeAttribute').on('change', function () {
        //Remove the required field.
        $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
        $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
        $('#edit-field-quotation-screwdriver-styl-wrapper input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
        // set Required field.
        $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
        $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
        $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
      });
      
      // Rebind the click event handler to the button after dynamically adding required fields
      $('#edit-actions button').off('click').click(validateForm);

      // end
    }
  };
})(jQuery, Drupal);

