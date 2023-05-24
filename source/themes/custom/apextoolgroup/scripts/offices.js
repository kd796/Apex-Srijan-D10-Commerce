(function ($) {
  'use strict';
  Drupal.behaviors.gmap = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $('a.mapLocationLink').each(function () {
          $(this).click(function (e) {
            e.preventDefault();
            let latitude = $(this).data('latitude');
            let longitude = $(this).data('longitude');
            let markers = Drupal.geolocation.maps[0].mapMarkers;
            for (let i = 0; i < markers.length; i++) {
              if (markers[i].position.lat() === latitude && markers[i].position.lng() === longitude) {
                google.maps.event.trigger(markers[i], 'click');
                break;
              }
            }
            return false;
          });
        });

        $('.offices .attachment .view-office .view-content .views-row .title .mapLocationLink').click(function (event) {
          event.preventDefault();
          $(this).parents('.views-row').addClass('is-active').siblings('.views-row').removeClass('is-active');
        });
      });
    }
  };
})(jQuery);
