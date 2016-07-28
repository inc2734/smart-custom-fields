/**
 * editor-colorpicker.js
 * Version    : 1.0.1
 * Author     : inc2734
 * Created    : October 21, 2014
 * Modified   : July 28, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
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
