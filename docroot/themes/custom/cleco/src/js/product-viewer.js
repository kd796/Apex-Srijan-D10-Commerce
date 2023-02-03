jQuery(document).ready(function($) {

  var productViewer = function(element) {
    this.element         = element;
    this.handleContainer = this.element.find('.cd-product-viewer-handle');
    this.handleFill      = this.handleContainer.children('.fill');
    this.handle          = this.handleContainer.children('.handle');
    this.imageWrapper    = this.element.find('.product-viewer');
    this.slideShow       = this.imageWrapper.children('.product-sprite');
    this.frames          = this.element.data('frames');
    //increase this value to increase the friction while dragging on the image - it has to be bigger than zero
    this.friction        = this.element.data('friction');
    this.visibleFrame    = 0;
    this.loaded          = false;
    this.animating       = false;
    this.xPosition       = 0;
    this.yPosition       = 0;
    this.loadFrames();
  }

  productViewer.prototype.loadFrames = function() {
    var self   = this,
      imageUrl = this.slideShow.data('image'),
      newImg   = $('<img/>');
    this.loading('0.5');
    this.slideShow.css('background-image', 'url('+imageUrl+')');

    //you need this to check if the image sprite has been loaded
    newImg.attr('src', imageUrl).one('load', function() {
      $(this).remove();
      self.loaded = true;
    }).each(function() {
      image = this;
      if (image.complete) {
        $(image).trigger('load');
      }
    });
  }

  productViewer.prototype.loading = function(percentage) {
    var self = this;
    transformElement(this.handleFill, 'scaleX(' + percentage + ')');
    setTimeout(function() {
      if (self.loaded) {
        //sprite image has been loaded
        self.element.addClass('loaded').data('visible-frame', 0);
        transformElement(self.handleFill, 'scaleX(1)');
        self.dragImage();
        if (self.handle) self.dragHandle();

        $('.loading-image').animate({
          opacity: 0
        }, 500);
      } else {
        //sprite image has not been loaded - increase self.handleFill scale value
        var newPercentage = parseFloat(percentage) + .1;
        self.loading(newPercentage < 1 ? newPercentage : parseFloat(percentage));
      }
    }, 500);
  }

  //draggable funtionality - credits to http://css-tricks.com/snippets/jquery/draggable-without-jquery-ui/
  productViewer.prototype.dragHandle = function() {
    //implement handle draggability
    var self = this;
    self.handle.on('mousedown vmousedown touchstart', function (e) {
      // console.log(e);
      // var parentContainer = $(this).parents('.cd-product-viewer-wrapper');
      // parentContainer.addClass('no-points');

      self.handle.addClass('cd-draggable');
      var dragWidth      = self.handle.outerWidth(),
        dragHeight       = self.handle.outerHeight(),
        containerOffset  = self.handleContainer.offset().left,
        containerOffsetY = self.handleContainer.offset().top,
        containerWidth   = self.handleContainer.outerWidth(),
        containerHeight  = self.handleContainer.outerHeight(),
        minLeft          = containerOffset - dragWidth / 2,
        maxLeft          = containerOffset + containerWidth - dragWidth / 2,
        minTop           = containerOffsetY - dragHeight / 2,
        maxTop           = containerOffsetY + containerHeight - dragHeight / 2;

      var pageX = e.type == 'touchstart' ? e.touches[0].pageX : e.pageX;

      self.xPosition = self.handle.offset().left + dragWidth - pageX;

      self.element.on('mousemove vmousemove touchmove', function (e) {
        // console.log(e);
        if (!self.animating) {
          self.animating = true;
          (!window.requestAnimationFrame) ?
          setTimeout(function() {
            self.animateDraggedHandle(e, { dragWidth, containerOffset, containerWidth, minLeft, maxLeft }, { dragHeight, containerOffsetY, containerHeight, minTop, maxTop });
          }, 100): requestAnimationFrame(function() {
            self.animateDraggedHandle(e, { dragWidth, containerOffset, containerWidth, minLeft, maxLeft }, { dragHeight, containerOffsetY, containerHeight, minTop, maxTop });
          });
        }
      }).one('mouseup vmouseup touchend', function (e) {
        // console.log(e);
        self.handle.removeClass('cd-draggable');
        self.element.off('mousemove vmousemove touchmove');
      });

      e.preventDefault();

    }).on('mouseup vmouseup touchend', function (e) {
      // console.log(e);
      self.handle.removeClass('cd-draggable');
    });
  }

  productViewer.prototype.animateDraggedHandle = function(e, x, y) {
    const { dragWidth, containerOffset, containerWidth, minLeft, maxLeft } = x;
    const { dragHeight, containerOffsetY, containerHeight, minTop, maxTop } = y;
    var self      = this;
    var pageX     = e.type == 'touchmove' ? e.touches[0].pageX : e.pageX;
    var leftValue = pageX + self.xPosition - dragWidth;
    // constrain the draggable element to move inside his container
    if (leftValue < minLeft) {
      leftValue = minLeft;
      var container = self.element;
      //$(container).removeClass('no-points');
    } else if (leftValue > maxLeft) {
      leftValue = maxLeft;
    }

    var widthValue    = Math.ceil((leftValue + dragWidth / 2 - containerOffset) * 1000 / containerWidth) / 10;
    self.visibleFrame = Math.ceil((widthValue * (self.frames - 1)) / 100);

    //update image frame
    self.updateFrame();
    //update handle position
    // var container = self.element;
    // if (self.visibleFrame === 0) {
    //   $(container).removeClass('no-points');
    // } else {
    //   $(container).addClass('no-points');
    // }

    var ypos = widthValue >= 50 ? 100 - widthValue : widthValue;

    $('.cd-draggable', self.handleContainer).css({
      'left': widthValue + '%',
      'top': ypos + '%'
    }).one('mouseup vmouseup touchend', function () {
      // console.log(e);
      $(this).removeClass('cd-draggable');
    });

    self.animating = false;
  }

  productViewer.prototype.dragImage = function() {
    //implement image draggability
    var self = this;
    self.slideShow.on('mousedown vmousedown touchstart', function (e) {
      // console.log(e);
      // var parentContainer = $(this).parents('.cd-product-viewer-wrapper');
      // parentContainer.addClass('no-points');

      self.slideShow.addClass('cd-draggable');
      var containerOffset = self.imageWrapper.offset().left,
        containerOffsetY  = self.imageWrapper.offset().top,
        containerWidth    = self.imageWrapper.outerWidth(),
        containerHeight   = self.imageWrapper.outerHeight(),
        minFrame          = 0,
        maxFrame          = self.frames - 1;

      self.xPosition = e.type == 'touchstart' ? e.touches[0].pageX : e.pageX;
      self.yPosition = e.type == 'touchstart' ? e.touches[0].pageY : e.pageY;

      self.element.on('mousemove vmousemove touchmove', function (e) {
        // console.log(e);
        if (!self.animating) {
          self.animating = true;
          (!window.requestAnimationFrame) ?
          setTimeout(function() {
            self.animateDraggedImage(e, containerOffset, containerWidth, containerOffsetY, containerHeight);
          }, 100): requestAnimationFrame(function() {
            self.animateDraggedImage(e, containerOffset, containerWidth, containerOffsetY, containerHeight);
          });
        }
      }).one('mouseup vmouseup touchend', function(e) {
        // console.log(e);
        self.slideShow.removeClass('cd-draggable');
        self.element.off('mousemove vmousemove touchmove');
        self.updateHandle();
      });

      e.preventDefault();

    }).on('mouseup vmouseup touchend', function(e) {
      // console.log(e);
      self.slideShow.removeClass('cd-draggable');
    });
  }

  productViewer.prototype.animateDraggedImage = function(e, containerOffset, containerWidth, containerOffsetY, containerHeight) {
    var pageX    = e.type == 'touchmove' ? e.touches[0].pageX : e.pageX;
    var self     = this,
      leftValue  = self.xPosition - pageX,
      widthValue = Math.ceil((leftValue) * 100 / (containerWidth * self.friction)),
      frame      = (widthValue * (self.frames - 1)) / 100;
      frame      = frame > 0 ? Math.floor(frame) : Math.ceil(frame);
    var newFrame = self.visibleFrame + frame;

    if (newFrame < 0) {
      newFrame = self.frames - 1;
    } else if (newFrame > self.frames - 1) {
      newFrame = 0;
    }

    if (newFrame != self.visibleFrame) {
      self.visibleFrame = newFrame;
      self.updateFrame();
      self.xPosition = pageX;
    }

    self.animating = false;
  }

  productViewer.prototype.updateHandle = function() {
    if (this.handle) {
      var ypos = widthValue >= 50 ? 100 - widthValue : widthValue;

      var widthValue = 100 * this.visibleFrame / this.frames;
      this.handle.animate({
        'left': widthValue + '%',
        'top': ypos + '%'
      }, 200);
    }
  }

  productViewer.prototype.updateFrame = function() {
    $(this.element).attr('data-visible-frame', this.visibleFrame);
    $(this.element).find('.rotate-point-inner').fadeOut(200);

    var transformValue = -(100 * this.visibleFrame / this.frames);
    transformElement(this.slideShow, 'translateX(' + transformValue + '%)');
  }

  function transformElement(element, value) {
    element.css({
      '-moz-transform': value,
      '-webkit-transform': value,
      '-ms-transform': value,
      '-o-transform': value,
      'transform': value,
    });
  }

  $('.cd-product-viewer-wrapper').each(function() {
    new productViewer($(this));
  });

  setInterval(function() {
    if($('.feature-container').hasClass('explore-360') && !$('.cd-product-viewer-wrapper').hasClass('loaded')) {
      $('.cd-product-viewer-wrapper').each(function() {
        new productViewer($(this));
      });
    }
  }, 500);
});
