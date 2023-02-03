/**
 * AnimatorAnimation
 * Class housing animation configurations.
 */
export class AnimatorAnimation {
  /**
   * AnimatorAnimation: Constructor
   *
   * @param {string} selector Selector string passed to querySelectorAll()
   * @param {object} options Options object
   */
  constructor(selector, options = {}) {
    this.selector = selector;
    this.offset = options.offset === undefined ? 0 : options.offset;
    this.properties = options.properties || {};
    this.children = options.children || [];
    this.offscreenOnly = options.offscreenOnly || false;
  }
}

/**
 * AnimatorAnimation: Add Child
 * Add a child to a given animation. Allows animation of child elements keying
 * off parent element delta.
 *
 * @param {string} selector Selector string passed to querySelectorAll()
 * @param {object} options Options object
 *
 * @return {AnimatorAnimation} Child animation object
 */
AnimatorAnimation.prototype.addChild = function (selector, options = {}) {
  let animation = new AnimatorAnimation(selector, options);
  this.children.push(animation);

  return animation;
};

/**
 * AnimatorAnimation: Animate Property
 * Set the animation properties for the animation.
 *
 * @param {string} property Property to animate. Transform properties are
 *     auto-detected.
 * @param {Number} from Value to animate from
 * @param {Number} to Value to animate to
 * @param {string|null} units Units to apply to the from/to values
 *
 * @return this
 */
AnimatorAnimation.prototype.animateProperty = function (property, from, to, units = null) {
  let transformProperties = [
    "scale",
    "scaleX",
    "scaleY",
    "rotate",
    "rotateX",
    "rotateY",
    "translate",
    "translate3d",
    "translateX",
    "translateY",
  ];

  let values = [from, to];
  if (units !== null) {
    values.push(units);
  }

  if (transformProperties.includes(property)) {
    if (this.properties.transform === undefined) {
      this.properties.transform = {};
    }

    this.properties.transform[property] = values;
  }
  else {
    this.properties[property] = values;
  }

  return this;
};

/**
 * AnimatorAnimation: Set Class
 * Sets the class name to toggle when an animation is triggered.
 *
 * @param {string} className Class name to toggle
 *
 * @return this
 */
AnimatorAnimation.prototype.setClass = function (className) {
  this.class = className;
  this.interval = null;

  return this;
};


/**
 * Animator
 * Class housing a scrolling animator.
 */
export class Animator {
  /**
   * Animator: Constructor
   */
  constructor() {
    this.animations = [];
    this.scrollElement = document.scrollingElement;
    this.viewportElement = window;
  }
}

/**
 * Animator: Add Element
 * Add an element to the animator.
 */
Animator.prototype.addElement = function (selector, options) {
  let animation = new AnimatorAnimation(selector, options);
  this.animations.push(animation);

  return animation;
};

/**
 * Animator: Init
 * Initialize the animator.
 *
 * @return this
 */
Animator.prototype.init = function () {
  this.start();

  return this;
};

/**
 * Animator: Start
 * Start the requestAnimationFrame interval to update the interface.
 *
 * @return this
 */
Animator.prototype.start = function () {
  this.interval = setInterval(() => {
    window.requestAnimationFrame(() => {
      this.update();
    });
  }, 10);

  return this;
};

/**
 * Animator: Stop
 * Stop the requestAnimationFrame interval.
 *
 * @return this
 */
Animator.prototype.stop = function () {
  clearInterval(this.interval);

  return this;
};

/**
 * Animator: Update
 * Update the interface based on the current scroll position.
 *
 * @return this
 */
Animator.prototype.update = function () {
  let scrollTop = this.scrollElement.scrollTop;
  this.animations.forEach((animation) => {
    document.querySelectorAll(animation.selector).forEach((element, index) => {
      let totalOffset = this.getTotalElementOffset(element);
      let bounds = element.getBoundingClientRect();
      let offset = this.viewportElement.innerHeight * (animation.offset || 0);
      let relativeTop = totalOffset.top - offset;
      let relativeEnd = relativeTop + bounds.height;
      if (animation.offset === false) {
        relativeTop = 0;
      }

      let delta = (scrollTop - relativeTop) / (relativeEnd - relativeTop);
      delta = Math.min(1, Math.max(0, delta));

      if (animation.offscreenOnly) {
        if (totalOffset.top < this.viewportElement.innerHeight) {
          delta = 1;
        }
      }

      this.updateElement(element, animation, delta);
    });
  });

  return this;
};

/**
 * Animator: Get Total Element Offset
 * Return the total offset of an element in relation to its parents
 *
 * @param {HTMLElement} element The element to query
 *
 * @return {object} Object with `top` and `left` properties
 */
Animator.prototype.getTotalElementOffset = function (element) {
  let offset = {
    top: 0,
    left: 0
  };

  while (element.offsetParent) {
    offset.top += element.offsetTop;
    offset.left += element.offsetLeft;

    element = element.offsetParent;
  }

  return offset;
};

/**
 * Animator: Interpolate
 * Interpolate the difference between a min and a max based on a delta
 *
 * @param {Number} min Minimum value
 * @param {Number} max Maximum value
 * @param {Number} delta Delta between 0 and 1
 *
 * @return {Number} The interpolated value
 */
Animator.prototype.interpolate = function (min, max, delta) {
  return (1 - delta) * min + delta * max;
};

/**
 * Animator: Update Element
 * Update an element based on its animation configuration and the current delta
 *
 * @param {HTMLElement} element Element to animate
 * @param {AnimatorAnimation} animation Animation configuration object
 * @param {Number} delta Current delta based on animation configuration
 *
 * @return this
 */
Animator.prototype.updateElement = function (element, animation, delta) {
  if (animation.properties) {
    for (let property in animation.properties) {
      if (animation.properties.hasOwnProperty(property)) {
        let value = animation.properties[property];
        if (!Array.isArray(value)) {
          let values = [];
          for (let func in value) {
            if (value.hasOwnProperty(func)) {
              let transformValue = value[func];
              let units = transformValue[2] || 0;
              values.push(func + "(" + (this.interpolate(transformValue[0], transformValue[1], delta) + units) + ")");
            }
          }
          element.style[property] = values.join(" ");
        }
        else {
          let units = value[2] || 0;
          element.style[property] = this.interpolate(value[0], value[1], delta) + units;
        }
      }
    }
  }

  if (animation.class) {
    element.classList.toggle(animation.class, delta > 0);
  }

  if (animation.children) {
    animation.children.forEach((childAnimation) => {
      element.querySelectorAll(childAnimation.selector).forEach((childElement) => {
        this.updateElement(childElement, childAnimation, delta);
      })
    });
  }

  return this;
};

export default Animator;
