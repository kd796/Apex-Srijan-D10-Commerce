import Vue from "vue";
// Custom
import formatAddress from "./filters/format-address";
import nl2br from "./filters/nl2br";
import groupBy from "./filters/group-by";

// Register
Vue.filter("formatAddress", formatAddress);
Vue.filter("nl2br", nl2br);
Vue.filter("groupBy", groupBy);
