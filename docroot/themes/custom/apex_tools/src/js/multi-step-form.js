(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.multiStepForm = {
    attach: function (context, settings) {
      $(window, context).once('multiStepForm').on('load', function () {

        // var step1 = $('#edit-group-business-information').get(0).outerHTML;
        // var step1_action = $('#edit-actions').get(0).outerHTML;
        // $('.multi-steps-label .step-label:first-child').append(step1, step1_action);
        // $('.multi-steps-label #edit-group-business-information, .multi-steps-label #edit-actions').addClass('show');
        // $('#edit-group-business-information, #edit-actions').addClass('hide');
        // $('input[name="price"]').change(function () {
        //     if($("#value_radio").is(':checked')) {
        //         $('#value_input').attr('required', true);
        //     } else {
        //         $('#value_input').removeAttr('required');
        //     }
        // });
      });

      $('.multi-steps-label').once('multi-steps-label').on('click', '.step-label', function (e) {
        console.log('working');
        var $current = $(this);
        var index = $current.index();
        console.log(index);
        var $next = $(this).closest('.step-label').next('.step-label');
        if($next.hasClass('active')) {
          console.log('perfect');
          $('#edit-back-button').trigger('click');
        }
        return false;
      });

      // end
    }
  };
})(jQuery, Drupal);

