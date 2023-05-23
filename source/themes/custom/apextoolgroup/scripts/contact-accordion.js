(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.contactAccordion = {
    attach: function (context, settings) {
      // accordions
      $('.accordion-title').once('accordion').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $(this)
            .siblings('.accordion-body')
            .slideUp(200);
        }
        else {
          $('.accordion-title').removeClass('active');
          $(this).addClass('active');
          $('.accordion-body').slideUp(200);
          $(this)
            .siblings('.accordion-body')
            .slideDown(200);
        }
      });

    }
  };
})(jQuery, Drupal);
