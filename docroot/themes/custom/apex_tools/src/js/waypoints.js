var waypoints = [];

// if (document.getElementsByClassName('enhanced-hero-logo')[0]) {
//   waypoints.push(new Waypoint({
//     element: document.getElementsByClassName('enhanced-hero-logo')[0],
//     handler: function (direction) {
//       if (direction === 'down') {
//         this.element.classList.add('is-animated');
//         this.destroy();
//       }
//     },
//     offset: '90%'
//   }));
// }

// if (document.getElementsByClassName('enhanced-hero-description')[0]) {
//   waypoints.push(new Waypoint({
//     element: document.getElementsByClassName('enhanced-hero-description')[0],
//     handler: function (direction) {
//       if (direction === 'down') {
//         this.element.classList.add('is-animated');
//         this.destroy();
//       }
//     },
//     offset: '90%'
//   }));
// }

if (document.getElementsByClassName('section--product-features')[0]) {
  waypoints.push(new Waypoint({
    element: document.getElementsByClassName('section--product-features')[0],
    handler: function (direction) {
      if (direction === 'down') {
        this.element.classList.add('is-animated');
        this.destroy();
      }
    },
    offset: '90%'
  }));
}

if (document.getElementsByClassName('section--find-out')[0]) {
  waypoints.push(new Waypoint({
    element: document.getElementsByClassName('section--find-out')[0].getElementsByClassName('contain')[0],
    handler: function (direction) {
      if (direction === 'down') {
        this.element.classList.add('is-animated');
        this.destroy();
      }
    },
    offset: '90%'
  }));
}

if (document.getElementsByClassName('section--related-products')[0]) {
  waypoints.push(new Waypoint({
    element: document.getElementsByClassName('section--related-products')[0].getElementsByClassName('basic-cards-slider')[0],
    handler: function (direction) {
      if (direction === 'down') {
        this.element.classList.add('is-animated');
        this.destroy();
      }
    },
    offset: '80%'
  }));
}

if (document.getElementsByClassName('section--insights')[0]) {
  var containers = document.getElementsByClassName('section--insights')[0].getElementsByClassName('contain');
  for (var i = 0; i < containers.length; i++) {
    waypoints.push(new Waypoint({
      element: containers[i],
      handler: function (direction) {
        if (direction === 'down') {
          this.element.classList.add('is-animated');
          this.destroy();
        }
      },
      offset: '80%'
    }));
  }
}

if (document.getElementsByClassName('section--schedule-demo')[0]) {
  var containers = document.getElementsByClassName('section--schedule-demo')[0].getElementsByClassName('contain');
  for (var i = 0; i < containers.length; i++) {
    waypoints.push(new Waypoint({
      element: containers[i],
      handler: function (direction) {
        if (direction === 'down') {
          this.element.classList.add('is-animated');
          this.destroy();
        }
      },
      offset: '80%'
    }));
  }
}
