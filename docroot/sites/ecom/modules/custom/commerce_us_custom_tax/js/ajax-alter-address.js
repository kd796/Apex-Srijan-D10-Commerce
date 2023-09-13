(function ($) {
  'use strict';

  Drupal.behaviors.commerce_us_custom_tax = {
    attach: function (context, settings) {
      let typingTimer;
      const countyField = $(context).find('select[id*=field-county]');
      const postalCodeInput = $(context).find('[id*=address-0-address-postal-code]');
      const localityInput = $(context).find('[id*=address-0-address-locality]');
      const administrativeAreaInput = $(context).find('[id*=address-0-address-administrative-area]');
      const doneTypingInterval = 1000;
      const autocompleteFields = $('.field--type-address .ui-autocomplete-input');

      // Hide elements initially.
      $(context).find('[class*=field-county]').hide();
      countyField.hide();

      autocompleteFields.on('autocompleteclose', function() {
        if ($(this).val() == 'No suggestions found.') {
          $(this).val('');
        }
      });
      
      postalCodeInput.on('autocompleteclose', function () {
        if ($(this).val() == '') {
          administrativeAreaInput.val('');
          localityInput.val('');
        }

        clearTimeout(typingTimer);
        typingTimer = setTimeout(sendAjaxRequest, doneTypingInterval);
      });

      function sendAjaxRequest() {
        const city = localityInput.val();
        const state = administrativeAreaInput.val();
        const zipcode = postalCodeInput.val();
        $.ajax({
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
              $.each(response.county, function (index, countyValue) {
                countyField.append($('<option>', {
                  value: countyValue,
                  text: countyValue
                }));
                if (currentSelectedValue && countyValue === currentSelectedValue) {
                  countyField.find('option[value="' + currentSelectedValue + '"').attr('selected', 'selected');
                }
              });
              $('[class*=field-county]').show();
              countyField.show();
              // Set the first option as selected
              if (countyField.find('option:selected').length === 0) {
                countyField.find('option:first').attr('selected', 'selected');
              }

              // Handle change event for countyField
              countyField.on('change', function () {
                const selectedValue = $(this).val();
                countyField.find('option').each(function () {
                  if ($(this).val() === selectedValue) {
                    $(this).attr('selected', 'selected');
                  } else {
                    $(this).removeAttr('selected');
                  }
                });
              });
            }
            else if (response.error) {
              $('[class*=field-county]').hide();
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
