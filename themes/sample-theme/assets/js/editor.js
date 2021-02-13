wp.blocks.registrBlockStyle(
    'core/quote',
    {
        name: 'fancy-quote',
        label: 'Fancy Quote',
    }
);

wp.blocks.registrBlockStyle(
    'core/button',
    {
        name: 'fancy-button',
        label: 'Fancy Button',
    }
);

// wp.blocks.unregisterBlockStyle( 'core/quote', 'fancy-quote' );

wp.domReady( function() {
    // wp.blocks.unregisterBlockStyle( 'core/quote', 'default' );
    wp.blocks.unregisterBlockStyle( 'core/quote', 'large' );
} );
