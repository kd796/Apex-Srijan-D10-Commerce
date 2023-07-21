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
        this.$nextTick(() => {
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
        });
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
      $(".custom-masthead-language").on('click','li',function() {
        // remove classname 'active' from all li who already has classname 'active'
        $(".custom-masthead-language li.active").removeClass("is-active");
        // adding classname 'active' to current click li.
        $(this).addClass("is-active");
    });
    }
    searchToggle();
    setActiveLang();
    $(document).ajaxComplete(function(){
      searchToggle();
      setActiveLang();
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
