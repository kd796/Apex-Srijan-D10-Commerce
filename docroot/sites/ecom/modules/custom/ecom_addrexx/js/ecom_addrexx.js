(function ($, Drupal) {
  'use strict';

  var eventTriggered = false;

  Drupal.behaviors.ecom_addrexx = {
    attach: function (context) {
      let typingTimer;
      // Initializing input variables.
      const countyField = jQuery(context).find('select[id*=field-county]');
      const postalCodeInput = jQuery(context).find('[id*=address-0-address-postal-code]');
      var localityInput = jQuery(context).find('[id*=address-0-address-locality]');
      var administrativeAreaInput = jQuery(context).find('[id*=address-0-address-administrative-area]');
      var addressLine1 = jQuery('[id*=address-0-address-address-line1]');
      var addressLine2 = jQuery('[id*=address-0-address-address-line2]');
      const doneTypingInterval = 1000;
      const autocompleteFields = jQuery('.field--type-address .ui-autocomplete-input');

      // Hide elements initially.
      jQuery(context).find('[class*=field-county]').hide();
      countyField.hide();

      autocompleteFields.on('autocompleteclose', function() {
        if (jQuery(this).val() == 'No suggestions found.' ||
          jQuery(this).val() == 'Error while validating address.' ) {
          jQuery(this).val('');
        }
      });

      // Postalcode autocompletes close event.
      jQuery(postalCodeInput).on("autocompleteclose", function ( event, ui ) {
        // Pick only numeric value to set it as Zipcode.
        var zipCodePattern = /\d{5}(-\d{4})?/;
        var zipCodeMatch = jQuery(this).val().match(zipCodePattern);
        if (zipCodeMatch) {
          jQuery(this).val(zipCodeMatch[0]);
        }
        // Clear autocomplete inputs if zipcode is empty.
        if (jQuery(this).val() == '') {
          administrativeAreaInput.val('');
          localityInput.val('');
        }
        // Get county value.
        clearTimeout(typingTimer);
        typingTimer = setTimeout(sendAjaxRequest, doneTypingInterval);
      });

      // Zipcode autocompleteselect event.
      jQuery(postalCodeInput).on('autocompleteselect', function (event, ui) {
        var inputString = ui.item.value;

        var parts = inputString.split(',').map(function (item) {
          return item.trim();
        });

        // Extract postal code, city, and state.
        var state = parts[1];
        var zipCodePattern = /\d{5}(-\d{4})?/;
        var zipCodeMatch = parts[0].match(zipCodePattern);
        if (zipCodeMatch) {
          // Extract the ZIP code from the match.
          var postalCode = zipCodeMatch[0];
          var city = parts[0].substring(postalCode.length).trim();
        }
        if (postalCode) {
          if (jQuery(context).find('input[name^="address["][name$="][address][locality]"]').length) {
            localityInput = jQuery(context).find('input[name^="address["][name$="][address][locality]"]');
          }
          if (jQuery(context).find('input[name^="address["][name$="][address][administrative_area]"]').length) {
            administrativeAreaInput = jQuery(context).find('input[name^="address["][name$="][address][administrative_area]"]');
          }
          administrativeAreaInput.val(state);
          localityInput.val(city);
        }
      });

      jQuery(context).find(localityInput).on('input', cityUpdate);

      jQuery(context).find(addressLine1).on('input', addressUpdate);
      jQuery(context).find(addressLine2).on('input', addressUpdate);

      // Update address autocomplete field.
      function addressUpdate() {
        var postalCodeValue = postalCodeInput.val();
        // Check if postalCodeValue is filled.
        if (postalCodeValue.length < 1) {
          jQuery(this).next('.error-message').text('Please enter a valid Zip code');
        }
        var localityValue = localityInput.val();
        var localityValueSubStr = localityValue.substring(0, 5).replace(/\s+/g, '+');
        var sourceValue = localityValueSubStr + postalCodeValue;
        var currentPath = jQuery(this).attr('data-autocomplete-path');

        // Check if "contextKey" already exists in the data-autocomplete-path.
        if (currentPath.indexOf('contextKey=') === -1) {
          // "contextKey" doesn't exist, so add it.
          currentPath += '&contextKey=' + sourceValue;
        } else {
          // "contextKey" already exists, so update its value.
          currentPath = currentPath.replace(/contextKey=[^&]+/, 'contextKey=' + sourceValue);
        }

        // Update the autocomplete field's parameters.
        jQuery(this).attr('data-autocomplete-path', currentPath);
      }

      // Update city autocomplete field.
      function cityUpdate() {
        var postalCodeValue = postalCodeInput.val();
        var administrativeAreaValue = administrativeAreaInput.val();
        var sourceValue = 'state=' + administrativeAreaValue + '&zipcode=' + postalCodeValue;
        var currentPath = jQuery(this).attr('data-autocomplete-path');
        // Check if "state" already exists in the data-autocomplete-path.
        if (currentPath.indexOf('state=') === -1) {
          // "state" doesn't exist, so add it.
          currentPath += '&state=' + sourceValue;
        } else {
          // "state" already exists, so update its value.
          currentPath = currentPath.replace(/state=[^&]+/, 'state=' + administrativeAreaValue);
          currentPath = currentPath.replace(/zipcode=[^&]+/, 'zipcode=' + postalCodeValue);
        }
        // Update the autocomplete field's parameters.
        jQuery(this).attr('data-autocomplete-path', currentPath);
      }

      // Update county field.
      function sendAjaxRequest() {
        const city = localityInput.val();
        const state = administrativeAreaInput.val();
        const zipcode = postalCodeInput.val();
        jQuery.ajax({
          url: '/search/zipcode',
          type: 'POST',
          data: {
            city,
            state,
            zipcode,
          },
          dataType: 'json',
          success: function (response) {
            if (response.county) {
              const currentSelectedValue = countyField.val();
              countyField.empty();
              // Iterate through the response.county array and create options
              jQuery.each(response.county, function (index, countyValue) {
                countyField.append(jQuery('<option>', {
                  value: countyValue,
                  text: countyValue
                }));
                if (currentSelectedValue && countyValue === currentSelectedValue) {
                  countyField.find('option[value="' + currentSelectedValue + '"').attr('selected', 'selected');
                }
              });
              jQuery('[class*=field-county]').show();
              countyField.show();
              // Set the first option as selected
              if (countyField.find('option:selected').length === 0) {
                countyField.find('option:first').attr('selected', 'selected');
              }

              // Handle change event for countyField
              countyField.on('change', function () {
                const selectedValue = jQuery(this).val();
                countyField.find('option').each(function () {
                  if (jQuery(this).val() === selectedValue) {
                    jQuery(this).attr('selected', 'selected');
                  } else {
                    jQuery(this).removeAttr('selected');
                  }
                });
              });
            }
            else if (response.error) {
              jQuery('[class*=field-county]').hide();
              countyField.hide();

              if (countyField.css('display') == 'none') {
                countyField.find('option:selected').removeAttr("selected"); 
              }
            }
          },
          error: function (xhr, status, error) {
            console.error(error);
          },
        });
      }

    }
  };
})(jQuery, Drupal);
