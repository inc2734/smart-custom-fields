/**
 * editor.js
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : September 30, 2014
 * Modified   :
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
jQuery( function( $ ) {

	var table_class = '.smart-cf-meta-box-table';

	/**
	 * 読み込みボタン
	 */
	$( '.smart-cf-meta-box .load-relation-posts' )
		.data( 'click_count', 0 )
		.click( function() {
			var parent = $( this ).parents( '.smart-cf-meta-box-table' );
			var click_count = $( this ).data( 'click_count' );
			var post_types = $( this ).data( 'post-types' );
			var btn_load = $( this );
			click_count ++;
			btn_load.data( 'click_count', click_count );
			btn_load.addClass( 'hide' );

			$.post( smart_cf_relation.endpoint, {
					action     : smart_cf_relation.action,
					nonce      : smart_cf_relation.nonce,
					click_count: click_count,
					post_types : post_types
				},
				function( response ) {
					$( response ).each( function( i, e ) {
						parent.find( '.smart-cf-relation-children-select ul' ).append(
							$( '<li />' )
								.attr( 'data-id', this.ID )
								.text( this.post_title )
						);
					} );
					if ( response ) {
						btn_load.removeClass( 'hide' );
					}
				}
			);
			return false;
		} );

	/**
	 * 選択肢
	 */
	var choices_li = '.smart-cf-meta-box .smart-cf-relation-children-select li';
	$( document ).on( 'click', choices_li, function() {
		var id = $( this ).data( 'id' );
		var parent = $( this ).parents( table_class );
		if ( parent.find( '.smart-cf-relation-right li[data-id="' + id + '"]' ).length === 0 ) {
			var clone = $( this ).clone();
			clone.append( $( '<span class="relation-remove">-</span>' ) );
			parent.find( '.smart-cf-relation-right ul' ).append( clone );
			update_relation_value( parent );
		}
	} );

	/**
	 * 選択済み項目の削除
	 */
	var relation_remove = '.smart-cf-meta-box .smart-cf-relation-right li .relation-remove';
	$( document ).on( 'click', relation_remove, function() {
		var li = $( this ).parent();
		var parent = li.parents( table_class );
		li.remove();
		update_relation_value( parent );
	} );

	/**
	 * update_relation_value
	 * @param dom table
	 */
	function update_relation_value( table ) {
		var hidden = table.find( 'input[type="hidden"]' );
		hidden.each( function( i, e ) {
			if ( i !== 0 ) {
				$( this ).remove();
			}
		} );
		table.find( '.smart-cf-relation-right li' ).each( function( i, e ) {
			var hidden_box = $( this ).parents( table_class ).find( '.smart-cf-relation-children-select' );
			var id = $( this ).data( 'id' );
			var clone = hidden.first().clone();
			var name = clone.attr( 'name' );
			clone.attr( 'name', name + '[]' );
			clone.val( id );
			hidden_box.append( clone );
		} );
	}
} );