(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.ecom_addrexx = {
    attach: function (context) {
      let typingTimer;
      var doneTypingInterval = 1000;

      var fieldset = "";
      var countyField = "";
      var postalCodeInput = "";
      var localityInput = "";
      var administrativeAreaInput = "";
      var addressLine1 = "";
      var autocompleteFields = "";

      if (jQuery("select.country").length > 0) {
        // Initializing input variables.
        countyField = jQuery(context).find("select[id*=field-county]");
        postalCodeInput = jQuery(context).find(
          'input[name$="[address][postal_code]"]'
        );
        localityInput = jQuery(context).find(
          'input[name$="[address][locality]"]'
        );
        administrativeAreaInput = jQuery(context).find(
          'select[name^="address["][name$="][address][administrative_area]"]'
        );
        addressLine1 = jQuery(context).find(
          'input[name$="[address][address_line1]"]'
        );
        autocompleteFields = jQuery(context).find(
          ".field--type-address .ui-autocomplete-input"
        );

        jQuery(autocompleteFields).on("autocompleteclose", function () {
          if (
            jQuery(this).val() == "No suggestions found." ||
            jQuery(this).val() == "Error while validating address."
          ) {
            jQuery(this).val("");
          }
        });
        // Postalcode autocompletes close event.
        jQuery(postalCodeInput).on("autocompleteclose", function (event, ui) {
          // Pick only numeric value to set it as Zipcode.
          var zipCodePattern = /\d{5}(-\d{4})?/;
          var zipCodeMatch = jQuery(this).val().match(zipCodePattern);
          if (zipCodeMatch) {
            jQuery(this).val(zipCodeMatch[0]);
          }
          // Clear autocomplete inputs if zipcode is empty.
          if (jQuery(this).val() == "") {
            administrativeAreaInput.val("");
            localityInput.val("");
          }
          clearTimeout(typingTimer);
          typingTimer = setTimeout(
            sendAjaxRequest(
              localityInput,
              administrativeAreaInput,
              postalCodeInput
            ),
            doneTypingInterval
          );
        });

        // Postalcode autocompleteselect event.
        jQuery(postalCodeInput).on("autocompleteselect", function (event, ui) {
          var inputString = ui.item.value;

          var parts = inputString.split(",").map(function (item) {
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
            if (jQuery(fieldset).find(localityInput).length) {
              localityInput = jQuery(fieldset).find(localityInput);
            }
            if (jQuery(fieldset).find(administrativeAreaInput).length) {
              administrativeAreaInput = jQuery(fieldset).find(
                administrativeAreaInput
              );
            }
            administrativeAreaInput.val(state);
            localityInput.val(city);
          }
        });

        administrativeAreaInput.change(function () {
          localityInput.val("");
          cityUpdate(localityInput, administrativeAreaInput, postalCodeInput);
        });

        jQuery(localityInput).on(
          "input",
          cityUpdate(localityInput, administrativeAreaInput, postalCodeInput)
        );
        jQuery(addressLine1).on(
          "input",
          addressUpdate(addressLine1, postalCodeInput, localityInput)
        );
      } else if (jQuery("form.commerce-checkout-flow").length > 0) {
        // Initializing input variables.
        const shippingPane = ".checkout-pane-shipping-information";
        const billingPane = ".checkout-pane-payment-information";

        jQuery(context).on(
          "click",
          shippingPane + ", " + billingPane,
          function (e) {
            // fieldset = e.target.closest('fieldset');
            fieldset = jQuery(this).closest("fieldset");

            countyField = jQuery(fieldset).find("select[id*=field-county]");
            postalCodeInput = jQuery(fieldset).find(
              'input[name$="[address][0][address][postal_code]"]'
            );
            localityInput = jQuery(fieldset).find(
              'input[name$="[address][0][address][locality]"]'
            );
            administrativeAreaInput = jQuery(fieldset).find(
              'select[name$="[address][0][address][administrative_area]"]'
            );
            addressLine1 = jQuery(fieldset).find(
              'input[name$="[address][0][address][address_line1]"]'
            );
            autocompleteFields = jQuery(fieldset).find(
              ".field--type-address .ui-autocomplete-input"
            );

            jQuery(autocompleteFields).on("autocompleteclose", function () {
              if (
                jQuery(this).val() == "No suggestions found." ||
                jQuery(this).val() == "Error while validating address."
              ) {
                jQuery(this).val("");
              }
            });

            // Postalcode autocompletes close event.
            jQuery(postalCodeInput).on(
              "autocompleteclose",
              function (event, ui) {
                // Pick only numeric value to set it as Zipcode.
                var zipCodePattern = /\d{5}(-\d{4})?/;
                var zipCodeMatch = jQuery(this).val().match(zipCodePattern);
                if (zipCodeMatch) {
                  jQuery(this).val(zipCodeMatch[0]);
                }
                // Clear autocomplete inputs if zipcode is empty.
                if (jQuery(this).val() == "") {
                  administrativeAreaInput.val("");
                  localityInput.val("");
                }
                clearTimeout(typingTimer);
                typingTimer = setTimeout(
                  sendAjaxRequest(
                    localityInput,
                    administrativeAreaInput,
                    postalCodeInput
                  ),
                  doneTypingInterval
                );
              }
            );

            // Postalcode autocompleteselect event.
            jQuery(postalCodeInput).on(
              "autocompleteselect",
              function (event, ui) {
                var inputString = ui.item.value;

                var parts = inputString.split(",").map(function (item) {
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
                  if (jQuery(fieldset).find(localityInput).length) {
                    localityInput = jQuery(fieldset).find(localityInput);
                  }
                  if (jQuery(fieldset).find(administrativeAreaInput).length) {
                    administrativeAreaInput = jQuery(fieldset).find(
                      administrativeAreaInput
                    );
                  }
                  administrativeAreaInput.val(state);
                  localityInput.val(city);
                }
              }
            );

            administrativeAreaInput.change(function () {
              localityInput.val("");
              cityUpdate(
                localityInput,
                administrativeAreaInput,
                postalCodeInput
              );
            });

            jQuery(localityInput).on(
              "input",
              cityUpdate(
                localityInput,
                administrativeAreaInput,
                postalCodeInput
              )
            );
            jQuery(addressLine1).on(
              "input",
              addressUpdate(addressLine1, postalCodeInput, localityInput)
            );
          }
        );
      }

      // // Initially hide county field.
      // if ($('[class*=field-county]').find('select').val() === '_none') {
      //   jQuery('[class*=field-county]').hide();
      // }

      // Update address autocomplete field.
      function addressUpdate(addressLine, postalCodeInput, localityInput) {
        var postalCodeValue = postalCodeInput.val();
        var localityValue = localityInput.val();

        var localityValueSubStr = localityValue
          .substring(0, 5)
          .replace(/\s+/g, "+");
        var sourceValue = localityValueSubStr + postalCodeValue;
        var currentPath = addressLine.attr("data-autocomplete-path");

        // Check if "contextKey" already exists in the data-autocomplete-path.
        if (currentPath.indexOf("contextKey=") === -1) {
          // "contextKey" doesn't exist, so add it.
          currentPath += "&contextKey=" + sourceValue;
        } else {
          // "contextKey" already exists, so update its value.
          currentPath = currentPath.replace(
            /contextKey=([^&]*)/,
            "contextKey=" + sourceValue
          );
        }

        // Update the autocomplete field's parameters.
        addressLine.attr("data-autocomplete-path", currentPath);
      }

      // Update city autocomplete field.
      function cityUpdate(
        localityInput,
        administrativeAreaInput,
        postalCodeInput
      ) {
        var administrativeAreaValue = administrativeAreaInput.val();
        var sourceValue = "state=" + administrativeAreaValue;

        var currentPath = localityInput.attr("data-autocomplete-path");
        if (typeof currentPath !== "undefined") {
          // Check if "state" already exists in the data-autocomplete-path.
          if (currentPath.indexOf("state=") === -1) {
            // "state" doesn't exist, so add it.
            currentPath += "&state=" + sourceValue;
          } else {
            // "state" already exists, so update its value.
            currentPath = currentPath.replace(
              /state=([^&]*)/,
              "state=" + administrativeAreaValue
            );
          }
        } else {
          currentPath = '/get-cities?state=A;;&q=""';
        }

        // Update the autocomplete field's parameters.
        localityInput.attr("data-autocomplete-path", currentPath);

        var inputValue = localityInput.val();

        // Get county value.
        clearTimeout(typingTimer);
        typingTimer = setTimeout(
          sendAjaxRequest(
            localityInput,
            administrativeAreaInput,
            postalCodeInput
          ),
          doneTypingInterval
        );
      }

      // Update county field.
      function sendAjaxRequest(
        localityInput,
        administrativeAreaInput,
        postalCodeInput
      ) {
        const city = localityInput.val();
        const state = administrativeAreaInput.val();
        const zipcode = postalCodeInput.val();

        const countyFieldWrapper = jQuery(localityInput)
          .parent()
          .siblings(".field--name-field-county");
        if (
          typeof jQuery(countyFieldWrapper).find(
            'select[name$="[field_county]"]'
          ) === "undefined"
        ) {
          const countyField = jQuery(countyFieldWrapper).find(
            'select[name="field_county"]'
          );
        } else {
          const countyField = jQuery(countyFieldWrapper).find(
            'select[name$="[field_county]"]'
          );
        }

        jQuery.ajax({
          url: "/get-county",
          data: {
            city,
            state,
            zipcode,
          },
          dataType: "json",
          success: function (response) {
            if (typeof response.county !== "undefined") {
              const currentSelectedValue = countyField.val();
              countyField.empty();
              // Iterate through the response.county array and create options
              jQuery.each(response.county, function (index, countyValue) {
                countyField.append(
                  jQuery("<option>", {
                    value: countyValue,
                    text: countyValue,
                  })
                );
                if (
                  currentSelectedValue &&
                  countyValue === currentSelectedValue
                ) {
                  countyField
                    .find('option[value="' + currentSelectedValue + '"')
                    .attr("selected", "selected");
                }
              });
              // jQuery('[class*=field-county]').show();
              // countyField.show();
              // Set the first option as selected
              if (countyField.find("option:selected").length === 0) {
                countyField.find("option:first").attr("selected", "selected");
              }
              // Handle change event for countyField
              countyField.on("change", function () {
                const selectedValue = countyField.val();
                countyField.find("option").each(function () {
                  if (countyField.val() === selectedValue) {
                    countyField.attr("selected", "selected");
                  } else {
                    jQuery(countyField).removeAttr("selected");
                  }
                });
              });
            } else if (response.error) {
              // jQuery('[class*=field-county]').hide();
              // countyField.hide();
              if (countyField.css("display") == "none") {
                countyField.find("option:selected").removeAttr("selected");
              }
            } else {
              countyField.empty();
            }
          },
          error: function (xhr, status, error) {
            console.error(error);
          },
        });
      }
    },
  };
})(jQuery, Drupal);
