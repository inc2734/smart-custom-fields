/**
 * editor.js
 * Version    : 1.1.0
 * Author     : inc2734
 * Created    : September 30, 2014
 * Modified   : November 12, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
jQuery( function( $ ) {

	var table_class = '.smart-cf-meta-box-table';
	
	/**
	 * 検索ボタン
	 */
	var search_query;
	var search_timer;
	$( '.smart-cf-meta-box .search-input' ).keyup( function() {
		$( '.smart-cf-meta-box .load-relation-posts' ).data( 'click_count', -1 );
		clearTimeout( search_timer );
		var parent = $( this ).closest( '.smart-cf-meta-box-table' );
		parent.find( '.smart-cf-relation-children-select ul li' ).remove();
		
		var load_btn = parent.find( '.load-relation-posts' );
		
		search_query = $( this ).val();
		if ( !search_query ) {
			load_btn.show();
		} else {
			load_btn.hide();
		}
		
		search_timer = setTimeout( function() {
			if ( !search_query ) {
				return false;
			}
			
			var post_types = parent.find( '.smart-cf-relation-left' ).data( 'post-types' );

			$.post( smart_cf_relation.endpoint, {
					action     : smart_cf_relation.action,
					nonce      : smart_cf_relation.nonce,
					post_types : post_types,
					s          : search_query
				},
				function( response ) {
					$( response ).each( function( i, e ) {
						parent.find( '.smart-cf-relation-children-select ul' ).append(
							$( '<li />' )
								.attr( 'data-id', this.ID )
								.text( this.post_title )
						);
					} );
				}
			);
		}, 2000 );
		return false;
	} );

	/**
	 * 読み込みボタン
	 */
	$( '.smart-cf-meta-box .load-relation-posts' )
		.data( 'click_count', 0 )
		.click( function() {
			var parent = $( this ).closest( '.smart-cf-meta-box-table' );
			var click_count = $( this ).data( 'click_count' );
			var post_types = parent.find( '.smart-cf-relation-left' ).data( 'post-types' );
			var btn_load = $( this );
			click_count ++;
			btn_load.data( 'click_count', click_count );
			var btn_load_text = btn_load.text();
			btn_load.text( 'Now loading...' );

			$.post( smart_cf_relation.endpoint, {
					action     : smart_cf_relation.action,
					nonce      : smart_cf_relation.nonce,
					click_count: click_count,
					post_types : post_types
				},
				function( response ) {
					btn_load.addClass( 'hide' );
					$( response ).each( function( i, e ) {
						parent.find( '.smart-cf-relation-children-select ul' ).append(
							$( '<li />' )
								.attr( 'data-id', this.ID )
								.text( this.post_title )
						);
					} );
					if ( response ) {
						btn_load.text( btn_load_text );
						btn_load.removeClass( 'hide' );
					}
				}
			);
			return false;
		} );

	/**
	 * 選択肢
	 */
	var choices_li = '.smart-cf-relation-children-select li';
	$( '.smart-cf-meta-box' ).on( 'click', choices_li, function() {
		var id = $( this ).data( 'id' );
		var parent = $( this ).closest( table_class );
		if ( parent.find( '.smart-cf-relation-right li[data-id="' + id + '"]' ).length === 0 ) {
			var clone = $( this ).clone();
			clone
				.prepend( $( '<span class="smart-cf-icon-handle dashicons dashicons-menu"></span>' ) )
				.append(  $( '<span class="relation-remove">-</span>' ) );
			parent.find( '.smart-cf-relation-right ul' ).append( clone );
			update_relation_value( $( this ).closest( 'tr' ) );
		}
	} );

	/**
	 * 選択済み項目の削除
	 */
	var relation_remove = '.smart-cf-relation-right li .relation-remove';
	$( '.smart-cf-meta-box' ).on( 'click', relation_remove, function() {
		var tr = $( this ).closest( 'tr' );
		$( this ).parent().remove();
		update_relation_value( tr );
	} );

	/**
	 * update_relation_value
	 * @param dom tr
	 */
	function update_relation_value( tr ) {
		var hidden = tr.find( 'input[type="hidden"]' );
		hidden.each( function( i, e ) {
			if ( i !== 0 ) {
				$( this ).remove();
			}
		} );
		tr.find( '.smart-cf-relation-right li' ).each( function( i, e ) {
			var hidden_box = $( this ).closest( table_class ).find( '.smart-cf-relation-children-select' );
			var id = $( this ).data( 'id' );
			var clone = hidden.first().clone();
			var name = clone.attr( 'name' );
			clone.attr( 'name', name + '[]' );
			clone.val( id );
			hidden_box.append( clone );
		} );
	}

	/**
	 * sortable
	 */
	$( '.smart-cf-meta-box .smart-cf-relation-right ul' ).sortable( {
		handle: '.smart-cf-icon-handle',
		update: function() {
			update_relation_value( $( this ).closest( 'tr' ) );
		}
	} );
} );
