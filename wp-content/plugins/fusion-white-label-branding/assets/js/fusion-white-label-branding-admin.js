( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function( $ ) {

		jQuery( '.fusion-white-label-branding-admin-toggle-heading' ).on( 'click', function() {
			jQuery( this ).parent().find( '.fusion-white-label-branding-admin-toggle-content' ).slideToggle( 300 );

			if ( jQuery( this ).find( '.fusion-white-label-branding-admin-toggle-icon' ).hasClass( 'fusion-plus' ) ) {
				jQuery( this ).find( '.fusion-white-label-branding-admin-toggle-icon' ).removeClass( 'fusion-plus' ).addClass( 'fusion-minus' );
			} else {
				jQuery( this ).find( '.fusion-white-label-branding-admin-toggle-icon' ).removeClass( 'fusion-minus' ).addClass( 'fusion-plus' );
			}

		} );

		// Convert color-field input to color picker.
		jQuery( '.color-field' ).wpColorPicker();

		// Handle the radio button.
		jQuery( '.ui-buttonset .ui-button' ).on( 'click', function( e ) {
			e.preventDefault();

			jQuery( this ).parent().find( '.button-set-value' ).val( jQuery( this ).data( 'value' ) );
			jQuery( this ).parent().find( '.ui-button' ).removeClass( 'ui-state-active' );
			jQuery( this ).addClass( 'ui-state-active' );
		});

		// Handle the image upload.
		jQuery( '.button-upload-image' ).click( function( e ) {
			var imageUploader = '',
			    title = jQuery( this ).data( 'title' ),
			    buttonTitle = jQuery( this ).data( 'button-title' ),
			    imageID = jQuery( this ).data( 'image-id' ),
			    attachment,
			    removeButton = jQuery( this ).next( '.button-remove-image' );

			e.preventDefault();

			imageUploader = wp.media( {
				title: title,
				button: {
					text: buttonTitle
				},
				multiple: false  // Set this to true to allow multiple files to be selected.
			} )
			.on( 'select', function() {
				attachment = imageUploader.state().get( 'selection' ).first().toJSON();
				jQuery( '.' + imageID + '_preview' ).html( '<img src="' + attachment.url + '">' );
				jQuery( '#' + imageID ).val( attachment.url );
				removeButton.show();
			})
			.open();
		});
	} );
} )( jQuery );
