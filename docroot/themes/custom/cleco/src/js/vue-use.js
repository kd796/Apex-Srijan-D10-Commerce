import Vue from "vue";
import VueScrollTo from "vue-scrollto";
import * as VueGoogleMaps from "vue2-google-maps";
import Siema from "vue2-siema";

Vue.use(VueScrollTo);
Vue.use(VueGoogleMaps, {
  load: {
    key: process.env.MIX_GOOGLE_MAPS_API_KEY,
  },
  installComponents: false,
});
Vue.use(Siema);
