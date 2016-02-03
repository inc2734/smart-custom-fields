/**
 * editor.js
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : February 2, 2016
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
jQuery( function( $ ) {

	var table_class = 'tr';

	/**
	 * 初期化
	 * click_count はロードボタンを押すごとに加算。
	 * 検索ボックスが変更されるたびに 0 にリセットすること。
	 */
	$( '.smart-cf-meta-box .load-relation-terms' ).closest( table_class )
		.data( 'click_count', 0 )
		.data( 'search_timer', null )
		.data( 'recent_search_query', '' );

	/**
	 * 検索ボタン
	 */
	$( document ).on( 'keyup', '.smart-cf-meta-box .search-input-terms', function() {
		var parent = $( this ).closest( table_class );
		var search_timer = parent.data( 'search_timer' );
		clearTimeout( search_timer );

		parent.data( 'click_count', 0 );
		parent.find( '.smart-cf-relation-children-select ul li' ).remove();

		var search_query = $( this ).val();
		parent.data( 'recent_search_query', search_query );
		parent.data( 'search_timer', setTimeout( function() {
			get_terms( { search: search_query }, parent );
		}, 2000 ) );
	} );

	/**
	 * 読み込みボタン
	 */
	$( document ).on( 'click', '.smart-cf-meta-box .load-relation-terms', function() {
		var parent = $( this ).closest( table_class );
		var click_count = parent.data( 'click_count' );
		click_count ++;
		parent.data( 'click_count', click_count );
		var search_query = parent.data( 'recent_search_query' );
		if ( search_query ) {
			get_terms( { search: search_query }, parent );
		} else {
			get_terms( {}, parent );
		}
	} );

	/**
	 * クエリ
	 */
	function get_terms( args, table ) {
		var click_count   = table.data( 'click_count' );
		var taxonomies    = table.find( '.smart-cf-relation-left' ).data( 'taxonomies' );
		var btn_load      = table.find( '.load-relation-terms' );
		var btn_load_text = btn_load.text();
		btn_load.text( 'Now loading...' );

		args = $.extend( args, {
			action     : smart_cf_relation_taxonomies.action,
			nonce      : smart_cf_relation_taxonomies.nonce,
			click_count: click_count,
			taxonomies : taxonomies
		} );
		$.post(
			smart_cf_relation_taxonomies.endpoint,
			args,
			function( response ) {
				btn_load.addClass( 'hide' );
				$( response ).each( function( i, e ) {
					table.find( '.smart-cf-relation-children-select ul' ).append(
						$( '<li />' )
							.attr( 'data-id', this.term_id )
							.text( this.name )
					);
				} );

				btn_load.text( btn_load_text );
				btn_load.removeClass( 'hide' );
			}
		);
		return false;
	}

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
