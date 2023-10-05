/**
 * @file
 * Defines Javascript behaviors for the commerce cart module.
 */

(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.commerceCartBlock = {
    attach(context) {
      const $context = $(context);
      const $cart = $context.find('.cart--cart-block').addBack('.cart--cart-block');
      const $cartContents = $cart.find('.cart-block--contents');

      if ($cartContents.length > 0) {
        // Expand the block when the link is clicked.
        $(once('cart-button-processed', '.cart-block--link__expand', context)).on('click', (e) => {
          // Get the shopping cart width + the offset to the left.
          const windowWidth = $(window).width();
          const cartWidth = $cartContents.width() + $cart.offset().left;
          // If the cart goes out of the viewport we should align it right.
          if (cartWidth > windowWidth) {
            $cartContents.addClass('is-outside-horizontal');
          }
        });
      }
    }
  };

})(jQuery, Drupal, once);

