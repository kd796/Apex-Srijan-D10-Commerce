const SEARCHING_CLASS = 'is-searching';

export default {
    bind: function(el, binding, vnode) {
        el.querySelectorAll('.masthead-search-toggle').forEach(($toggle) => {
            $toggle.addEventListener('click', (e) => {
                vnode.context.$root.$emit('masthead-search')
            })
        })

        el.__searchHandler = function mastheadSearchDirectiveHandler(show = null) {
            if (show === null) {
                show = ! el.classList.contains(SEARCHING_CLASS);
            }

            let $input = el.querySelector('.masthead-search-form-input');

            if (show === false) {
                $input.value = '';
                el.classList.remove(SEARCHING_CLASS);
                document.removeEventListener('keyup', el.__documentKeyupHandler);
            } else {
                el.classList.add(SEARCHING_CLASS);
                $input.focus();
                document.addEventListener('keyup', el.__documentKeyupHandler);
            }
        }

        el.__documentKeyupHandler = function mastheadSearchDocumentKeyupHandler(e) {
            // Escape
            if (e.keyCode === 27) {
                vnode.context.$root.$emit('masthead-search', false);
            }
        }

        // Add event listeners
        vnode.context.$root.$on('masthead-search', el.__searchHandler);
    },
    unbind(el) {
        vnode.context.$root.$off('masthead-search', el.__searchHandler)
        document.removeEventListener('keyup', el.__documentKeyupHandler);
    }
}
