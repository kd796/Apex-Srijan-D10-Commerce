jQuery(document).ready(function($) {
  if ($('.atg_callout .atg_callout_content').length) {
    var content = $('.atg_callout .atg_callout_content'),
        width = content.width(),
        height = content.height();
    content.data('width', width).data('height', height);

    $(document).on('DOMNodeInserted', function (e) {
      if ($(e.target).hasClass('usabilla_live_button_container')) {
        $('.usabilla_live_button_container').css('cssText', 'position:fixed; right: 0; z-index: 999; width: 30px; height: 180px; top: 40%; transform: translateY(-50%);');
      }
    });
  }
});
