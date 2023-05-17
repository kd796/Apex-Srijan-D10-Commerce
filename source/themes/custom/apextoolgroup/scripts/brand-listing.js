(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.apextoolgroup = {
    attach: function (context, settings) {
      localTaskTabs();
      sidebarSliding();

      $(document).ajaxComplete(function () {
        localTaskTabs();
        sidebarSliding();
      });
    }
  };

  // Brand Page Sidebar Sliding On Tablet Device.
  function sidebarSliding() {
    // Brand Filter Toggle hamburger.
    const filterHamburger = $('.brands__filter-icon');
    const brandSidebar = $('.brands__sidebar');
    filterHamburger
      .once('filterHamburger')
      .on('click', function () {
        brandSidebar.addClass('is-active');
      });

    // Brand Filter Close Filter.
    const filterClose = $('.brands__filter-close-icon');
    filterClose
      .once('filterClose')
      .on('click', function () {
        brandSidebar.removeClass('is-active');
      });

    /* end */
  }

  // Local Task Tabs Prepend.
  function localTaskTabs() {
    $('.brands .brands__content .brands__tabs .tabs').remove();
    var localTaskTabs = $('.path-brands-faceting .region-content .block-local-tasks-block').html();
    $('.brands .brands__content .brands__tabs').append(localTaskTabs);
  }
})(jQuery, Drupal);
