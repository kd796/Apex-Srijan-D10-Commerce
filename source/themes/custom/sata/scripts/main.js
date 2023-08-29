(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.sata = {
    attach: function (context, settings) {
      // Form error on submit button click
      $(once('webform-submission-form', '.webform-submission-form', context)).each(function (index) {
        // Initialize variables.
        var $widget = $(this);
        var $submitButton = $($widget).find('.webform-button--submit');

        // Create isValid() jQuery function
        $.fn.isValid = function () {
          return this[0].checkValidity();
        };

        // Set error class
        $($submitButton).on('click', function () {
          if (!$($widget).isValid()) {
            $($widget).addClass('form-error');
          }
        });
      });

      function focusKeyDown(e) {
        if ([9, 13, 32, 38, 40].indexOf(e.keyCode) !== -1) {
          $('body').addClass('keyboard-activated');
          $(window).on('mousedown.keyboard', focusMouseDown);
          $(window).off('keydown.keyboard');
        }
      }

      function focusMouseDown(e) {
        $('body').removeClass('keyboard-activated');
        $(window).on('keydown.keyboard', focusKeyDown);
        $(window).off('mousedown.keyboard');
      }

      $(window).on('keydown.keyboard', focusKeyDown);

      // Remove all svg IDs.
      $('svg').removeAttr('id');

      // Toggle class on body for if modal is open.
      $(window).on({
        'dialog:aftercreate': function (dialog, $element, settings) {
          $('body').addClass('ui-dialog-open');
        },
        'dialog:afterclose': function (dialog, $element, settings) {
          $('body').removeClass('ui-dialog-open');
        }
      });
    }
  };
  // Add this observer for Buy Now button.
  // Buy Now button is legacy issue.
  // TO Fix Price Spider(Buy Now not rendering).
  const options = {
    root: null,
    threshold: 0.5
  };
  const observePSwidget = function (entries, observer) {
    entries.forEach((entry) => {
      window.PriceSpider.rebind();
    });
  };
  const observer = new IntersectionObserver(observePSwidget, options);
  const elementsToObserve = document.querySelectorAll('.ps-widget');
  elementsToObserve.forEach((element) => {
    observer.observe(element);
  });
})(jQuery, Drupal, once);
