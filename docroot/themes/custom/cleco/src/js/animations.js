import {Animator, AnimatorAnimation} from "./helpers/animations";
import browser from 'browser-detect';

const browserResult = browser();
const ignoreBrowsers = ['ie'];

/**
 * ANIMATIONS
 */
if (ignoreBrowsers.indexOf(browserResult.name) == -1) {
  const hero = document.getElementsByClassName('hero')[0];

  if (typeof hero !== 'undefined' && hero.className.indexOf('hero--no-animate') === -1) {
    let animator = new Animator();

    // Animate Hero
    let heroAnimation = animator.addElement(".hero", {
      offset: false
    }).animateProperty("opacity", 1, 0);
    heroAnimation.addChild(".hero-title-prefix").animateProperty("translateX", 0, -2, "rem");
    heroAnimation.addChild(".hero-title").animateProperty("translateX", 0, 2, "rem");
    heroAnimation.addChild(".hero-image").animateProperty("scale", 1, 1.2);

    // Animate Media Blocks
    animator.addElement(".media-block", {
      offset: 0.66,
      offscreenOnly: true
    }).setClass("animate");

    // Animate Feature Section Items
    animator.addElement(".section--features .feature", {
      offset: 0.66,
      offscreenOnly: true
    }).setClass("animate");

    // Kick it off
    animator.init();
  }

} else {
  document.body.classList.add('anim-disabled');
}

