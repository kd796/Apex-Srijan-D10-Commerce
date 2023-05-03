(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.faqAccordion = {
    attach: function (context, settings) {
      $('.faq-question').click(function () {
        $(this).toggleClass('active');
        $(this).next('.faq-answer').slideToggle(300);
      });
    }
  };

})(jQuery, Drupal);
