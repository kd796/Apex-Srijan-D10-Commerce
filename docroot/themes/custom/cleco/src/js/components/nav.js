/**
 * NAV
 */
document.addEventListener('DOMContentLoaded', () => {
    let navToggleSelector = '.nav-link--depth-0';
    var $navToggles = document.querySelectorAll(navToggleSelector);
    let navMenuSelector = '.nav-menu--depth-1';
    let $navMenus = document.querySelectorAll(navMenuSelector);
    let activeClass = 'is-active';

    $navToggles.forEach(($navToggle) => {
        let $navItem = $navToggle.parentNode;
        let $navMenu = $navItem.querySelectorAll(navMenuSelector);

        //$navToggles.querySelector('.child');
        if($navMenu.length>0) {
            $navItem.classList.add('menu-item--has-children');
        }
        $navToggle.addEventListener('click', function(event) {
            if ( ! $navItem.classList.contains(activeClass) && $navMenu.length > 0) {
                event.preventDefault();
                closeActiveDropdowns();
                $navItem.classList.add(activeClass);
            }
        });
    });

    /**
     * CONTAIN SUBMENU EVENTS
     * Contain clicks and touchstart events inside menus to prevent accidental closure.
     */
    $navMenus.forEach(($dropdownMenu) => {
        $dropdownMenu.addEventListener("click", e => { e.stopPropagation() });
        $dropdownMenu.addEventListener("touchstart", e => { e.stopPropagation() });
    });

    /**
     * CLICK AWAY
     * Close dropdowns on click or touchstart not matching the anchor selector.
     */
    let handleClickAway = function (e) {
        if (! e.target.matches(navToggleSelector)) {
            closeActiveDropdowns();
        }
    };
    window.addEventListener("click", handleClickAway);
    window.addEventListener("touchstart", handleClickAway);

    /**
     * CLOSE ACTIVE DROPDOWNS
     * Find all active dropdowns and close them.
     */
    let closeActiveDropdowns = function() {
        let $activeDropdowns = document.querySelectorAll(navMenuSelector);
        $activeDropdowns.forEach(($dropdown) => {
            $dropdown.parentNode.classList.remove(activeClass)
        });
    };
});

jQuery(document).ready(function ($) {
    $('.toggle').click(function (e) {
        e.preventDefault();
        $(this).toggleClass('is-active');
        $('.masthead-navigation').toggleClass('is-active');
        $('body').toggleClass('mobile-menu-opened');
    });
});
