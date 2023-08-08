// Drift zomm for images on hover

let triggerElement = document.querySelectorAll(
  ".field--name-field-product-images .media--image .media__element"
);
let paneContainer = document.querySelector(".product-detail__links");

function driftZoom(triggerElement, paneContainer) {
  "use strict";
  triggerElement.forEach((element) => {
    new Drift(element, {
      paneContainer: paneContainer,
      inlinePane: false
    });
  });
}

if (window.innerWidth > 767) {
  driftZoom(triggerElement, paneContainer);
}
