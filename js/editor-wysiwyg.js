jQuery(function ($) {
	// タイミング設定定数
	var TIMING = {
		FIREFOX_INIT_DELAY: 800,
		FIREFOX_EDITOR_DELAY: 300,
		FIREFOX_QUICKTAGS_DELAY: 350,
		FIREFOX_DROP_DELAY: 200,
		FIREFOX_GROUP_DELAY: 200,
		DEFAULT_INIT_DELAY: 100,
		DEFAULT_EDITOR_DELAY: 100,
		DEFAULT_QUICKTAGS_DELAY: 150,
		DEFAULT_DROP_DELAY: 100,
		DEFAULT_GROUP_DELAY: 50,
		MAX_RETRIES: 50,
		RETRY_INTERVAL: 100,
		QUICKTAGS_OFFSET: 50
	};

	// Firefox判定
	function isFirefox() {
		return navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
	}

	// DOM要素の存在と準備状態を厳密にチェック
	function isDOMElementReady(element_id) {
		var element = document.getElementById(element_id);
		if (!element) {
			return false;
		}

		// 要素が実際にDOMに接続されているかチェック
		if (!document.body.contains(element)) {
			return false;
		}

		// 要素が表示可能な状態かチェック
		var style = window.getComputedStyle(element);
		if (style.display === 'none' || style.visibility === 'hidden') {
			return false;
		}

		return true;
	}

	// TinyMCEとその依存関係の完全な準備チェック
	function isTinyMCEReady() {
		return (
			typeof tinymce !== 'undefined' &&
			typeof tinyMCEPreInit !== 'undefined' &&
			typeof quicktags !== 'undefined' &&
			typeof QTags !== 'undefined' &&
			tinymce.init &&
			tinymce.execCommand
		);
	}

	// 安全なエディタ初期化（再帰的チェック付き）
	function safelyInitializeEditors(maxRetries = 50) {
		if (maxRetries <= 0) {
			console.warn('Smart Custom Fields: TinyMCE initialization timeout');
			return;
		}

		// TinyMCEの準備チェック
		if (!isTinyMCEReady()) {
			setTimeout(function () {
				safelyInitializeEditors(maxRetries - 1);
			}, TIMING.RETRY_INTERVAL);
			return;
		}

		$('.smart-cf-meta-box').each(function (i, e) {
			var wrapper = $(e);
			var table_class = '.smart-cf-meta-box-table';

			wrapper.find('.smart-cf-wp-editor').each(function (i, e) {
				var $textarea = $(e);

				// 親テーブルが非表示の場合はスキップ
				if ($textarea.parents(table_class).css('display') === 'none') {
					return true;
				}

				// エディタIDの生成と設定
				var name_attr = $textarea.attr('name');
				if (!name_attr) {
					console.warn('Smart Custom Fields: textarea name attribute is missing');
					return true;
				}

				var editor_id = name_attr.replace(/(\[|\]|\-)/ig, '_');
				$textarea.attr('id', editor_id);

				// DOM要素の準備チェック
				if (!isDOMElementReady(editor_id)) {
					console.warn('Smart Custom Fields: DOM element not ready for ' + editor_id);
					return true;
				}

				var wrap = $textarea.parents('.wp-editor-wrap');
				if (!wrap.length) {
					console.warn('Smart Custom Fields: wp-editor-wrap not found for ' + editor_id);
					return true;
				}

				// 既存のTinyMCEインスタンスをクリーンアップ
				if (tinymce.get(editor_id)) {
					try {
						tinymce.execCommand('mceRemoveEditor', false, editor_id);
					} catch (cleanup_error) {
						console.warn('Smart Custom Fields: Failed to cleanup existing editor ' + editor_id, cleanup_error);
					}
				}

				// 共通エディタ初期化を使用
				initializeEditorWithTiming(wrap, editor_id, false);
			});
		});
	}

	// エディタ初期化の共通関数（タイミング制御付き）
	function initializeEditorWithTiming(wrap, editor_id, isNewGroup) {
		if (!wrap || !wrap.length || !editor_id) {
			return false;
		}

		try {
			// DOM要素の初期化
			initialize_editor(wrap, editor_id);

			// TinyMCE初期化
			var mceinit = scf_generate_mceinit(editor_id);
			if (mceinit) {
				tinyMCEPreInit.mceInit[editor_id] = mceinit;

				var editor_delay = isFirefox() ? TIMING.FIREFOX_EDITOR_DELAY : TIMING.DEFAULT_EDITOR_DELAY;

				setTimeout(function () {
					if (isTinyMCEReady()) {
						try {
							tinymce.init(mceinit);
						} catch (error) {
							console.error('Smart Custom Fields: TinyMCE init failed for ' + editor_id, error);
						}
					}
				}, editor_delay);
			}

			// Quicktags初期化
			var qtinit = scf_generate_qtinit(editor_id);
			if (qtinit) {
				tinyMCEPreInit.qtInit[editor_id] = qtinit;

				var quicktags_delay = isFirefox() ?
					TIMING.FIREFOX_QUICKTAGS_DELAY :
					(TIMING.DEFAULT_QUICKTAGS_DELAY + TIMING.QUICKTAGS_OFFSET);

				setTimeout(function () {
					try {
						quicktags(qtinit);
						if (QTags && QTags._buttonsInit) {
							QTags._buttonsInit();
						}
					} catch (error) {
						console.error('Smart Custom Fields: Quicktags init failed for ' + editor_id, error);
					}
				}, quicktags_delay);
			}

			return true;
		} catch (error) {
			console.error('Smart Custom Fields: Editor initialization failed for ' + editor_id, error);
			return false;
		}
	}

	// 初期化のトリガー（ブラウザ別）
	if (isFirefox()) {
		// Firefoxの場合：DOMContentLoaded + 長めの遅延
		$(document).ready(function () {
			setTimeout(safelyInitializeEditors, TIMING.FIREFOX_INIT_DELAY);
		});
	} else {
		// その他のブラウザ：window.load
		$(window).on('load', function () {
			setTimeout(safelyInitializeEditors, TIMING.DEFAULT_INIT_DELAY);
		});
	}

	/**
	 * グループ追加時の処理
	 */
	$(document).on('smart-cf-after-add-group', function (e, data) {
		var button = data.button;
		var clone = data.clone;

		// 少し遅延してから処理（DOM更新の完了を待つ）
		var group_delay = isFirefox() ? TIMING.FIREFOX_GROUP_DELAY : TIMING.DEFAULT_GROUP_DELAY;

		setTimeout(function () {
			clone.find('.smart-cf-wp-editor').each(function (i, e) {
				var $textarea = $(e);
				var name_attr = $textarea.attr('name');

				if (!name_attr) {
					return true;
				}

				var editor_id = name_attr.replace(/(\[|\]|\-)/ig, '_');
				$textarea.attr('id', editor_id);

				// DOM要素の準備チェック
				if (!isDOMElementReady(editor_id)) {
					console.warn('Smart Custom Fields: DOM element not ready for new group editor ' + editor_id);
					return true;
				}

				var wrap = $textarea.parents('.wp-editor-wrap');
				if (!wrap.length) {
					return true;
				}

				// 共通エディタ初期化を使用（新規グループフラグを立てる）
				initializeEditorWithTiming(wrap, editor_id, true);
			});
		}, group_delay);
	});

	/**
	 * ドラッグ開始時の処理
	 */
	$(document).on('smart-cf-repeat-table-sortable-start', function (e, ui) {
		$(ui).find('.smart-cf-wp-editor').each(function (i, e) {
			var editor_id = $(this).attr('id');

			if (!editor_id) {
				return true;
			}

			try {
				if (tinymce && tinymce.get(editor_id)) {
					tinymce.execCommand('mceRemoveEditor', false, editor_id);
				}
			} catch (error) {
				console.warn('Smart Custom Fields: Failed to remove editor during drag ' + editor_id, error);
			}

			// 設定の準備
			try {
				var mceinit = scf_generate_mceinit(editor_id);
				if (mceinit) {
					tinyMCEPreInit.mceInit[editor_id] = mceinit;
				}

				var qtinit = scf_generate_qtinit(editor_id);
				if (qtinit) {
					tinyMCEPreInit.qtInit[editor_id] = qtinit;
					quicktags(qtinit);
					if (QTags && QTags._buttonsInit) {
						QTags._buttonsInit();
					}
				}
			} catch (error) {
				console.error('Smart Custom Fields: Configuration failed during drag for ' + editor_id, error);
			}
		});
	});

	/**
	 * ドロップ完了時の処理
	 */
	$(document).on('smart-cf-repeat-table-sortable-stop', function (e, ui) {
		// DOM更新の完了を待つ
		var drop_delay = isFirefox() ? TIMING.FIREFOX_DROP_DELAY : TIMING.DEFAULT_DROP_DELAY;

		setTimeout(function () {
			$(ui).find('.smart-cf-wp-editor').each(function (i, e) {
				var editor_id = $(this).attr('id');

				if (!editor_id || !isDOMElementReady(editor_id)) {
					return true;
				}

				try {
					var mceinit = scf_generate_mceinit(editor_id);
					if (mceinit) {
						tinyMCEPreInit.mceInit[editor_id] = mceinit;

						var editor_delay = isFirefox() ? TIMING.FIREFOX_EDITOR_DELAY : TIMING.DEFAULT_EDITOR_DELAY;

						setTimeout(function () {
							if (isTinyMCEReady()) {
								try {
									tinymce.init(mceinit);
								} catch (error) {
									console.error('Smart Custom Fields: TinyMCE reinit failed after drop for ' + editor_id, error);
								}
							}
						}, editor_delay);
					}

					var qtinit = scf_generate_qtinit(editor_id);
					if (qtinit) {
						tinyMCEPreInit.qtInit[editor_id] = qtinit;

						var quicktags_delay = isFirefox() ? TIMING.FIREFOX_QUICKTAGS_DELAY : TIMING.DEFAULT_QUICKTAGS_DELAY;

						setTimeout(function () {
							try {
								quicktags(qtinit);
								if (QTags && QTags._buttonsInit) {
									QTags._buttonsInit();
								}
							} catch (error) {
								console.error('Smart Custom Fields: Quicktags reinit failed after drop for ' + editor_id, error);
							}
						}, quicktags_delay);
					}

				} catch (error) {
					console.error('Smart Custom Fields: Editor reinitialization failed after drop for ' + editor_id, error);
				}
			});
		}, drop_delay);
	});

	function initialize_editor(wrap, editor_id) {
		if (!wrap || !wrap.length || !editor_id) {
			return false;
		}

		try {
			wrap.attr('id', 'wp-' + editor_id + '-wrap');
			wrap.find('a.add_media').attr('data-editor', editor_id);
			wrap.find('.switch-tmce')
				.attr('data-wp-editor-id', editor_id)
				.attr('id', editor_id + '-tmce');
			wrap.find('.switch-html')
				.attr('data-wp-editor-id', editor_id)
				.attr('id', editor_id + '-html');
			wrap.find('.quicktags-toolbar').attr('id', 'qt_' + editor_id + '_toolbar');

			return true;
		} catch (error) {
			console.error('Smart Custom Fields: initialize_editor failed for ' + editor_id, error);
			return false;
		}
	}

	function scf_generate_mceinit(editor_id) {
		if (!editor_id) {
			return null;
		}

		var mceinit;

		try {
			// デフォルト設定
			var default_config = {
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

			if (typeof tinyMCEPreInit !== 'undefined' &&
				tinyMCEPreInit.mceInit &&
				typeof tinyMCEPreInit.mceInit.content !== 'undefined') {
				mceinit = $.extend(true, {}, tinyMCEPreInit.mceInit.content);
				mceinit.selector = '#' + editor_id;
			} else {
				mceinit = default_config;
			}

			return mceinit;
		} catch (error) {
			console.error('Smart Custom Fields: scf_generate_mceinit failed for ' + editor_id, error);
			return null;
		}
	}

	function scf_generate_qtinit(editor_id) {
		if (!editor_id) {
			return null;
		}

		var qtinit;

		try {
			// デフォルト設定
			var default_config = {
				id: editor_id,
				buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
			};

			if (typeof tinyMCEPreInit !== 'undefined' &&
				tinyMCEPreInit.qtInit &&
				typeof tinyMCEPreInit.qtInit.content !== 'undefined') {
				qtinit = $.extend(true, {}, tinyMCEPreInit.qtInit.content);
				qtinit.id = editor_id;
			} else {
				qtinit = default_config;
			}

			return qtinit;
		} catch (error) {
			console.error('Smart Custom Fields: scf_generate_qtinit failed for ' + editor_id, error);
			return null;
		}
	}
});
