(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.branddetail = {
    attach: function (context, settings) {
      $('.brand-detail .brand-detail__tabs .tabs li a').click(function (event) {
        event.preventDefault();
        var $tab = $(this).attr('href');
        $($tab).addClass('is-active').siblings().removeClass('is-active');
        $(this).parent('li').addClass('is-active').siblings('li').removeClass('is-active');
      });
    }
  };
})(jQuery, Drupal);
