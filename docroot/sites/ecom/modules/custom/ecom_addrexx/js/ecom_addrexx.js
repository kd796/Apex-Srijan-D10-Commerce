(function ($) {
  Drupal.behaviors.ecom_addrexx = {
    attach: function (context) {
      // Watch for changes in the source field.
      jQuery('input[name^="address["][name$="][address][postal_code]"]', context).on('autocompleteselect', function () {
        var inputValue = jQuery(this).val();
        var zipCodePattern = /\d{5}(-\d{4})?/;
        var zipCodeMatch = inputValue.match(zipCodePattern);
        console.log("zipCodeMatch " +zipCodeMatch);
        if (zipCodeMatch) {
          // Extract the ZIP code from the match.
          var zipCode = zipCodeMatch[0];
          jQuery(this).val(zipCode);
        }
        console.log("value " +jQuery(this).val());
      });
      jQuery('input[name^="address["][name$="][address][address_line1]"]', context).on('input', function () {
        var postalCodeValue = jQuery(this).closest('form').find('input[name^="address["][name$="][postal_code]"]').val();
        var localityValue = jQuery(this).closest('form').find('input[name^="address["][name$="][locality]"]').val();
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
      });
    }
  };
})(jQuery);
