(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.commerce_us_custom_tax = {
    attach: function () {
      let typingTimer;
      const countyField = $('select[id*=field-county]');
      const postalCodeInput = $('[id*=address-0-address-postal-code]');
      const localityInput = $('[id*=address-0-address-locality]');
      const administrativeAreaInput = $('[id*=address-0-address-administrative-area]');
      const doneTypingInterval = 1000; // Adjust the interval as needed.

      // Hide elements initially.
      $('[class*=field-county]').hide();
      countyField.hide();

      postalCodeInput.on('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(sendAjaxRequest, doneTypingInterval);
      });

      function sendAjaxRequest() {
        const city = localityInput.val();
        const state = administrativeAreaInput.val();
        const zipcode = postalCodeInput.val();

        $.ajax({
          url: '/search/zipcode', // URL for controller call.
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
                  countyField.find('option[value="'+ currentSelectedValue + '"').attr('selected', 'selected');
                }
              });
              $('[class*=field-county]').show();
              countyField.show();
              // Set the first option as selected
              if (countyField.find('option:selected').length === 0) {
                countyField.find('option:first').attr('selected', 'selected');
              }

              // Handle change event for countyField
              countyField.on('change', function() {
                const selectedValue = $(this).val();
                countyField.find('option').each(function() {
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
