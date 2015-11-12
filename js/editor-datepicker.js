/**
 * editor-datepicker.js
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : January 18, 2015
 * Modified   :
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

	$( document ).on( 'smart-cf-after-add-group', function( e, button ) {
		var parent = $( button ).parents( '.smart-cf-meta-box-repeat-tables' );
		parent.find( '.smart-cf-datepicker' ).each( function( i, e ) {
			if ( $( e ).attr( 'disabled' ) !== 'disabled' ) {
				$( e ).datepicker( $( e ).data( 'js' ) );
			}
		} );
	} );
} );