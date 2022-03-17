(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productDetailImageSlider = {
    attach: function (context, settings) {
      $('.product-detail-slider:not(.product-detail-slider--js-initialized)').each(function (index) {
        // Main Slider.
        var $component = $(this);
        var $sliderContainer = $component.find('.product-detail-slider__container');
        var $sliderWrapper = $sliderContainer.find('.field--name-field-product-images');
        var $sliderItems = $sliderContainer.find('.field--name-field-product-images .field__item');
        var $sliderButtonPrev = $component.find('.swiper-button-prev');
        var $sliderButtonNext = $component.find('.swiper-button-next');

        // Thumb Slider
        var $thumbsContainer = $component.find('.product-detail-slider__thumbs-container');
        var $thumbsWrapper = $thumbsContainer.find('.product-detail-slider__thumbs-wrapper');
        var $thumbsItems = $thumbsContainer.find('.product-detail-slider__thumbs-wrapper img');

        // Track that this component has been initialized.
        $component.addClass('product-detail-slider--js-initialized');

        // Add swiper classes and elements.
        $sliderContainer.addClass('swiper-container');
        $sliderItems.addClass('swiper-slide');
        $sliderWrapper.addClass('swiper-wrapper');
        $thumbsContainer.addClass('swiper-container');
        $thumbsItems.addClass('swiper-slide');
        $thumbsWrapper.addClass('swiper-wrapper');

        // Initialize swiper.
        if ($component.find('.field__item').length > 1) {
          // eslint-disable-next-line
          var $galleryThumbs = new Swiper($thumbsContainer, {
            spaceBetween: 24,
            slidesPerView: 4,
            breakpoints: {
              568: {
                slidesPerView: 6,
                spaceBetween: 12
              },
              1024: {
                slidesPerView: 4,
                spaceBetween: 16
              }
            },
            freeMode: true,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,

            on: {
              resize: function () {
                var $thumbHeight = $thumbsItems.outerHeight();
                var $thumbWidth = $thumbsItems.outerWidth();
                $('.product-detail-slider__button').css('height', $thumbHeight).css('width', $thumbWidth);
              },
              imagesReady: function () {
                var $thumbHeight = $thumbsItems.outerHeight();
                var $thumbWidth = $thumbsItems.outerWidth();
                $('.product-detail-slider__button').css('height', $thumbHeight).css('width', $thumbWidth);
              },
              slideChange: function (el) {
                $('.swiper-slide').each(function () {
                  var youtubePlayer = $(this).find('iframe').get(0);

                  if (youtubePlayer) {
                    youtubePlayer.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                  }
                });
              },
              slideChangeTransitionEnd: function () {
                Drupal.behaviors.swiper.updateSlideAria.apply(this);
                Drupal.blazy.init.revalidate();
              }
            }
          });
          // eslint-disable-next-line
          new Swiper($sliderContainer, {
            effect: 'fade',
            loop: true,
            navigation: {
              nextEl: $sliderButtonNext,
              prevEl: $sliderButtonPrev
            },
            on: {
              init: function () {
                // Use a timeout on init to make sure to catch contextual links.
                setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind(this), 500);
              },
              resize: function () {
                Drupal.behaviors.swiper.updateSlideAria.apply(this);
                Drupal.blazy.init.revalidate();
              },
              slideChange: function (el) {
                $('.swiper-slide').each(function () {
                  var youtubePlayer = $(this).find('iframe').get(0);

                  if (youtubePlayer) {
                    youtubePlayer.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                  }
                });
              },
              slideChangeTransitionEnd: function () {
                Drupal.behaviors.swiper.updateSlideAria.apply(this);
                Drupal.blazy.init.revalidate();
              }
            },
            slidesPerGroup: 1,
            slidesPerView: 1,
            touchEventsTarget: 'container',
            thumbs: {
              swiper: $galleryThumbs
            }
          });
        }

        $(document).on('click', '.product-detail-slider__pseudo-prev-button', function () {
          $('.swiper-button-prev').trigger('click');
        });

        $(document).on('click', '.product-detail-slider__pseudo-next-button', function () {
          $('.swiper-button-next').trigger('click');
        });
      });
    }
  };

  Drupal.behaviors.productDetailModalImageslider = {
    initModalSlider: function ($modalSliderContainer, $modalSliderButtonPrev, $modalSliderButtonNext) {
      // eslint-disable-next-line
      return new Swiper($modalSliderContainer, {
        slidesPerView: 1,
        slidesPerGroup: 1,
        loop: true,
        navigation: {
          nextEl: $modalSliderButtonNext,
          prevEl: $modalSliderButtonPrev
        },
        on: {
          init: function () {
            Drupal.behaviors.productDetailModalImageslider.updatePointerEvents();

            // Use a timeout on init to make sure to catch contextual links.
            setTimeout(Drupal.behaviors.swiper.updateSlideAria.bind($(this)[0]), 500);

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          },
          resize: function () {
            Drupal.behaviors.productDetailModalImageslider.updatePointerEvents();
            Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          },
          slideChangeTransitionEnd: function () {
            Drupal.behaviors.swiper.updateSlideAria.apply($(this)[0]);
            Drupal.behaviors.productDetailModalImageslider.updatePointerEvents();

            if (typeof Drupal.blazy !== 'undefined') {
              Drupal.blazy.init.revalidate();
            }
          }
        },
        touchEventsTarget: 'container'
      });
    },
    updatePointerEvents: function () {
      $('.swiper-slide').removeClass('active-pointer');
      $('.swiper-slide-visible').addClass('active-pointer');
    },
    attach: function (context, settings) {
      // Product image slider modal
      $('.field--name-field-product-images').click(function (event) {
        $('#product-image-slider-modal').modal({
          fadeDuration: 300,
          fadeDelay: 1.55
        });
        return false;
      });

      // check for initialized
      $('.product-detail-modal-slider:not(.product-detail-modal-slider--js-initialized)').each(function (index) {
        // Modal Swiper Image Slider
        var $modalComponent = $(this);
        var $modalSliderContainer = $modalComponent.find('.product-detail-modal-slider__container');
        var $modalSliderWrapper = $modalSliderContainer.find('.field--name-field-product-images');
        var $modalSliderItems = $modalSliderContainer.find('.field--name-field-product-images .field__item');
        var $modalSliderButtonPrev = $modalComponent.find('.product-detail-modal-slider__button-prev');
        var $modalSliderButtonNext = $modalComponent.find('.product-detail-modal-slider__button-next');

        // Track that this component has been initialized.
        $modalComponent.addClass('product-detail-modal-slider--js-initialized');

        $modalSliderContainer.addClass('swiper-container');
        $modalSliderWrapper.addClass('swiper-wrapper');
        $modalSliderItems.addClass('swiper-slide');

        var $modalSliderSwiper = Drupal.behaviors.productDetailModalImageslider.initModalSlider($modalSliderContainer, $modalSliderButtonPrev, $modalSliderButtonNext);

        $('.field--name-field-product-images').click(function (event) {
          setTimeout(function () {
            if (typeof $modalSliderSwiper !== 'undefined') {
              $modalSliderSwiper.destroy(true, true);
              $modalSliderSwiper = Drupal.behaviors.productDetailModalImageslider.initModalSlider($modalSliderContainer, $modalSliderButtonPrev, $modalSliderButtonNext);

              // Get product detail slider active slide and index
              var $detailSliderContainer = $('.product-detail-slider').find('.product-detail-slider__container');
              var $detailSliderSlides = $($detailSliderContainer).find('.swiper-slide');
              var $detailSliderActiveSlide = $($detailSliderContainer).find('.swiper-slide-active');
              var $slideIndx = $($detailSliderSlides).index($detailSliderActiveSlide);

              $modalSliderSwiper.slideTo($slideIndx, 0, false);
            }
          }, 500);
        });

        $($modalSliderButtonPrev).on('click', function () {
          $('.product-detail-slider__button-prev').trigger('click');
        });

        $($modalSliderButtonNext).on('click', function () {
          $('.product-detail-slider__button-next').trigger('click');
        });
      });
    }
  };

  Drupal.behaviors.productDetailTabs = {
    attach: function (context, settings) {
      $('.node--type-product-tabs:not(.node--type-product-tabs--js-initialized)').once('tabbed').each(function (index) {
        // Initialize variables.
        var $tabsWidget = $(this);
        var $tablist = $tabsWidget.find('.node--type-product-tabs__nav-wrapper').children('.node--type-product-tabs__nav');
        var $tabs = $tablist.find('.node--type-product-tabs__nav-item');
        var $links = $tablist.find('a');
        var $panelContainer = $tabsWidget.children('.node--type-product-tabs__content');
        var $panels = $panelContainer.children('.node--type-product-tabs-tab');
        var $mobileTabsWidget = $(this);
        var $mobileTablist = $mobileTabsWidget.find('.node--type-product-tabs__content');
        var $mobileTabs = $mobileTablist.find('.node--type-product-tabs__mobile-nav-item');
        var $mobileLinks = $mobileTablist.find('a');

        // Mark that the tabs component has been initialized.
        $tabsWidget.addClass('node--type-product-tabs--js-initialized');

        // Add static roles to elements.
        $tablist.attr('role', 'tablist');
        $tabs.attr('role', 'tab');
        $panels.attr('role', 'tabpanel');
        $links.attr('role', 'presentation');

        // Add static roles to elements.
        $mobileTablist.attr('role', 'tablist');
        $mobileTabs.attr('role', 'tab');
        $mobileLinks.attr('role', 'presentation');

        // Default to last item as selected.
        $tabs.attr('aria-selected', 'false')
          .last()
          .attr('tabindex', '0')
          .attr('aria-selected', 'true');
        $mobileTabs.attr('aria-selected', 'false');
        $panels.prop('hidden', true)
          .last()
          .prop('hidden', false);

        // Label panel and mark that each is controlled by their respective tab.
        $tabs.each(function (tabIndex) {
          var $tab = $(this);
          var $tabLink = $tab.find('a');
          var panelId = $tabLink.attr('href').substring($tabLink.attr('href').indexOf('#') + 1);
          $tabLink.attr('id', panelId);
          var tabId = panelId + '-tab';

          // Remove link from href.
          $tabLink.removeAttr('href');

          // Link tab to panel.
          $tab.attr('id', tabId)
            .attr('aria-controls', panelId);

          // Link panel to tab.
          $panels.eq(tabIndex)
            .attr('id', panelId)
            .attr('aria-labelledby', tabId);
        });

        $mobileTabs.each(function (tabIndex) {
          var $tab = $(this);
          var $tabLink = $tab.find('a');
          var $tabLinkHref = $tabLink.attr('href');
          var panelId;
          if (typeof $tabLinkHref !== 'undefined' && $tabLinkHref !== false) {
            panelId = $tabLink.attr('href').substring($tabLink.attr('href').indexOf('#') + 1);
            $tabLink.attr('id', panelId);
          }
          else {
            panelId = $tabLink.attr('id').substring($tabLink.attr('id').indexOf('#') + 1);
          }
          var tabId = panelId + '-tab';

          // Remove link from href.
          $tabLink.removeAttr('href');

          // Link tab to panel.
          $tab.attr('id', tabId)
            .attr('aria-controls', panelId);

          // Link panel to tab.
          $panels.eq(tabIndex)
            .attr('id', panelId)
            .attr('aria-labelledby', tabId);
        });

        // Initialize the roving tabindex.
        $tablist.rovingTabindex('[role=tab]');
        $('.node--type-product-tabs__content').rovingTabindex('[role=tab]');

        // Track when tab is changed.
        $mobileTablist.on('rovingTabindexChange', '[role=tab]', function (e, data) {
          var $mobileTab = $(this);
          $mobileTabs.attr('aria-selected', 'false');
          $mobileTab.attr('aria-selected', 'true');
        });

        $mobileTablist.on('click keydown', '[role=tab]', function (e, data) {
          var $keyCode = e.keyCode || e.which;
          if (e.type === 'click' || ($keyCode === 13 && e.type === 'keydown')) {
            var $mobileTab = $(this);
            if ($panels.filter('#' + $mobileTab.attr('aria-controls')).prop('hidden') === true) {
              $panels.prop('hidden', true);
              $panels.filter('#' + $mobileTab.attr('aria-controls')).prop('hidden', false);
            }
            else {
              $panels.filter('#' + $mobileTab.attr('aria-controls')).prop('hidden', true);
            }
            Drupal.blazy.init.revalidate();
          }
        });

        $tablist.on('rovingTabindexChange', '[role=tab]', function (e, data) {
          var $tab = $(this);
          $tabs.attr('aria-selected', 'false');
          $tab.attr('aria-selected', 'true');
          $panels.prop('hidden', true);
          $panels.filter('#' + $tab.attr('aria-controls')).prop('hidden', false);
          Drupal.blazy.init.revalidate();
        });

        // Track active slide.
        $(window).resize(function () {
          var $openPanel;
          $panels.each(function (i, obj) {
            if ($(this)[0].hidden === false) {
              $openPanel = i;
            }
          });
          if (typeof $openPanel === 'undefined') {
            $tabs.each(function (i, obj) {
              if (i === 0) {
                $(this).trigger('click');
              }
            });
          }
          else {
            $tabs.each(function (i, obj) {
              if (i === $openPanel) {
                $(this).trigger('click');
              }
            });
          }
        });

        // Set active tab onload.
        setTimeout(function () {
          $tabs.each(function (i, obj) {
            if (i === 2) {
              $(this).trigger('click');
              $(this).removeAttr('tabindex');
            }
          });
        }, 0);

        // Track Features click
        $('.more-features-link').on('click', function () {
          $tabs.each(function (i, obj) {
            if (i === 0) {
              $(this).trigger('click');
            }
          });
        });
      });
    }
  };
})(jQuery, Drupal);
