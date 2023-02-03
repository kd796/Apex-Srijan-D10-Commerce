const inputs = document.querySelectorAll( '.floating-labels input, .floating-labels select' );

for (var i = 0; i < inputs.length; i++) {
    const input = inputs[i];

    inputs[i].addEventListener( 'focus', () => {
        input.parentElement.classList.add( 'move-label' );
    });

    inputs[i].addEventListener( 'blur', () => {
        if ( !input.value ) {
            input.parentElement.classList.remove( 'move-label' );
        }
    });

    inputs[i].addEventListener( 'change', () => {
        if ( !input.value ) {
            input.parentElement.classList.remove( 'move-label' );
        }
    });
}
