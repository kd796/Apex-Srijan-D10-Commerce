(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.faqAccordion = {
    attach: function (context, settings) {
      // accordions
      $(once('accordion', '.faq-question', context)).on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $(this)
            .siblings('.faq-answer')
            .slideUp(200);
        }
        else {
          $('.faq-question').removeClass('active');
          $(this).addClass('active');
          $('.faq-answer').slideUp(200);
          $(this)
            .siblings('.faq-answer')
            .slideDown(200);
        }
      });

    }
  };
})(jQuery, Drupal, once);
