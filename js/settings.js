/**
 * settings.js
 * Version    : 1.1.0
 * Author     : inc2734
 * Created    : September 23, 2014
 * Modified   : July 14, 2018
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
jQuery( function( $ ) {
	$( '.smart-cf-fields-wrapper' ).each( function( i, e ) {
		var wrapper = $( e );
		var btn_add_group    = wrapper.find( '.btn-add-group' );
		var btn_remove_group = wrapper.find( '.btn-remove-group' );
		var btn_add_field    = wrapper.find( '.btn-add-field' );
		var btn_remove_field = wrapper.find( '.btn-remove-field' );
		var group_class      = '.smart-cf-group';
		var field_class      = '.smart-cf-field';
		var duplicate_alert_class = '.smart-cf-duplicate-alert';
		var options          = wrapper.find( '.smart-cf-field-options' );
		var cnt = wrapper.find( field_class ).length;

		/**
		 * 重複エラーメッセージ表示 & 重複時の更新不可
		 */
		wrapper.find( 'input[class="smart-cf-group-name"], input[class="smart-cf-field-name"]' ).keyup( function() {
			var val = $( this ).val();
			var cnt = 0;
			wrapper.find( 'input[class="smart-cf-group-name"], input[class="smart-cf-field-name"]' ).each( function( i, e ) {
				if ( val === $( this ).val() && val !== '' ) {
					cnt ++;
				}
			} );
			if ( cnt > 1 ) {
				if ( $( this ).siblings( duplicate_alert_class ).length < 1 ) {
					$( this ).after(
						$( '<div class="smart-cf-alert" />' )
							.addClass( duplicate_alert_class.replace( '.', '' ) )
							.text( smart_cf_settings.duplicate_alert )
					);
				}
				cnt = 0;
			} else {
				$( this ).siblings( duplicate_alert_class ).remove();
			}

			if ( $( duplicate_alert_class ).length ) {
				$( '#publish' ).attr( 'disabled', 'disabled' );
			} else {
				$( '#publish' ).removeAttr( 'disabled' );
			}
		} );

		/**
		 * sortable
		 */
		$( '.smart-cf-groups' ).sortable( {
			cursor: 'move',
			handle: '.smart-cf-icon-handle'
		} );
		$( '.smart-cf-fields' ).sortable( {
			cursor: 'move',
			handle: '.smart-cf-icon-handle'
		} );

		/**
		 * フィールドの開閉
		 */
		$( '.field-label' ).click( function() {
			var field_label = $( this );
			var table = $( this ).parents( field_class ).find( 'table' );
			if ( table.hasClass( 'hide' ) ) {
				field_label.html( "&nbsp;" );
				table.fadeIn( 'fast', function() {
					$( this ).removeClass( 'hide' );
					table.find( '.smart-cf-field-options' ).each( function( i, e ) {
						$( this ).trigger( 'smart-cf-setting-field-open', e );
					} );
				} );
			} else {
				var field_options = table.find( '.smart-cf-field-options:visible' );
				var label = field_options.find( '.smart-cf-field-label' ).val();
				var name = $( '<small>' ).text( '[ ' + field_options.find('.smart-cf-field-name').val() + ' ]' );

				if ( !label ) {
					label = field_options.find( '.smart-cf-field-name' ).val();
				}
				table.fadeOut( 'fast', function() {
					$( this ).addClass( 'hide' );
					field_label.text( label + " " ).append( name );
				} );
			}
		} );

		/**
		 * グループ追加ボタン
		 */
		btn_add_group.click( function() {
			cnt ++;
			var group = wrapper.find( group_class );
			var group_clone = group.first().clone( true, true );
			group.last().after( group_clone.fadeIn( 'fast', function() {
				$( this ).removeClass( 'hide' );
			} ) );

			var field = group_clone.find( field_class );
			var field_clone = field.first().clone( true, true );
			field.last().after( field_clone.removeClass( 'hide' ) );

			group_clone.find( 'input, select, textarea' ).each( function( i, e ) {
				$( this ).attr( 'name',
					$( this ).attr( 'name' ).replace(
						/^(smart-custom-fields)\[\d+\]/,
						'$1[' + cnt + ']'
					)
				);
			} );

			field_clone.find( 'input, select, textarea' ).each( function( i, e ) {
				$( this ).attr( 'name',
					$( this ).attr( 'name' ).replace(
						/^(smart-custom-fields)\[.+?\](\[fields\])\[\d+?\]/,
						'$1[' + cnt + ']$2[' + cnt + ']'
					)
				);
			} );
		} );

		/**
		 * グループ削除ボタン
		 */
		btn_remove_group.click( function() {
			$( this ).parents( group_class ).fadeOut( 'fast', function() {
				$( this ).remove();
			} );
		} );

		/**
		 * フィールド追加ボタン
		 */
		btn_add_field.click( function() {
			cnt ++;
			var group = $( this ).parents( group_class );
			var field = group.find( field_class );
			var clone = field.first().clone( true, true );
			field.last().after( clone.fadeIn( 'fast', function() {
				$( this ).removeClass( 'hide' );
			} ) );

			clone.find( 'input, select, textarea' ).each( function( i, e ) {
				$( this ).attr( 'name',
					$( this ).attr( 'name' ).replace(
						/^(smart-custom-fields\[.+?\]\[fields\])\[\d+?\]/,
						'$1[' + cnt + ']'
					)
				);
			} );
		} );

		/**
		 * フィールド削除ボタン
		 */
		btn_remove_field.click( function() {
			$( this ).parents( field_class ).fadeOut( 'fast', function() {
				$( this ).remove();
			} );
		} );

		/**
		 * 選択項目オプション
		 */
		options.find( 'input, textarea, select' ).attr( 'disabled', 'disabled' );
		wrapper.find( '.smart-cf-field-select' ).each( function( i, e ) {
			var selected_type = $( this ).val();
			$( this ).parents( field_class ).find( '.smart-cf-field-options-' + selected_type )
				.removeClass( 'hide' )
				.find( 'input, textarea, select' ).removeAttr( 'disabled' );
		} );

		wrapper.find( '.smart-cf-field-select' ).change( function() {
			var field = $( this ).parents( field_class );
			var val = $( this ).val();

			var hide_options = field.find( '.smart-cf-field-options' );
			hide_options.addClass( 'hide' );
			hide_options.find( 'input, textarea, select' ).attr( 'disabled', 'disabled' );

			var show_options = field.find( '.smart-cf-field-options-' + val );
			show_options.find( 'input, textarea, select' ).removeAttr( 'disabled' );
			show_options.removeClass( 'hide' );
			show_options.trigger( 'smart-cf-setting-show-options', show_options );
		} );

		/**
		 * リピートボタンクリック時
		 */
		wrapper.find( '.smart-cf-group-repeat input' ).click( function() {
			var group = $( this ).parents( group_class );
			var names = group.find( '.smart-cf-group-names' );
			var btn_add_field = group.find( '.btn-add-field' );
			if ( $( this ).prop( 'checked' ) ) {
				names.removeClass( 'hide' );
				btn_add_field.removeClass( 'hide' );
			} else {
				names.addClass( 'hide' );
				btn_add_field.addClass( 'hide' );
			}
		} );

		/**
		 * Convert string to slug
		 * https://gist.github.com/codeguy/6684588
		 */
		function string_to_slug(str) {
			str = str.replace(/^\s+|\s+$/g, ""); // trim
			str = str.toLowerCase();

			// remove accents, swap ñ for n, etc
			var from = "åàáãäâèéëêìíïîòóöôùúüûñç·/-,:;";
			var to = "aaaaaaeeeeiiiioooouuuunc______";

			for (var i = 0, l = from.length; i < l; i++) {
				str = str.replace(new RegExp(from.charAt(i), "g"), to.charAt(i));
			}

			str = str
				.replace(/[^a-z0-9 -]/g, "") // remove invalid chars
				.replace(/\s+/g, "_") // collapse whitespace and replace by -
				.replace(/-+/g, "_"); // collapse dashes

			return str;
		}

		/**
		 * フィールド名入力ボックス
		 */
		wrapper.find( '.smart-cf-field-name' ).focus( function() {
			var field     = $( this ).parents( '.smart-cf-field-options' );
			var label_val  = field.find( '.smart-cf-field-label' ).val();
			var name_val = $( this ).val();
			if ( label_val && !name_val) {
				$( this ).val( string_to_slug(label_val) );
			}
		} );
	} );

	/**
	 * Add autocomplete (selectivity plguin) in posts condition field
	 * https://github.com/arendjr/selectivity
	 */
	$('#smart-cf-autocomplete-condition-post').selectivity({
		data: smart_cf_saved_posts,
		multiple: true,
		placeholder: smart_cf_settings.autocomplete_placeholder,
		ajax: {
			url: smart_cf_settings.rest_api_url,
			quietMillis: 200,
			params: function(term, offset) {
				return {
					_wpnonce: smart_cf_settings.nonce,
				};
			},
		},
		templates: {
			multipleSelectInput: function(options) {
				return (
							'<div class="selectivity-multiple-input-container">' +
							(options.enabled ?
								'<input type="text" autocomplete="off" autocorrect="off" ' +
								'autocapitalize="off" class="selectivity-multiple-input">' :
								'<div class="selectivity-multiple-input ' + 'selectivity-placeholder"></div>') +
							'<div class="selectivity-clearfix"></div>' +
							'</div>'
						);
			},
			multipleSelectedItem: function(options) {
				var extraClass = options.highlighted ? ' highlighted' : '';
						return (
							'<span class="selectivity-multiple-selected-item button button-primary' +
							extraClass +
							'" ' +
							'data-item-id="' +
							options.id +
							'">' +
							(options.removable ?
								'<a class="selectivity-multiple-selected-item-remove">' +
								'x' +
								'</a>' :
								'') +
							options.id +
							'</span>'
						); //options.text
			},
			dropdown: function(options) {
				var extraClass = options.dropdownCssClass ? ' ' + options.dropdownCssClass : '',
				searchInput = '';
						if (options.showSearchInput) {
					extraClass += ' has-search-input';
							var placeholder = options.searchInputPlaceholder;
							searchInput =
								'<div class="selectivity-search-input-container">' +
								'<input type="text" class="selectivity-search-input"' +
								(placeholder ? ' placeholder="' + escape(placeholder) + '"' : '') +
								'>' +
								'</div>';
				}

				return (
							'<div class="selectivity-dropdown' +
							extraClass +
							'">' +
							searchInput +
							'<div class="selectivity-results-container"></div>' +
							'</div>'
						);
			},
			loading: function() {
					return '<div class="selectivity-loading">' + smart_cf_settings.loading + '</div>';
			},
			loadMore: function() {
					return '<div class="selectivity-load-more">' + smart_cf_settings.load_more + '</div>';
			},
		}
	});
	$('#smart-cf-autocomplete-condition-post').on('change', function() {
		var data = $(this).selectivity('value');
		$('[name="smart-cf-condition-post-ids"]').val(data);
	});

	/**
	 * Add IOS style for checkboxes
	 */
	$('.smart-cf-group .smart-cf-group-repeat label, #smart-cf-meta-box-condition-post, #smart-cf-meta-box-condition-profile, #smart-cf-meta-box-condition-taxonomy, #smart-cf-meta-box-condition-options-page')
		.find('input[type=checkbox]')
		.iosCheckbox();

} );
