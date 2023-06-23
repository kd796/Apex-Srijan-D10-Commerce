(function ($) {
  var $contentlist = $('.hubspot-tabs-content li');
  var $tabslist = $('.hubspot-tabs li');

  $('.hubspot-tabs').once('hubspot-tabs').on('click', 'li', function (e) {
    var $current = $(this);
    var index = $current.index();

    if ($current.hasClass('active-tab')) {
      $current.removeClass('active-tab');
    } else {
      $tabslist.removeClass('active-tab');
      $current.addClass('active-tab');
    }

    if ($contentlist.eq(index).hasClass('active')) {
      $contentlist.eq(index).removeClass('active');
    } else {
      $contentlist.removeClass('active');
      $contentlist.eq(index).addClass('active');
      var target = $($(this).find('.checkable-card').attr('href'));
      if (target.length) {
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 500);
      }
    }
    return false;
  });

  // end
})(jQuery);
