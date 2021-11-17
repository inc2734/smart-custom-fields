jQuery( function( $ ) {
	$( document ).on( 'smart-cf-setting-field-open', function( e, options ) {
		if ( $( options ).hasClass( 'smart-cf-field-options-datepicker' ) ) {
			$( options ).find( '.default-option' ).each( function( i, e ) {
				$( e ).datepicker( $( e ).data( 'js' ) );
			} );
		}
	} );
	$( document ).on( 'smart-cf-setting-show-options', function( e, options ) {
		if ( $( options ).hasClass( 'smart-cf-field-options-datepicker' ) ) {
			$( options ).find( '.default-option' ).each( function( i, e ) {
				$( e ).datepicker( $( e ).data( 'js' ) );
			} );
		}
	} );
} );
