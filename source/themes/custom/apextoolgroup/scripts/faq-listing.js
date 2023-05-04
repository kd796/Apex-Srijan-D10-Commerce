(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.faqAccordion = {
    attach: function (context, settings) {
      $('.faq-listing .faq-question').click(function () {
        $(this).parents('.faq-listing .views-row').toggleClass('active').siblings('.faq-listing .views-row').removeClass('active');
      });
    }
  };
})(jQuery, Drupal);
