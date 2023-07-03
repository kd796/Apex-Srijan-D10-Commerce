(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.multiStepForm = {
    attach: function (context, settings) {
      // Grouping the radio buttons.
      $(window, context).once('multiStepForm').on('load', function () {
        $('.field--name-field-screwdriver-style input[type="radio"]').attr('name', 'screwdriver-style');
        $('.field--name-field-bit-holder-styles input[type="radio"]').attr('name', 'bit-holder-styles');
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
      
      $('.field--name-field-screwdriver-style input[type="radio"]').once('removeAttribute').on('change', function () {
        //Remove the required field.
        $('.field--name-field-screwdriver-style input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
        $('.field--name-field-screwdriver-style input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
        $('.field--name-field-screwdriver-style input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
        // set Required field.
        $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
        $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
        $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
      });

      $('.field--name-field-bit-holder-styles input[type="radio"]').once('removeAttribute').on('change', function () {
        //Remove the required field.
        $('.field--name-field-bit-holder-styles input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
        $('.field--name-field-bit-holder-styles input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
        $('.field--name-field-bit-holder-styles input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
        // set Required field.
        $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
        $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
        $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
      });
      
      // Rebind the click event handler to the button after dynamically adding required fields
      $('#edit-actions button').off('click').click(validateForm);

      // Trigger add more paragraph
      $('#field-custom-quotation-worksheet-quotation-screwdriver-add-more').addClass('field-custom-quotation-worksheet-quotation-screwdriver-add-more');
      $('#field-custom-quotation-worksheet-socket-extension-adapter-add-more').addClass('field-custom-quotation-worksheet-socket-extension-adapter-add-more');
      $('#field-custom-quotation-worksheet-universal-swivel-wrench-para-add-more').addClass('field-custom-quotation-worksheet-universal-swivel-wrench-para-add-more');
      $('#edit-field-custom-drive-tool-type').once('triggerAdd').on('change', function () {
        var selectedValue = $(this).val();
        // Trigger the button based on the selected value
        if (selectedValue === 'screwdriver') {
          $('.field-custom-quotation-worksheet-quotation-screwdriver-add-more').mousedown();
        } else if (selectedValue === 'socket') {
          $('.field-custom-quotation-worksheet-socket-extension-adapter-add-more').mousedown();
        } else if (selectedValue === 'universal_wrench') {
          $('.field-custom-quotation-worksheet-universal-swivel-wrench-para-add-more').mousedown();
        }
        // show add buttons.
        $('#edit-field-custom-quotation-worksheet-add-more').addClass('show-button');
        //Adding class to hide collapse option.
        $('.paragraphs-icon-button-collapse').closest('.paragraph-top').addClass('collapse-wrapper'); 
      });
      var selectedValuettt = $('#edit-field-custom-drive-tool-type').val();
      $(document).ajaxComplete(function(){
        var selectedValue;
        $('#edit-field-custom-drive-tool-type').once('triggerAdd').on('change', function () {
          selectedValue = $(this).val();
          // Trigger the button based on the selected value
          if (selectedValue === 'screwdriver') {
            $('.field-custom-quotation-worksheet-quotation-screwdriver-add-more').mousedown();
          } else if (selectedValue === 'socket') {
            $('.field-custom-quotation-worksheet-socket-extension-adapter-add-more').mousedown();
          } else if (selectedValue === 'universal_wrench') {
            $('.field-custom-quotation-worksheet-universal-swivel-wrench-para-add-more').mousedown();
          }
        });
        $('#edit-field-custom-drive-tool-type').val(selectedValuettt);
        // show add buttons.
        $('#edit-field-custom-quotation-worksheet-add-more').addClass('show-button');
        //Adding class to hide collapse option.
        $('.paragraphs-icon-button-collapse').closest('.paragraph-top').addClass('collapse-wrapper');
      });
       

      // end
    }
  };
})(jQuery, Drupal);

