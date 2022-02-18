(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.torqueConversionCalculator = {
    attach: function (context, settings) {
      $('#block-torque-conversion-calculator-button-convert').once('torque-conversion-calculator').on('click', function () {
        var conversiondata = jQuery.parseJSON('{"ozf_in":{"ozf_in":1,"lbf_in":0.0625,"lbf_ft":0.00521,"mN_m":7.06,"cN_m":0.706,"N_m":0.00706,"gf_cm":72,"kgf_cm":0.072,"kgf_m":0.00072},"lbf_in":{"ozf_in":16,"lbf_in":1,"lbf_ft":0.0833,"mN_m":113,"cN_m":11.3,"N_m":0.113,"gf_cm":1150,"kgf_cm":1.15,"kgf_m":0.0115},"lbf_ft":{"ozf_in":192,"lbf_in":12,"lbf_ft":1,"mN_m":1360,"cN_m":136,"N_m":1.36,"gf_cm":13800,"kgf_cm":13.8,"kgf_m":0.138},"mN_m":{"ozf_in":0.142,"lbf_in":0.00885,"lbf_ft":0.000738,"mN_m":1,"cN_m":0.1,"N_m":0.001,"gf_cm":10.2,"kgf_cm":0.0102,"kgf_m":0.000102},"cN_m":{"ozf_in":1.42,"lbf_in":0.0885,"lbf_ft":0.00738,"mN_m":10,"cN_m":1,"N_m":0.01,"gf_cm":102,"kgf_cm":0.102,"kgf_m":0.00102},"N_m":{"ozf_in":142,"lbf_in":8.85,"lbf_ft":0.738,"mN_m":1000,"cN_m":100,"N_m":1,"gf_cm":10200,"kgf_cm":10.2,"kgf_m":0.102},"gf_cm":{"ozf_in":0.0139,"lbf_in":0.000868,"lbf_ft":7.23e-5,"mN_m":0.0981,"cN_m":0.00981,"N_m":9.81e-5,"gf_cm":1,"kgf_cm":0.001,"kgf_m":1.0e-5},"kgf_cm":{"ozf_in":13.9,"lbf_in":0.868,"lbf_ft":0.0723,"mN_m":98.1,"cN_m":9.81,"N_m":0.0981,"gf_cm":1000,"kgf_cm":1,"kgf_m":0.01},"kgf_m":{"ozf_in":1390,"lbf_in":86.8,"lbf_ft":7.23,"mN_m":9810,"cN_m":981,"N_m":9.81,"gf_cm":100000,"kgf_cm":100,"kgf_m":1}}');

        var conversionInput = jQuery('#conversionInput').val();
        var conversionType = jQuery('#conversipnType').val();
        var conversionarray = [];

        if (isNaN(parseFloat(conversionInput, 10))) {
          alert('Please enter the number you want to convert');
          return false;
        }

        conversionarray = conversiondata[conversionType];

        jQuery.each(conversionarray, function (index, value) {
          var conversion = conversionInput * value;
          jQuery('#' + index + '_input').val(conversion);
        });
      });

      $('#block-torque-conversion-calculator-button-reset').on('click', function () {
        $('input[type="text"]').each(function () {
          $(this).val('0');
        });
      });
    }
  };

})(jQuery, Drupal);
