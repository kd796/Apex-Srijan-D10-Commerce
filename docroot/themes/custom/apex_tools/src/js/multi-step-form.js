(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.multiStepForm = {
    attach: function (context, settings) {
      // Grouping the radio buttons.
      $('.field--name-field-screwdriver-style input[type="radio"]').attr('name', 'screwdriver-style');
      $('.field--name-field-bit-holder-styles input[type="radio"]').attr('name', 'bit-holder-styles');
      $('.field--name-field-drive-type input[type="radio"]').attr('name', 'drive-type');
      var femaleMaleDriveType =  $('[data-drupal-selector$="subform-field-female-ref-wrapper"] input[type="radio"], [data-drupal-selector$="subform-field-male-hex-ref-wrapper"] input[type="radio"], [data-drupal-selector$="subform-field-female-square-ref-sh-wrapper"] input[type="radio"]');
      femaleMaleDriveType.attr('name', 'female-male-drive-type');

      // Trigger back button.
      $('.multi-steps-label').once('multi-steps-label').on('click', '.step-label', function (e) {
        var $next = $(this).closest('.step-label').next('.step-label');
        if($next.hasClass('active')) {
          $('#edit-back-button').trigger('click');
        }
        return false;
      });

      // removing required for which are not showing.
      $('#edit-field-custom-quotation-worksheet-wrapper input[required], #edit-field-custom-quotation-worksheet-wrapper select[required], #edit-field-custom-quotation-worksheet-wrapper textarea[required]').each(function() {
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

        $('#edit-field-custom-quotation-worksheet-wrapper input[required], #edit-field-custom-quotation-worksheet-wrapper select[required], #edit-field-custom-quotation-worksheet-wrapper textarea[required]').each(function() {
          if ($(this).val() === '' || $(this).val() === 'null' || $(this).val() === '_none') {
            allFieldsFilled = false;
            var fieldNames = $(this).closest('div').find('label').text();
            emptyFields.push(fieldNames);
          }
        });
        // Checking group radio button validation.
        if ($("div").hasClass("field--name-field-broach-opening-allowed")) {
          var noneCheckedbroach = true;
          $('[data-drupal-selector$="subform-field-broach-opening-allowed-wrapper"] input[type="radio"]').each(function() {
            if ($(this).is(':checked')) {
              noneCheckedbroach = false;
              return false; // Exit the loop if a checked radio button is found
            }
          });

          if (noneCheckedbroach) {
            allFieldsFilled = false;
          }
        }

        if ($("div").hasClass("field--name-field-magnetic-field")) {
          var noneCheckedmagnetic = true;
          $('[data-drupal-selector$="subform-field-magnetic-field-wrapper"] input[type="radio"]').each(function() {
            if ($(this).is(':checked')) {
              noneCheckedmagnetic = false;
              return false; // Exit the loop if a checked radio button is found
            }
          });

          if (noneCheckedmagnetic) {
            allFieldsFilled = false;
          }
        }

        // Error handling.
        if (allFieldsFilled) {
          $('#edit-actions button.step-three-submit').off('click'); // Remove the previous event handler
          $('#edit-actions button.step-three-submit').click(); // Trigger the button click event
        } else {
          var errorMessage = 'Please fill in all required fields: ' + emptyFields.join(', ');
          alert(errorMessage);
        }
      }

      $('#edit-actions button.step-three-submit').once('validation').click(validateForm);

      function dynamicChanges() {
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').prop('required', false);
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').removeAttr('required');
        $('.field--name-field-screwdriver-style input[type="radio"]').once('removeAttributeScrewdriver').on('change', function () {
          //Remove the required field.
          $('.field--name-field-screwdriver-style input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
          $('.field--name-field-screwdriver-style input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
          $('.field--name-field-screwdriver-style input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
          // set Required field.
          $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
          $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
          $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
        });

        $('.field--name-field-bit-holder-styles input[type="radio"]').once('removeAttributeBitHolder').on('change', function () {
          //Remove the required field.
          $('.field--name-field-bit-holder-styles input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
          $('.field--name-field-bit-holder-styles input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
          $('.field--name-field-bit-holder-styles input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
          // set Required field.
          $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
          $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
          $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
        });

        $('.field--name-field-drive-type input[type="radio"]').once('removeAttributeBitHolder').on('change', function () {
          //Remove the required field.
          $('.field--name-field-drive-type input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
          $('.field--name-field-drive-type input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
          $('.field--name-field-drive-type input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
          // set Required field.
          $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
          $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
          $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
        });

        $('.field--name-field-socket-type input[type="radio"]').once('removeAttributeBitHolder').on('change', function () {
          //Remove the required field.
          $('.field--name-field-socket-type input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
          $('.field--name-field-socket-type input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
          $('.field--name-field-socket-type input[type="radio"]').closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
          // set Required field.
          $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
          $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
          $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
          var selectedVal = $(this).val();
          if (!(selectedVal === 'socket_extension')) {
            $('.field--name-field-extension-dropdown').find('select').prop('required', false);
          }
        });

        femaleMaleDriveType.once('femaleMaleDriveType').on('change', function () {
          //Remove the required field.
          femaleMaleDriveType.closest('.field--widget-options-buttons').siblings('div').find('label').removeClass('form-required');
          femaleMaleDriveType.closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', false);
          femaleMaleDriveType.closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', false);
          // set Required field.
          $(this).closest('.field--widget-options-buttons').siblings('div').find('label').addClass('form-required');
          $(this).closest('.field--widget-options-buttons').siblings('div').find('select').prop('required', true);
          $(this).closest('.field--widget-options-buttons').siblings('div').find('input').prop('required', true);
        });

        $('[data-drupal-selector$="subform-field-broach-opening-wrapper"] input[value="male_square"]').parent('.form-item').addClass('male-square');
        $('[data-drupal-selector$="subform-field-broach-opening-wrapper"] input[value="male_square"]').prop('required', false);
        $('[data-drupal-selector$="quotation-worksheet-add-more"]').parent('.clearfix').addClass('show-button');
        // hide and show fields.
        $('.field--name-field-socket-dimensions-label .field--name-field-a-square-drive label').addClass('form-required');
        $('.field--name-field-socket-dimensions-label .field--name-field-a-square-drive input').prop('required', true);

        $('[data-drupal-selector$="subform-field-screwdriver-0-subform"] .field--name-field-1-part-type .fieldset-wrapper input[type="radio"]').on('change', function () {
          var seletedOption = $(this).val();
          if (seletedOption === 'bit') {
            $('[data-drupal-selector$="subform-field-screwdriver-4-subform"], [data-drupal-selector$="subform-field-screwdriver-7-subform"]').addClass('hide-options');
            $('[data-drupal-selector$="subform-field-screwdriver-1-subform"], [data-drupal-selector$="subform-field-screwdriver-3-subform"]').removeClass('hide-options');
            $('[data-drupal-selector$="subform-field-screwdriver-4-top"], [data-drupal-selector$="subform-field-screwdriver-7-top"]').addClass('hide-options');
            $('[data-drupal-selector$="subform-field-screwdriver-1-top"], [data-drupal-selector$="subform-field-screwdriver-3-top"]').removeClass('hide-options');
            $('[data-drupal-selector$="subform-field-a-square-drive-wrapper"] input, [data-drupal-selector$="subform-field-a-square-drive-wrapper"] select').prop('required', false);
            $('[data-drupal-selector$="subform-field-c-overall-length-wrapper"] input, [data-drupal-selector$="subform-field-c-overall-length-wrapper"] select').prop('required', false);
            $('[data-drupal-selector$="subform-field-h-broach-opening-wrapper"] select').prop('required', false);
            $('[data-drupal-selector$="subform-field-screwdriver-5-top"], [data-drupal-selector$="subform-field-screwdriver-5-subform"]').removeClass('show-option');
            $(this).closest('.field--name-field-1-part-type').siblings('div').removeClass('show-option');
            $('.field--name-field-bit-holder-style-for-hex').removeClass('show-option');
            $('[data-drupal-selector$="subform-field-screwdriver-5"]').removeClass('show-option');
            $('[data-drupal-selector$="subform-field-screwdriver-6"]').removeClass('show-option');

            $('[data-drupal-selector$="subform-field-screwdriver-7-top"] img, [data-drupal-selector$="subform-field-screwdriver-7-top"] span').removeClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive').removeClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-i-torx-r-size').removeClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-k-spring-force-rate-').removeClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-l-barbell-style-dims').removeClass('hide-option');
          } else {
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').prop('required', false);
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').removeAttr('required');
            $(this).closest('.field--name-field-1-part-type').siblings('div').addClass('show-option');
            $('[data-drupal-selector$="subform-field-screwdriver-1-subform"], [data-drupal-selector$="subform-field-screwdriver-3-subform"]').addClass('hide-options');
            $('[data-drupal-selector$="subform-field-screwdriver-4-subform"], [data-drupal-selector$="subform-field-screwdriver-7-subform"]').removeClass('hide-options');
            $('[data-drupal-selector$="subform-field-screwdriver-1-top"], [data-drupal-selector$="subform-field-screwdriver-3-top"]').addClass('hide-options');
            $('[data-drupal-selector$="subform-field-screwdriver-4-top"], [data-drupal-selector$="subform-field-screwdriver-7-top"]').removeClass('hide-options');
            $('[data-drupal-selector$="subform-field-a-square-drive-wrapper"] input, [data-drupal-selector$="subform-field-a-square-drive-wrapper"] select').prop('required', true);
            $('[data-drupal-selector$="subform-field-c-overall-length-wrapper"] input, [data-drupal-selector$="subform-field-c-overall-length-wrapper"] select').prop('required', true);
            $('[data-drupal-selector$="subform-field-h-broach-opening-wrapper"] select').prop('required', true);
            $('[data-drupal-selector$="subform-field-screwdriver-5-top"], [data-drupal-selector$="subform-field-screwdriver-5-subform"]').addClass('show-option');
            $('.field--name-field-bit-holder-style-for-hex').addClass('show-option');
            $('[data-drupal-selector$="subform-field-screwdriver-5"]').addClass('show-option');
            $('[data-drupal-selector$="subform-field-screwdriver-6"]').addClass('show-option');

            $('[data-drupal-selector$="subform-field-screwdriver-7-top"] img, [data-drupal-selector$="subform-field-screwdriver-7-top"] span').addClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive').addClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').removeAttr('required');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-i-torx-r-size').addClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-k-spring-force-rate-').addClass('hide-option');
            $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-l-barbell-style-dims').addClass('hide-option');
          }
        });
        $('[data-drupal-selector$="subform-field-screwdriver-8-subform"] .field--name-field-cover-requirements input[type="radio"]').on('change', function () {
          var seletedOption = $(this).val();
          if (seletedOption === '1') {
            $('[data-drupal-selector$="subform-field-screwdriver-8-subform-field-components"]').removeClass('hide-options');
            $('.field--name-field__bit-configuration').addClass('show-option');
          } else {
            $('[data-drupal-selector$="subform-field-screwdriver-8-subform-field-components"]').addClass('hide-options');
            $('.field--name-field__bit-configuration').removeClass('show-option');
            $('[data-drupal-selector$="subform-field-screwdriver-9"] .field--name-field-title').css('display','none');
          }
        });

        $('[data-drupal-selector$="subform-field-nylon-requirements-yes-no-"] .js-form-type-radio input[type="radio"]').on('change', function () {
          var seletedOption = $(this).val();
          if (seletedOption === 'nylon_yes') {
            $('[data-drupal-selector$="subform-field-nylon-covers-features-0-subform-field-cover-features-radio-wrapper"], .field--name-field-other-considerations-speci, .field--name-field-o-d-limitations-must-speci, .field--name-field-od-textbox, .field--name-field-nylon-other-textbox').removeClass('hide-options');
            $('.field--name-field-field-nylon-component').addClass('show-options');
          } else {
            $('[data-drupal-selector$="subform-field-nylon-covers-features-0-subform-field-cover-features-radio-wrapper"], .field--name-field-other-considerations-speci, .field--name-field-o-d-limitations-must-speci, .field--name-field-od-textbox, .field--name-field-nylon-other-textbox').addClass('hide-options');
            $('.field--name-field-field-nylon-component').removeClass('show-options');
          }
        });

        // Adding required fields.
        $('.field--name-field-magnetic-field .js-form-type-radio:nth-child(2) input[type="radio"]').prop('required', true);
        $('.field--name-field-broach-opening-allowed .js-form-type-radio:nth-child(2) input[type="radio"]').prop('required', true);
        // Adding required fields on change.
        $('.field--name-field-magnetic-field input[type="radio"]').on('change', function() {
          $('.field--name-field-magnetic-field input[type="radio"]').prop('required', false);
          $(this).prop('required', true);
        });
        $('.field--name-field-broach-opening-allowed input[type="radio"]').on('change', function() {
          $('.field--name-field-broach-opening-allowed input[type="radio"]').prop('required', false);
          $(this).prop('required', true);
          $('[data-drupal-selector$="subform-field-broach-opening-wrapper"] input[value="male_square"]').prop('required', false);
        });

        // Clear Options.
        $('.clear-option-feature').on('click', function (e) {
          $('[data-drupal-selector$="subform-field-optional-feature-wrapper"] fieldset input[type="checkbox"]').prop('checked', false);
        });
        $('.clear-option-drive').on('click', function (e) {
          $('.field--name-field-drive-configuration input[type="checkbox"]').prop('checked', false);
          $('.field--name-field-driveconf-hex input[type="checkbox"]').prop('checked', false);
          $('.field--name-field-driveconf-other input[type="checkbox"]').prop('checked', false);
        });
        $('.clear-option-cover-feature').on('click', function (e) {
          $('.field--name-field-cover-features-radio fieldset input[type="checkbox"]').prop('checked', false);
          $('.field--name-field-o-d-limitations-must-speci input[type="checkbox"]').prop('checked', false);
          $('.field--name-field-other-considerations-speci input[type="checkbox"]').prop('checked', false);
          $('.field--name-field-od-textbox input[type="text"]').val('');
          $('.field--name-field-nylon-other-textbox input[type="text"]').val('');
        });
        $('.clear-option-cover-feature-uw').on('click', function (e) {
          $('[data-drupal-selector$="subform-field-optional-features-ref-0-subform-field-cover-features"] input[type="checkbox"]').prop('checked', false);
        });
        // adding title.
        $('[data-drupal-selector$="subform-field-female-ref-0-top"]').find('.paragraph-type-label').text('Style');
        // Show field on select change.
        var retractableSelect = $('.field--name-field-retractable select');
        var targetText = $('[data-drupal-selector$="subform-field-screwdriver-8-subform-field-components-1-subform"] .field--name-field-title');
        retractableSelect.on('change', function () {
          var valueRetractable = $(this).val();
          if (valueRetractable === 'spring') {
            targetText.addClass('show-option');
          } else {
            targetText.removeClass('show-option');
          }
        });
        //show on load.
        var retractableSelectOnload = $('.field--name-field-retractable select').val();
        if (retractableSelectOnload === 'spring') {
          targetText.addClass('show-option');
        } else {
          targetText.removeClass('show-option');
        }
        //end of the function.
      }
      // Trigger back button.
      $('.step-label.active').prev().addClass('prev');


      $('[data-drupal-selector$="subform-field-optional-feature-wrapper"] fieldset legend').after('<span class="clear-option clear-option-feature">Clear Options</span>');
      $('.field--name-field-drive-configuration > fieldset > legend').after('<span class="clear-option clear-option-drive">Clear Options</span>');
      $('.field--name-field-cover-features-radio > fieldset > legend').after('<span class="clear-option clear-option-cover-feature">Clear Options</span>');
      $('.field--name-field-cover-features > fieldset > legend').after('<span class="clear-option clear-option-cover-feature-uw">Clear Options</span>');
      // Clear Options.
      $('.clear-option-feature').on('click', function (e) {
        $('[data-drupal-selector$="subform-field-optional-feature-wrapper"] fieldset input[type="checkbox"]').prop('checked', false);
      });
      $('.clear-option-drive').on('click', function (e) {
        $('.field--name-field-drive-configuration input[type="checkbox"]').prop('checked', false);
        $('.field--name-field-driveconf-hex input[type="checkbox"]').prop('checked', false);
        $('.field--name-field-driveconf-other input[type="checkbox"]').prop('checked', false);
      });
      $('.clear-option-cover-feature').on('click', function (e) {
        $('.field--name-field-cover-features-radio fieldset input[type="checkbox"]').prop('checked', false);
        $('.field--name-field-o-d-limitations-must-speci input[type="checkbox"]').prop('checked', false);
        $('.field--name-field-other-considerations-speci input[type="checkbox"]').prop('checked', false);
        $('.field--name-field-od-textbox input[type="text"]').val('');
        $('.field--name-field-nylon-other-textbox input[type="text"]').val('');
      });
      $('.clear-option-cover-feature-uw').on('click', function (e) {
        $('[data-drupal-selector$="subform-field-optional-features-ref-0-subform-field-cover-features"] input[type="checkbox"]').prop('checked', false);
      });

      var seletedOptionScrewdriver = $('[data-drupal-selector$="subform-field-screwdriver-0-subform"] .field--name-field-1-part-type .fieldset-wrapper input[type="radio"]').val();
      if (seletedOptionScrewdriver === 'bit') {
        $('[data-drupal-selector$="subform-field-screwdriver-4-subform"], [data-drupal-selector$="subform-field-screwdriver-7-subform"]').addClass('hide-options');
        $('[data-drupal-selector$="subform-field-screwdriver-1-subform"], [data-drupal-selector$="subform-field-screwdriver-3-subform"]').removeClass('hide-options');
        $('[data-drupal-selector$="subform-field-screwdriver-4-top"], [data-drupal-selector$="subform-field-screwdriver-7-top"]').addClass('hide-options');
        $('[data-drupal-selector$="subform-field-screwdriver-1-top"], [data-drupal-selector$="subform-field-screwdriver-3-top"]').removeClass('hide-options');
        $('[data-drupal-selector$="subform-field-a-square-drive-wrapper"] input, [data-drupal-selector$="subform-field-a-square-drive-wrapper"] select').prop('required', false);
        $('[data-drupal-selector$="subform-field-c-overall-length-wrapper"] input, [data-drupal-selector$="subform-field-c-overall-length-wrapper"] select').prop('required', false);
        $('[data-drupal-selector$="subform-field-h-broach-opening-wrapper"] select').prop('required', false);
        $('[data-drupal-selector$="subform-field-screwdriver-5-top"], [data-drupal-selector$="subform-field-screwdriver-5-subform"]').removeClass('show-option');
        $(this).closest('.field--name-field-1-part-type').siblings('div').removeClass('show-option');
        $('.field--name-field-bit-holder-style-for-hex').removeClass('show-option');
        $('[data-drupal-selector$="subform-field-screwdriver-5"]').removeClass('show-option');
        $('[data-drupal-selector$="subform-field-screwdriver-6"]').removeClass('show-option');

        $('[data-drupal-selector$="subform-field-screwdriver-7-top"] img, [data-drupal-selector$="subform-field-screwdriver-7-top"] span').removeClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive').removeClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-i-torx-r-size').removeClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-k-spring-force-rate-').removeClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-l-barbell-style-dims').removeClass('hide-option');
      } else {
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').prop('required', false);
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').removeAttr('required');
        $(this).closest('.field--name-field-1-part-type').siblings('div').addClass('show-option');
        $('[data-drupal-selector$="subform-field-screwdriver-1-subform"], [data-drupal-selector$="subform-field-screwdriver-3-subform"]').addClass('hide-options');
        $('[data-drupal-selector$="subform-field-screwdriver-4-subform"], [data-drupal-selector$="subform-field-screwdriver-7-subform"]').removeClass('hide-options');
        $('[data-drupal-selector$="subform-field-screwdriver-1-top"], [data-drupal-selector$="subform-field-screwdriver-3-top"]').addClass('hide-options');
        $('[data-drupal-selector$="subform-field-screwdriver-4-top"], [data-drupal-selector$="subform-field-screwdriver-7-top"]').removeClass('hide-options');
        $('[data-drupal-selector$="subform-field-a-square-drive-wrapper"] input, [data-drupal-selector$="subform-field-a-square-drive-wrapper"] select').prop('required', true);
        $('[data-drupal-selector$="subform-field-c-overall-length-wrapper"] input, [data-drupal-selector$="subform-field-c-overall-length-wrapper"] select').prop('required', true);
        $('[data-drupal-selector$="subform-field-h-broach-opening-wrapper"] select').prop('required', true);
        $('[data-drupal-selector$="subform-field-screwdriver-5-top"], [data-drupal-selector$="subform-field-screwdriver-5-subform"]').addClass('show-option');
        $('.field--name-field-bit-holder-style-for-hex').addClass('show-option');
        $('[data-drupal-selector$="subform-field-screwdriver-5"]').addClass('show-option');
        $('[data-drupal-selector$="subform-field-screwdriver-6"]').addClass('show-option');

        $('[data-drupal-selector$="subform-field-screwdriver-7-top"] img, [data-drupal-selector$="subform-field-screwdriver-7-top"] span').addClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive').addClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-i-torx-r-size').addClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-k-spring-force-rate-').addClass('hide-option');
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-l-barbell-style-dims').addClass('hide-option');
      }

      var seletedOptionScrewdriver7 = $('[data-drupal-selector$="subform-field-screwdriver-8-subform"] .field--name-field-cover-requirements input[type="radio"]').val();
      if (seletedOptionScrewdriver7 === '1') {
        $('[data-drupal-selector$="subform-field-screwdriver-8-subform-field-components"]').removeClass('hide-options');
        $('.field--name-field__bit-configuration').addClass('show-option');
      } else {
        $('[data-drupal-selector$="subform-field-screwdriver-8-subform-field-components"]').addClass('hide-options');
        $('.field--name-field__bit-configuration').removeClass('show-option');
        $('[data-drupal-selector$="subform-field-screwdriver-9"] .field--name-field-title').css('display','none');
      }

      var seletedOptionNylon = $('[data-drupal-selector$="subform-field-nylon-requirements-yes-no-"] .js-form-type-radio input[type="radio"]').val();
      if (seletedOptionNylon === 'nylon_yes') {
        $('[data-drupal-selector$="subform-field-nylon-covers-features-0-subform-field-cover-features-radio-wrapper"], .field--name-field-other-considerations-speci, .field--name-field-o-d-limitations-must-speci, .field--name-field-od-textbox, .field--name-field-nylon-other-textbox').removeClass('hide-options');
        $('.field--name-field-field-nylon-component').addClass('show-options');
      } else {
        $('[data-drupal-selector$="subform-field-nylon-covers-features-0-subform-field-cover-features-radio-wrapper"], .field--name-field-other-considerations-speci, .field--name-field-o-d-limitations-must-speci, .field--name-field-od-textbox, .field--name-field-nylon-other-textbox').addClass('hide-options');
        $('.field--name-field-field-nylon-component').removeClass('show-options');
      }

      // Rebind the click event handler to the button after dynamically adding required fields
      $('#edit-actions button.step-three-submit').off('click').click(validateForm);

      //Adding class to hide collapse option.
      $('.paragraphs-icon-button-collapse').closest('.paragraph-top').addClass('collapse-wrapper');

      // Trigger add more paragraph
      $('#field-custom-quotation-worksheet-quotation-screwdriver-add-more').addClass('field-custom-quotation-worksheet-quotation-screwdriver-add-more');
      $('#field-custom-quotation-worksheet-socket-extension-adapter-add-more').addClass('field-custom-quotation-worksheet-socket-extension-adapter-add-more');
      $('#field-custom-quotation-worksheet-universal-swivel-wrench-para-add-more').addClass('field-custom-quotation-worksheet-universal-swivel-wrench-para-add-more');

      //Show and hide the step 3 button
      if ($("div").hasClass("field--name-field-order-type")) {
        $('.step-three-submit').addClass("show-button");
      }

      // paragraph icon append
      var itemsScrewdriver = $('.field--name-field-screwdriver-style tbody tr');
      itemsScrewdriver.each(function() {
        var hiddenDiv = $(this).children().find('.paragraph-top');
        var image = hiddenDiv.find('img');
        var targetPlace = $(this).children().find('.paragraphs-subform').find('fieldset');

        targetPlace.addClass('target-class');
        targetPlace.append(image);
      });

      var itemDriverType = $('.field--name-field-drive-type tbody tr');
      itemDriverType.each(function() {
        var hiddenDiv = $(this).children().find('.paragraph-top');
        var image = hiddenDiv.find('img');
        var targetPlace = $(this).children().find('.paragraphs-subform').find('fieldset');

        targetPlace.addClass('drive-target-class');
        targetPlace.append(image);
      });


      var hiddenDivFemaleSquare = $('[data-drupal-selector$="subform-field-female-ref"] tbody tr td > div > div .paragraph-top');
      var imageFemaleSquare = hiddenDivFemaleSquare.find('img');
      var targetPlaceFemaleSquare = $('[data-drupal-selector$="subform-field-female-ref"] tbody tr td > div > div .paragraphs-subform').find('fieldset');
      targetPlaceFemaleSquare.append(imageFemaleSquare);

      var hiddenDivMaleHexDrive = $('[data-drupal-selector$="subform-field-male-hex-ref-wrapper"] tbody tr td > div > div .paragraph-top');
      var imageMaleHexDrive = hiddenDivMaleHexDrive.find('img');
      var targetPlaceMaleHexDrive = $('[data-drupal-selector$="subform-field-male-hex-ref-wrapper"] tbody tr td > div > div .paragraphs-subform').find('fieldset');
      targetPlaceMaleHexDrive.append(imageMaleHexDrive);

      var hiddenDivFemaleSquareDrive = $('[data-drupal-selector$="subform-field-female-square-ref-sh-wrapper"] tbody tr td > div > div .paragraph-top');
      var imageFemaleSquareDrive = hiddenDivFemaleSquareDrive.find('img');
      var targetPlaceFemaleSquareDrive = $('[data-drupal-selector$="subform-field-female-square-ref-sh-wrapper"] tbody tr td > div > div .paragraphs-subform').find('fieldset');
      targetPlaceFemaleSquareDrive.append(imageFemaleSquareDrive);


      dynamicChanges();

      $(document).ajaxComplete(function(){
        $('#edit-actions button.step-three-submit').once('validation').click(validateForm);
        dynamicChanges();
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').prop('required', false);
        $('[data-drupal-selector$="subform-field-screwdriver-7-subform"] .field--name-field-a-square-drive input').removeAttr('required');
        $('#edit-actions button.step-three-submit').off('click').click(validateForm);
        //Show and hide the step 3 button
        if ($("div").hasClass("field--name-field-order-type")) {
          $('.step-three-submit').addClass("show-button");
        }
        });

        $(window).once('onceOnLoad').on('load', function (e) {
          $('[data-drupal-selector$="subform-field-optional-feature-wrapper"] fieldset legend').after('<span class="clear-option">Clear Options</span>');
          // Trigger back button.
          $('.step-label.active').prev().addClass('prev');
        });


      // end
    }
  };
})(jQuery, Drupal);

