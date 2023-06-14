jQuery(document).ready(function ($) {
  $('.rotate-point-trigger').on('click', function (e) {
    var container = $(this).parents('.feature-container');
    var target    = $(this).data('point');
    var frame     = $(this).data('frame');
    var video     = $('#rotate-point-' + target).find('video');
    var sprite    = container.find('.product-sprite');

    var currTrans      = $(sprite).css('transform').split(/[()]/)[1];
    var posX           = currTrans ? currTrans.split(',')[4] : 0;
    var containerWidth = container.find('.product-viewer').width();
    var countX         = Math.abs(posX) / containerWidth;
    var percent        = countX * 4.16666666666666667;
    var animDur        = countX * 50;

    // hide all rotate-point modals
    container.find('.rotate-point-inner').fadeOut(200);

    if ($(this).hasClass('no-video')) {
      if ($(this).hasClass('static')) {
        $('#rotate-point-' + target).fadeIn(200);
      } else {
        setTimeout(function() {
          $('#rotate-point-' + target).fadeIn(200);
        }, animDur);
      }

    } else {
      if ($(this).hasClass('static')) {
        $('#rotate-point-' + target).fadeIn(200);
        video.get(0).load();
        video.get(0).play();
      } else {
        setTimeout(function() {
          $('#rotate-point-' + target).fadeIn(200);
          video.get(0).load();
          video.get(0).play();
        }, animDur);
      }
    }
  });

  $('.rotate-point-inner').on('click', '.close', function (e) {
    $(e.delegateTarget).fadeOut(200);

    if (!$(e.delegateTarget).hasClass('no-video')) {
      var video = $(e.delegateTarget).find('video');

      setTimeout(function() {
        video.get(0).pause();
      }, 100);
    }
  });
});
