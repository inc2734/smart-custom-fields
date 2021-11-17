jQuery( function( $ ) {

	$( '.smart-cf-meta-box' ).each( function( i, e ) {
		var wrapper = $( e );
		var table_class = '.smart-cf-meta-box-table';

		/**
		 * ロード時に wysiwyg エディター用のテキストエリアがあったら wysiwyg 化する。
		 */
		wrapper.find( '.smart-cf-wp-editor' ).each( function( i, e ) {
			if ( $( this ).parents( table_class ).css( 'display' ) === 'none' ) {
				return true;
			}
			$( e ).attr( 'id', $( e ).attr( 'name' ).replace( /(\[|\]|\-)/ig, '_' ) );
			var editor_id = $( e ).attr( 'id' );
			var wrap = $( e ).parents( '.wp-editor-wrap' );
			initialize_editor( wrap, editor_id );

			var mceinit = scf_generate_mceinit( editor_id );
			tinyMCEPreInit.mceInit[editor_id] = mceinit;
			if ( typeof tinymce !== 'undefined' ) {
				tinymce.init( mceinit );
			}

			var qtinit = scf_generate_qtinit( editor_id );
			tinyMCEPreInit.qtInit[editor_id] = qtinit;
			if ( typeof quicktags !== 'undefined' ) {
				quicktags( qtinit );
				QTags._buttonsInit();
			}
		} );
	} );

	/**
	 * グループ追加ボタンを押したときに発火。
	 * wysiwyg エディター用のテキストエリアがあったら wysiwyg 化する。
	 */
	$( document ).on( 'smart-cf-after-add-group', function( e, data ) {
		var button = data.button;
		var clone  = data.clone;
		clone.find( '.smart-cf-wp-editor' ).each( function( i, e ) {
			$( e ).attr( 'id', $( e ).attr( 'name' ).replace( /(\[|\]|\-)/ig, '_' ) );
			var editor_id = $( e ).attr( 'id' );
			var wrap = 	$( e ).parents( '.wp-editor-wrap' );
			initialize_editor( wrap, editor_id );

			var mceinit = scf_generate_mceinit( editor_id );
			tinyMCEPreInit.mceInit[editor_id] = mceinit;
			if ( typeof tinymce !== 'undefined' ) {
				tinymce.init( mceinit );
			}

			var qtinit = scf_generate_qtinit( editor_id );
			tinyMCEPreInit.qtInit[editor_id] = qtinit;
			if ( typeof quicktags !== 'undefined' ) {
				quicktags( qtinit );
				QTags._buttonsInit();
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

			tinymce.execCommand( 'mceRemoveEditor', false, editor_id );

			var mceinit = scf_generate_mceinit( editor_id );
			tinyMCEPreInit.mceInit[editor_id] = mceinit;

			var qtinit = scf_generate_qtinit( editor_id );
			tinyMCEPreInit.qtInit[editor_id] = qtinit;
			if ( typeof quicktags !== 'undefined' ) {
				quicktags( qtinit );
				QTags._buttonsInit();
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

			var mceinit = scf_generate_mceinit( editor_id );
			tinyMCEPreInit.mceInit[editor_id] = mceinit;
			if ( typeof tinymce !== 'undefined' ) {
				tinymce.init( mceinit );
			}

			var qtinit = scf_generate_qtinit( editor_id );
			tinyMCEPreInit.qtInit[editor_id] = qtinit;
			if ( typeof quicktags !== 'undefined' ) {
				quicktags( qtinit );
				QTags._buttonsInit();
			}
		} );
	} );

	function initialize_editor( wrap, editor_id ) {
		wrap.attr( 'id', 'wp-' + editor_id + '-wrap' );
		wrap.find( 'a.add_media' ).attr( 'data-editor', editor_id );
		wrap.find( '.switch-tmce' )
			.attr( 'data-wp-editor-id', editor_id )
			.attr( 'id', editor_id + '-tmce' );
		wrap.find( '.switch-html' )
			.attr( 'data-wp-editor-id', editor_id )
			.attr( 'id', editor_id + '-html' );
		wrap.find( '.quicktags-toolbar' ).attr( 'id', 'qt_' + editor_id + '_toolbar' );
	}

	function scf_generate_mceinit( editor_id ) {
		var mceinit;
		if ( typeof tinyMCEPreInit.mceInit.content !== 'undefined' ) {
			mceinit = $.extend( true, {}, tinyMCEPreInit.mceInit.content );
			mceinit.selector = '#' + editor_id;
		} else {
			mceinit = {
				content_css: ['../wp-includes/js/tinymce/skins/wordpress/wp-content.css', '../wp-content/plugins/smart-custom-fields/css/wysiwyg.css'],
				menubar: false,
				plugins: "hr,wplink,fullscreen,wordpress,textcolor,paste,charmap,lists",
				toolbar1: "bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_adv,fullscreen",
				toolbar2: "formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help,code",
				convert_urls: false,
				theme: "modern",
				skin: "lightgray",
				wp_autoresize_on: true,
				wpautop: true,
				selector: '#' + editor_id
			};
		}
		return mceinit;
	}

	function scf_generate_qtinit( editor_id ) {
		var qtinit;
		if ( typeof tinyMCEPreInit.qtInit.content !== 'undefined' ) {
			qtinit = $.extend( true, {}, tinyMCEPreInit.qtInit.content );
			qtinit.id = editor_id;
		} else {
			qtinit = {
				id: editor_id,
				buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
			}
		}
		return qtinit;
	}
} );
