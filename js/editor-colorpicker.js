/**
 * editor-colorpicker.js
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : October 21, 2014
 * Modified   :
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

	$( document ).on( 'smart-cf-after-add-group', function( e, button ) {
		var parent = $( button ).parents( '.smart-cf-meta-box-repeat-tables' );
		parent.find( '.smart-cf-colorpicker' ).each( function( i, e ) {
			if ( $( e ).attr( 'disabled' ) !== 'disabled' ) {
				$( e ).wpColorPicker();
			}
		} );
	} );
} );