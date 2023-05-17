(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.apextoolgroup = {
    attach: function (context, settings) {
      function inputValidation(ele) {
        var text_val = $(ele).val();
        if (text_val === '') {
          $(ele).removeClass('has-value');
        }
        else {
          $(ele).addClass('has-value');
        }
      }

      $('.application-form form .form-item input, .application-form form .form-item select').focusout(function (e) {
        inputValidation(this);
      });

      function textareaValidation(ele) {
        var text_val = $(ele).val();
        if (text_val === '') {
          $(ele).parent().removeClass('has-value');
        }
        else {
          $(ele).parent().addClass('has-value');
        }
      }

      $('.application-form form .form-item textarea').focusout(function (e) {
        textareaValidation(this);
      });

      $('.application-form form #edit-actions-submit').on('submit', function (e) {
        e.preventDefault();
        inputValidation('.application-form form .form-item input, .application-form form .form-item select');
        textareaValidation('.application-form form .form-item textarea');
      });

      var validated = false;
      $('.application-form form input').each(function (e) {
        if ($(this).hasClass('error')) {
          validated = true;
        }
      });

      $('.application-form form input').each(function (e) {
        if (validated === true) {
          inputValidation(this);
        }
      });

      $('.application-form form select').each(function (e) {
        if (validated === true) {
          inputValidation(this);
        }
      });

      $('.application-form form textarea').each(function (e) {
        if (validated === true) {
          textareaValidation(this);
        }
      });

    }
  };
})(jQuery, Drupal);
