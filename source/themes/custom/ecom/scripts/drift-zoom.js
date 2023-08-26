// Drift zomm for images on hover

let triggerElement = document.querySelectorAll(
  ".field--name-field-product-images .media--image .media__element"
);
let paneContainer = document.querySelector(".product-detail__links");

function driftZoom(triggerElement, paneContainer) {
  "use strict";
  triggerElement.forEach((element) => {
    let imageSrc = element.getAttribute('src');
    let originalImg = imageSrc.replace('styles/medium/public/', '');
    element.setAttribute('data-zoom', originalImg);
    new Drift(element, {
      paneContainer: paneContainer,
      inlinePane: false
    });
  });
}

if (window.innerWidth > 767) {
  driftZoom(triggerElement, paneContainer);
}
