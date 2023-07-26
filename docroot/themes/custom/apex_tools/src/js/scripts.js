import '@babel/polyfill';
import Vue from "vue";
import EventBus from "./event-bus";

require("./polyfill");
Vue.config.devtools = true;

// Vue Config
window.Vue = Vue;
require("./vue-use");
require("./vue-filters");
require("./vue-directives");
require("./vue-components");

(function( $ ){

  if(!$('div').hasClass('multi-form')) {
    // Bootstrap Vue
    window.app = new Vue({
      el: "#app",
      mounted() {
        EventBus.$on("captive", (isCaptive) => {
          if (isCaptive) {
            let isScrollable = document.body.scrollHeight > window.innerHeight;
            if (isScrollable) {
              document.documentElement.classList.add("is-captive--scroll");
            }

            document.documentElement.classList.add("is-captive");
          }
          else {
            document.documentElement.classList.remove("is-captive", "is-captive--scroll");
          }
        })
      }
    });
  }

  if ($('div').hasClass('multi-form')) {
    function searchToggle() {
      $('.masthead-search-toggle').on('click', function (e) {
        $('.masthead-primary').toggleClass('is-searching');
      });
    }

    function setActiveLang() {
      // Get the current domain from the page URL
      var currentDomain = window.location.hostname;
            
      // Loop through each language link and compare its domain with the current domain
      $('.language-link').each(function () {
          var linkDomain = $(this).prop('href').split('/')[2];
          
          if (currentDomain === linkDomain) {
              // Add the "active" class to the parent li element if the domain matches
              $(this).parent('li').addClass('is-active');
          }
      });
    }

    searchToggle();
    setActiveLang();
    $(document).ajaxComplete(function(){
      searchToggle();
      setActiveLang();
      if ($('div').hasClass('multi-form')) {
        $('.masthead-search-toggle').on('click', function (e) {
          $('.masthead-primary').toggleClass('is-searching');
        });
      }
    });
  }

})(jQuery);


require("./components/nav");
require("./helpers/floating-labels");
require("./helpers/modals");
require("./animations");

require("./product-hotspots");
require("./product-viewer");

require("./helpers/waypoint");
require("./waypoints");
require("./hero-video");
require("./hubspot");
require("./multi-step-form");
