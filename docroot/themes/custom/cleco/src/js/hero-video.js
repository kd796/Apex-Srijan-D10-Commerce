(function($) {
  var heroVideoContainer = $('.hero.hero-enhanced--video');
  var heroVideoElem      = $('video', heroVideoContainer);
  var heroBanner = $('.hero--large .hero-image');
  var videoBannerText = $('.hero-video-banner-text');
  if(heroBanner.hasClass('video-section')) {
    videoBannerText.addClass('dispblock');
  }
  heroVideoElem.on('loadeddata', function() {
    heroVideoElem.addClass('is-ready');
  });

  heroVideoElem.on('play', function () {
    if (!heroVideoElem.hasClass('is-ready')) {
      heroVideoElem.addClass('is-ready');
    }
  });

  heroVideoElem.on('ended', function() {
    this.currentTime = this.duration;
    this.pause();

    $('.hero.hero-enhanced--video').addClass('is-animated');
  });
})(jQuery);

function generateThumbnail(video) {
  var w = video.videoWidth;
  var h = video.videoHeight;

  video.currentTime = video.duration;

  // generate thumbnail URL data
  var canvas = document.createElement('canvas');
      canvas.width  = w;
      canvas.height = h;
  var context = canvas.getContext('2d');
      context.drawImage(video, 0, 0, w, h);
  var dataUrl = canvas.toDataURL();

  video.currentTime = 0;
  // video.play();

  return dataUrl;
}

