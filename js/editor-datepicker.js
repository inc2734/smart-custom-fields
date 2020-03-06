/**
 * editor-datepicker.js
 * Version    : 1.0.1
 * Author     : inc2734
 * Created    : January 18, 2015
 * Modified   : July 28, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
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
