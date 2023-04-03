(function ($) {
  'use strict';
  Drupal.behaviors.mySliderBehavior = {
    attach: function (context, settings) {

      var brandTimelineSlider = $('.brand-timeline-content');
      var windowWidth = window.innerWidth;
      var tabletBreakpoint = 766.9;

      // Set the selected year for dropdown.
      var seletedLabel = $('.selected-year-display .year-label');
      var checkedValue = $('.brand-timeline-years.select-view .form-radios .form-radio:checked').siblings('label').text();
      seletedLabel.text(checkedValue);

      // Initialize the slider based on width.
      if (windowWidth < tabletBreakpoint) {
        brandTimelineSlider.not('.slick-initialized').slick({
          infinite: false,
          centerMode: false,
          speed: 300,
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: false
        });
      }
      else {
        brandTimelineSlider.not('.slick-initialized').slick({
          infinite: false,
          centerMode: false,
          speed: 300,
          slidesToShow: 2,
          slidesToScroll: 2,
          arrows: false
        });
      }

      // add a window resize event listener to update the actions when the window size changes
      $(window).on('resize', _.debounce(function () {
        windowWidth = window.innerWidth;
        if (windowWidth < tabletBreakpoint) {
          brandTimelineSlider.slick('unslick');
          brandTimelineSlider.not('.slick-initialized').slick({
            infinite: false,
            centerMode: false,
            speed: 300,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false
          });
        }
        else {
          brandTimelineSlider.slick('unslick');
          brandTimelineSlider.not('.slick-initialized').slick({
            infinite: false,
            centerMode: false,
            speed: 300,
            slidesToShow: 2,
            slidesToScroll: 2,
            arrows: false
          });
        }
      }, 250));

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
      $('.slick-prev, .slick-next').once('sliderChange').on('click', function () {
        // Clicked Prev button.
        if ($(this).hasClass('slick-prev')) {
          if (firstSlide.hasClass('slick-active')) {
            firstSlide.addClass('prev-trigger');
            $('.slick-prev').addClass('prev-trigger');
            lastSlide.removeClass('next-trigger');
            $('.slick-next').removeClass('next-trigger');
          }
          if (firstSlide.hasClass('slick-active prev-trigger')) {
            $('.slick-prev.prev-trigger').once('triggerprev').on('click', function () {
              var checkedValue = $('.form-radios .form-radio:checked').val();
              var checkedValueF = parseInt(checkedValue) - 1; // Use parseInt to convert checkedValue to a number
              if (!(checkedValueF < firstRadioVal)) {
                ajaxfix();
                $('input:radio').attr('value', checkedValueF).trigger('click');
                afterTrigger();
              }
            });
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
          if (lastSlide.hasClass('slick-active next-trigger')) {
            $('.slick-next.next-trigger').once('triggernext').on('click', function () {
              var checkedValue = $('.form-radios input[name="field_event_date_value"]:checked').val();
              var checkedValueF = parseInt(checkedValue) + 1; // Use parseInt to convert checkedValue to a number
              if (!(checkedValueF > lastRadioVal)) {
                ajaxfix();
                $('input:radio').attr('value', checkedValueF).trigger('click');
                afterTrigger();
              }
            });
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

    }
  };
})(jQuery);
