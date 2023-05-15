(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.apextoolgroup = {
    attach: function (context, settings) {
      $('.application-form form .form-item input, .application-form form .form-item select').focusout(function () {
        var text_val = $(this).val();
        if (text_val === '') {
          $(this).removeClass('has-value');
        }
        else {
          $(this).addClass('has-value');
        }
      });

      $('.application-form form .form-item textarea').focusout(function () {
        var text_val = $(this).val();
        if (text_val === '') {
          $(this).parent().removeClass('has-value');
        }
        else {
          $(this).parent().addClass('has-value');
        }
      });
    }
  };
})(jQuery, Drupal);
