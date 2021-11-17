jQuery( function( $ ) {
	$( '.smart-cf-meta-box-table' ).each( function( i, e ) {
		$( e ).find( '.smart-cf-colorpicker' ).each( function( i, e ) {
			if ( $( e ).attr( 'disabled' ) !== 'disabled' ) {
				$( e ).wpColorPicker();
			}
		} );
	} );

	$( document ).on( 'smart-cf-after-add-group', function( e, obj ) {
		var parent = $( obj.button ).parents( '.smart-cf-meta-box-repeat-tables' );
		parent.find( '.smart-cf-colorpicker' ).each( function( i, e ) {
			if ( $( e ).attr( 'disabled' ) !== 'disabled' ) {
				$( e ).wpColorPicker();
			}
		} );
	} );
} );
