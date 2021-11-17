jQuery( function( $ ) {
	$( '.smart-cf-meta-box-table' ).each( function( i, e ) {
		$( e ).find( '.smart-cf-datepicker' ).each( function( i, e ) {
			if ( $( e ).attr( 'disabled' ) !== 'disabled' ) {
				$( e ).datepicker( $( e ).data( 'js' ) );
			}
		} );
	} );

	$( document ).on( 'smart-cf-after-add-group', function( e, obj ) {
		var parent = $( obj.button ).parents( '.smart-cf-meta-box-repeat-tables' );
		parent.find( '.smart-cf-datepicker' ).each( function( i, e ) {
			if ( $( e ).attr( 'disabled' ) !== 'disabled' ) {
				$( e ).datepicker( $( e ).data( 'js' ) );
			}
		} );

		$( '.smart-cf-datetime_picker.add' ).each( function ( i, e ) {
			var data = e.getAttribute( 'data-js' );
			data = JSON.parse( data );
			data['enableTime'] = true;
			flatpickr( this, data );
			$( this ).removeClass( 'add' );
		} );
	} );
} );
