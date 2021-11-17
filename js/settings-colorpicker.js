jQuery( function( $ ) {
	$( document ).on( 'smart-cf-setting-field-open', function( e, options ) {
		if ( $( options ).hasClass( 'smart-cf-field-options-colorpicker' ) ) {
			$( options ).find( '.default-option' ).each( function( i, e ) {
				$( e ).wpColorPicker();
			} );
		}
	} );
	$( document ).on( 'smart-cf-setting-show-options', function( e, options ) {
		if ( $( options ).hasClass( 'smart-cf-field-options-colorpicker' ) ) {
			$( options ).find( '.default-option' ).each( function( i, e ) {
				$( e ).wpColorPicker();
			} );
		}
	} );
} );
