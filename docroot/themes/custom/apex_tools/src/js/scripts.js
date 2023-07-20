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
  
  if($('body').hasClass('path-products')) {
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
  else if ($('div').hasClass('multi-form')) {
    function searchToggle() {
      $('.masthead-search-toggle').on('click', function (e) {
        $('.masthead-primary').toggleClass('is-searching');
      });
    }
    searchToggle();
    $(document).ajaxComplete(function(){
      searchToggle();
    });
  }
  else {
    window.app = new Vue({
      el: "#app"
    });
  }

})( jQuery );


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
