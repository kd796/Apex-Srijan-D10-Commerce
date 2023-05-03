(function ($) {
  'use strict';
  Drupal.behaviors.mySliderBehavior = {
    attach: function (context, settings) {

      var brandTimelineSlider = $('.brand-timeline-content');
      var windowWidth = window.innerWidth;
      var mobileBreakpoint = 766.9;
      var tabletBreakpoint = 959;

      // Set the selected year for dropdown.
      var seletedLabel = $('.selected-year-display .year-label');
      var checkedValueLabel = $('.brand-timeline-years.select-view .form-radios .form-radio:checked').siblings('label').text();
      seletedLabel.text(checkedValueLabel);
      // Initialize the slider based on width.
      if (windowWidth < mobileBreakpoint) {
        brandTimelineSlider.not('.slick-initialized').slick({
          infinite: false,
          centerMode: false,
          speed: 300,
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: false
        });
      }
      else if (windowWidth > mobileBreakpoint && windowWidth < tabletBreakpoint) {
        brandTimelineSlider.not('.slick-initialized').slick({
          infinite: false,
          centerMode: false,
          speed: 300,
          slidesToShow: 3,
          slidesToScroll: 3,
          arrows: false
        });
      }
      else {
        brandTimelineSlider.not('.slick-initialized').slick({
          infinite: false,
          centerMode: false,
          speed: 300,
          slidesToShow: 4,
          slidesToScroll: 4,
          arrows: false,
          margin: 16
        });
      }

      // Custom slider arrow.
      $('.next-arrow').on('click', function () {
        brandTimelineSlider.slick('slickNext');
      });
      $('.prev-arrow').on('click', function () {
        brandTimelineSlider.slick('slickPrev');
      });
      // Fix the random seclect on ajax.
      function ajaxfix() {
        var lastRadioBtnLabel = $('.form-radios .form-type-radio:last-child label');
        $(document).ajaxStart(function () {
          lastRadioBtnLabel.addClass('ajax-load-class');
          // Slide effect on trigger.
          if ($('.slick-track .slick-slide').first().hasClass('slick-active')) {
            $('.slick-track').removeClass('slide').addClass('slide-prev');
          }
          else {
            $('.slick-track').removeClass('slide-prev').addClass('slide');
          }
        });
        $(document).ajaxComplete(function () {
          lastRadioBtnLabel.removeClass('ajax-load-class');
        });
      }
      // After trigger add and remove class.
      function afterTrigger() {
        // Remove and add classes after triger.
        var firstSlide = $('.slick-track .slick-slide:first-child');
        var lastSlide = $('.slick-track .slick-slide:last-child');
        if (firstSlide.hasClass('slick-active')) {
          lastSlide.removeClass('next-trigger');
          $('.slick-next').removeClass('next-trigger');
          firstSlide.addClass('prev-trigger');
          $('.slick-prev').addClass('prev-trigger');
        }
        if (lastSlide.hasClass('slick-active')) {
          firstSlide.removeClass('prev-trigger');
          $('.slick-prev').removeClass('prev-trigger');
          lastSlide.addClass('next-trigger');
          $('.slick-next').addClass('next-trigger');
        }
      }
      // Check slider change.
      // Set first and last slide.
      var firstSlide = $('.slick-track .slick-slide:first-child');
      var lastSlide = $('.slick-track .slick-slide:last-child');
      var firstRadioVal = 0;
      var lastRadioVal = $('.brand-timeline-years.tab-view .form-radios .form-type-radio').length - 1;
      // Triggering previous and next year on single click for less slide .
      var slideCount = $('.slick-track .slick-slide').length;
      if (windowWidth < mobileBreakpoint) {
        if (slideCount <= 1) {
          $('.slick-prev').addClass('prev-trigger');
          $('.slick-next').addClass('next-trigger');
        }
      }
      else if (windowWidth > mobileBreakpoint && windowWidth < tabletBreakpoint) {
        if (slideCount === 3) {
          $('.slick-prev').addClass('prev-trigger');
          $('.slick-next').addClass('next-trigger');
        }
      }
      else {
        if (slideCount <= 4) {
          $('.slick-prev').addClass('prev-trigger');
          $('.slick-next').addClass('next-trigger');
        }
      }
      // Triggering previous year.
      var PrevArrow = $('.slick-prev');
      PrevArrow.once('triggerprev').on('click', function () {
        if (PrevArrow.hasClass('prev-trigger')) {
          var checkedValue = $('.form-radios .form-radio:checked').val();
          var checkedValueF = parseInt(checkedValue) - 1; // Use parseInt to convert checkedValue to a number
          if (!(checkedValueF < firstRadioVal)) {
            ajaxfix();
            $('input:radio').attr('value', checkedValueF).trigger('click');
            afterTrigger();
          }
        }
      });
      // Triggering next year.
      var NextArrow = $('.slick-next');
      NextArrow.once('triggernext').on('click', function () {
        if (NextArrow.hasClass('next-trigger')) {
          var checkedValue = $('.form-radios input[name="field_event_date_value"]:checked').val();
          var checkedValueF = parseInt(checkedValue) + 1; // Use parseInt to convert checkedValue to a number
          if (!(checkedValueF > lastRadioVal)) {
            ajaxfix();
            $('input:radio').attr('value', checkedValueF).trigger('click');
            afterTrigger();
          }
        }
      });
      $('.slick-prev').addClass('prev-trigger');
      $('.slick-prev, .slick-next').once('sliderChange').on('click', function () {
        $('.slick-prev').removeClass('prev-trigger');
        // Clicked Prev button.
        if ($(this).hasClass('slick-prev')) {
          if (firstSlide.hasClass('slick-active')) {
            firstSlide.addClass('prev-trigger');
            $('.slick-prev').addClass('prev-trigger');
            lastSlide.removeClass('next-trigger');
            $('.slick-next').removeClass('next-trigger');
          }
        }
        // Clicked Next button.
        if ($(this).hasClass('slick-next')) {
          if (lastSlide.hasClass('slick-active')) {
            lastSlide.addClass('next-trigger');
            $('.slick-next').addClass('next-trigger');
            firstSlide.removeClass('prev-trigger');
            $('.slick-prev').removeClass('prev-trigger');
          }
        }
      });
      // Responsive select view of radio button.
      function menuGroup() {
        $('body').click(function () {
          if ($('.menu-trigger').hasClass('active')) {
            $('.menu-trigger').removeClass('active');
          }
          if ($('.menu-dropdown').hasClass('open')) {
            $('.menu-dropdown').removeClass('open');
          }
        });
        // Open menu
        $('.menu-trigger').once('trigger').click(function (event) {
          event.preventDefault();
          event.stopPropagation();
          $(this).toggleClass('active');
          $(this).next('.menu-dropdown').toggleClass('open');
        });
      }
      menuGroup();
      // Hide arrows when slider reaches last and first slide.
      function disableArrow() {
        if ($('.brand-timeline-years.select-view .form-radios .form-radio').first().is(':checked')) {
          if ($('.slick-track .slick-slide').first().hasClass('slick-active')) {
            $('.slick-prev').addClass('disabled');
          }
          else {
            $('.slick-prev').removeClass('disabled');
          }
        }

        if ($('.brand-timeline-years.select-view .form-radios .form-radio').last().is(':checked')) {
          if ($('.slick-track .slick-slide').last().hasClass('slick-active')) {
            $('.slick-next').addClass('disabled');
          }
          else {
            $('.slick-next').removeClass('disabled');
          }
        }
      }
      disableArrow();
      brandTimelineSlider.on('afterChange', function (event, slick, currentSlide, nextSlide) {
        disableArrow();
      });
    }
  };
})(jQuery);
