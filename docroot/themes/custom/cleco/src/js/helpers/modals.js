const openButtons = document.querySelectorAll( '.open-modal' );
const masks = document.querySelectorAll( '.modal-mask' );

for (var i = 0; i < openButtons.length; i++) {
    const button = openButtons[i];

    button.addEventListener( 'click', (e) => {
        const name = button.getAttribute( 'data-modal' );
        document.getElementById( 'modal-' + name ).parentElement.classList.add( 'is-visible' );
        e.preventDefault();
    });
}


for (var i = 0; i < masks.length; i++) {
    const mask = masks[i];

    mask.addEventListener( 'click', (e) => {
        if ( e.target.classList.contains('close-modal') || e.target.classList.contains('modal-mask') ) {
            mask.classList.remove( 'is-visible' );
        }
        e.preventDefault();
    });
}
