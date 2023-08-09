jQuery(document).ready(function () {
  var jQuerycontentlist = jQuery('.hubspot-tabs-content li');
  var jQuerytabslist = jQuery('.hubspot-tabs li');

  jQuery(once('hubspot-tabs', '.hubspot-tabs')).on('click', 'li', function (e) {
    var jQuerycurrent = jQuery(this);
    var index = jQuerycurrent.index();
    if (jQuerycurrent.hasClass('active-tab')) {
      jQuerycurrent.removeClass('active-tab');
    } else {
      jQuerytabslist.removeClass('active-tab');
      jQuerycurrent.addClass('active-tab');
    }

    if (jQuerycontentlist.eq(index).hasClass('active')) {
      jQuerycontentlist.eq(index).removeClass('active');
    } else {
      jQuerycontentlist.removeClass('active');
      jQuerycontentlist.eq(index).addClass('active');
      var target = jQuery(jQuery(this).find('.checkable-card').attr('href'));
      if (target.length) {
        jQuery('html, body').animate({
          scrollTop: target.offset().top
        }, 500);
      }
    }
    return false;
  });
  // Opening a form based on ID.
  if(window.location.hash !== '') {
    var cardID = window.location.hash;
    jQuery('a[href*="' + cardID + '"]').parent().trigger('click');
  }
  
});
