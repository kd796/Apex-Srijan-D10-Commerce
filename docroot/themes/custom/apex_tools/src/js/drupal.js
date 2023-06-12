import Vue from "vue";

/**
 * RE-VUE
 * Add a Drupal behavior to mount any injected DOM nodes inside $root as a Vue
 * component
 */
Drupal.behaviors.reVue = {
  attach(context, settings) {
    if (context.children.length > 0 && app.$el.contains(context)) {
      let InjectedComponent = Vue.extend({
        template: context.outerHTML,
      });

      new InjectedComponent().$mount(context);
    }
  }
};
