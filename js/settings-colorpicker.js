/**
 * settings-colorpicker.js
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : March 10, 2014
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
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