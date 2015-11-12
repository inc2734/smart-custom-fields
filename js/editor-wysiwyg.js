/**
 * editor-wysiwyg.js
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : September 28, 2014
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
jQuery( function( $ ) {

	/**
	 * グループ追加ボタンを押したときに発火。
	 * wysiwyg エディター用のテキストエリアがあったら wysiwyg 化する。
	 */
	$( document ).on( 'smart-cf-after-add-group', function( e, button ) {
		var parent = $( button ).parents( '.smart-cf-meta-box-repeat-tables' );
		parent.find( '.smart-cf-wp-editor' ).each( function( i, e ) {
			if ( $( this ).css( 'display' ) !== 'none' ) {
				var editor_id = $( this ).attr( 'id' );
				if ( editor_id ) {
					$( this ).parents( '.wp-editor-wrap' ).find( 'a.add_media' ).attr( 'data-editor', editor_id );
					tinymce.execCommand( 'mceAddEditor', false, editor_id );
				}
			}
		} );
	} );

	/**
	 * ドラッグしたときに発火。
	 * wysiwyg エディター用のテキストエリアをオフる。
	 */
	$( document ).on( 'smart-cf-repeat-table-sortable-start', function( e, ui ) {
		$( ui ).find( '.smart-cf-wp-editor' ).each( function( i, e ) {
			var editor_id = $( this ).attr( 'id' );
			if ( editor_id ) {
				tinymce.execCommand( 'mceRemoveEditor', false, editor_id );
			}
		} );
	} );

	/**
	 * ドロップしたときに発火。
	 * wysiwyg エディター用のテキストエリアを wysiwyg 化する。
	 */
	$( document ).on( 'smart-cf-repeat-table-sortable-stop', function( e, ui ) {
		$( ui ).find( '.smart-cf-wp-editor' ).each( function( i, e ) {
			var editor_id = $( this ).attr( 'id' );
			if ( editor_id ) {
				tinymce.execCommand( 'mceAddEditor', false, editor_id );
			}
		} );
	} );

} );