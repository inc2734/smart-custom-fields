jQuery( function( $ ) {

	var table_class = 'tr';

	/**
	 * 選択肢
	 */
	var choices_li = '.smart-cf-relation-children-select li';
	$( '.smart-cf-meta-box' ).on( 'click', choices_li, function() {
		var id = $( this ).data( 'id' );
		var parent = $( this ).closest( table_class );
		var limit = parent.find( '.smart-cf-relation-left' ).data( 'limit' );

		if ( limit > 0 && limit <= parent.find( '.smart-cf-relation-right li' ).length ) {
			return true;
		}

		if ( parent.find( '.smart-cf-relation-right li[data-id="' + id + '"]' ).length !== 0 ) {
			return true;
		}

		var clone = $( this ).clone();
		clone
			.prepend( $( '<span class="smart-cf-icon-handle dashicons dashicons-menu"></span>' ) )
			.append(  $( '<span class="relation-remove">-</span>' ) );
		parent.find( '.smart-cf-relation-right ul' ).append( clone );
		update_relation_value( $( this ).closest( 'tr' ) );
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
	$( '.smart-cf-meta-box' ).find( '.smart-cf-relation-right ul' )
		.on( 'mousedown', function( event ) {
			event.stopPropagation();
		} )
		.sortable( {
			handle: '.smart-cf-icon-handle',
			update: function() {
				update_relation_value( $( this ).closest( 'tr' ) );
			}
		} );

} );
