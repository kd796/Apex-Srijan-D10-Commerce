(function () {
  "use strict";

  window.addEventListener("load", () => {
    const facetCheckbox = document.querySelectorAll(".block-facet--checkbox");
    const sortBy = document.querySelector('.view-header-filter .form-item-sort-by select');
    const submitSearch = document.querySelector('.view-header-filter .form-actions .form-submit');

    facetCheckbox[0].classList.add("filter--open");

    facetCheckbox.forEach((element) => {
      element.addEventListener("click", function () {
        if (element.classList.contains("filter--open")) {
          element.classList.remove("filter--open");
          return;
        }
        facetCheckbox.forEach((element) => {
          element.classList.remove("filter--open");
        });

        element.classList.add("filter--open");
      });
    });

    sortBy.addEventListener('change', function () {
      submitSearch.click();
    });
  });
})();
