(function () {
  "use strict";

  window.addEventListener("load", () => {
    const facetCheckbox = document.querySelectorAll(".block-facet--checkbox");
    const sortBy = document.querySelector(
      ".view-header-filter .form-item-sort-by .bef-links"
    );
    const sortedOption = document.querySelector(
      ".view-header-filter .sorted-option"
    );
    const selectedOption = document.querySelector(
      ".view-header-filter .bef-link--selected"
    );
    const searchSidebar = document.querySelector(".search-page .sidebar-first");
    const facetItems = document.querySelectorAll(
      ".facets-widget-checkbox .facet-item"
    );

    if (facetCheckbox.length > 0) {
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
    }
    else {
      searchSidebar.style.padding = 0;
    }

    sortBy.classList.add("none");
    if (selectedOption) {
      sortedOption.innerHTML = selectedOption.textContent;
    }

    sortedOption.addEventListener("click", function () {
      if (sortBy.classList.contains("none")) {
        sortBy.classList.remove("none");
        sortBy.classList.add("block");
      }
      else {
        sortBy.classList.add("none");
        sortBy.classList.remove("block");
      }
    });

    document.addEventListener("click", function (event) {
      const clickedElement = event.target;

      if (
        clickedElement === sortBy ||
        sortBy.contains(clickedElement) ||
        clickedElement === sortedOption
      ) {
        return;
      }
      else {
        if (sortBy.classList.contains("block")) {
          sortBy.classList.add("none");
          sortBy.classList.remove("block");
        }
      }
    });

    facetItems.forEach((element) => {
      element.querySelector("label").addEventListener("click", function () {
        if (!element.querySelector("input").checked) {
          element.querySelector("input").classList.add('checked');
        }
        else {
          element.querySelector("input").classList.remove('checked');
        }
      });
    });
  });
})();
