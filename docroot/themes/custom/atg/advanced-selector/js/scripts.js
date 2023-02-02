jQuery(document).ready(function($) {
  $('#businessform').validate();
  $('#emailform').validate();

  // fix for drupal core forms.js error
  $(window).off('hashchange.form-fragment');

  // tab control
  $('input, select', '.sel-form').on('keydown', function(event) {
    if (event.which == 9 && $(this).is('input') && $(this).prev().is('select')) {
      $(this).prev().focus(); // from input -> select
    }
  });
});
