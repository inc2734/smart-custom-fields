<?php
/**
 * Smart_Custom_Fields_Controller_Editor
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Editor {

	/**
	 * post_custom 格納用。何度も関数呼び出ししなくて良いように保存
	 * @var array
	 */
	protected $post_custom = array();

	/**
	 * 各フォーム部品のオブジェクトを格納する配列
	 * @var array
	 */
	protected $fields = array();

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes'       , array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post'            , array( $this, 'save_post' ) );
	}

	/**
	 * 投稿画面用の css、js、翻訳ファイルのロード
	 * 
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		do_action( SCF_Config::PREFIX . 'before-editor-enqueue-scripts' );
		wp_enqueue_style(
			SCF_Config::PREFIX . 'editor',
			plugins_url( SCF_Config::NAME ) . '/css/editor.css'
		);
		wp_enqueue_media();
		wp_enqueue_script(
			SCF_Config::PREFIX . 'editor',
			plugins_url( SCF_Config::NAME ) . '/js/editor.js',
			array( 'jquery' ),
			null,
			true
		);
		wp_localize_script( SCF_Config::PREFIX . 'editor', 'smart_cf_uploader', array(
			'image_uploader_title' => esc_html__( 'Image setting', 'smart-custom-fields' ),
			'file_uploader_title'  => esc_html__( 'File setting', 'smart-custom-fields' ),
		) );
		do_action( SCF_Config::PREFIX . 'after-editor-enqueue-scripts' );
	}

	/**
	 * 投稿画面にカスタムフィールドを表示
	 *
	 * @param string $post_type
	 * @param WP_Post $post
	 */
	public function add_meta_boxes( $post_type, $post ) {
		$_post = $post;
		$settings = SCF::get_settings( $post_type );
		foreach ( $settings as $Setting ) {
			add_meta_box(
				SCF_Config::PREFIX . 'custom-field-' . $Setting->get_id(),
				$Setting->get_title(),
				array( $this, 'display_meta_box' ),
				$post_type,
				'normal',
				'default',
				$Setting->get_groups()
			);
		}
	}

	/**
	 * 投稿画面にカスタムフィールドを表示
	 * 
	 * @param object $post
	 * @param array $callback_args カスタムフィールドの設定情報
	 */
	public function display_meta_box( $post, $callback_args ) {
		$groups = $callback_args['args'];
		$tables = $this->get_tables( $post->ID, $groups );

		printf( '<div class="%s">', esc_attr( SCF_Config::PREFIX . 'meta-box' ) );
		$index = 0;
		foreach ( $tables as $group_key => $Group ) {
			$is_repeatable = $Group->is_repeatable();
			if ( $is_repeatable && $index === 0 ) {
				printf(
					'<div class="%s">',
					esc_attr( SCF_Config::PREFIX . 'meta-box-repeat-tables' )
				);
				$this->display_tr( $post->ID, $is_repeatable, $Group->get_fields() );
			}
			$this->display_tr( $post->ID, $is_repeatable, $Group->get_fields(), $index );

			// ループの場合は添字をカウントアップ
			// ループを抜けたらカウントをもとに戻す
			if ( $is_repeatable &&
				 isset( $tables[$group_key + 1 ] ) &&
				 $tables[$group_key + 1 ]->get_name() === $Group->get_name() ) {
				$index ++;
			} else {
				$index = 0;
			}
			if ( $is_repeatable && $index === 0 ) {
				printf( '</div>' );
			}
		}
		printf( '</div>' );
		wp_nonce_field( SCF_Config::NAME . '-fields', SCF_Config::PREFIX . 'fields-nonce' );
	}

	/**
	 * 投稿画面のカスタムフィールドからのメタデータを保存
	 * 
	 * @param int $post_id
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ){
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}

		check_admin_referer(
			SCF_Config::NAME . '-fields',
			SCF_Config::PREFIX . 'fields-nonce'
		);

		// 繰り返しフィールドのチェックボックスは、普通のチェックボックスと混ざって
		// 判別できなくなるのでわかるように保存しておく
		$repeat_multiple_data = array();

		// チェックボックスが未入力のときは "" がくるので、それは保存しないように判別
		$multiple_data_fields = array();

		$post_type = get_post_type();
		$settings  = SCF::get_settings( $post_type );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					delete_post_meta( $post_id, $field_name );
					if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
						$multiple_data_fields[] = $field_name;
					}

					if ( $Group->is_repeatable() && $Field->get_attribute( 'allow-multiple-data' ) ) {
						$repeat_multiple_data_fields = $_POST[SCF_Config::NAME][$field_name];
						foreach ( $repeat_multiple_data_fields as $values ) {
							if ( is_array( $values ) ) {
								$repeat_multiple_data[$field_name][] = count( $values );
							} else {
								$repeat_multiple_data[$field_name][] = 0;
							}
						}
					}
				}
			}
		}

		delete_post_meta( $post_id, SCF_Config::PREFIX . 'repeat-multiple-data' );
		if ( $repeat_multiple_data ) {
			update_post_meta( $post_id, SCF_Config::PREFIX . 'repeat-multiple-data', $repeat_multiple_data );
		}

		foreach ( $_POST[SCF_Config::NAME] as $name => $values ) {
			foreach ( $values as $value ) {
				if ( in_array( $name, $multiple_data_fields ) && $value === '' ) {
					continue;
				}
				if ( !is_array( $value ) ) {
					$this->add_post_meta( $post_id, $name, $value );
				} else {
					foreach ( $value as $val ) {
						$this->add_post_meta( $post_id, $name, $val );
					}
				}
			}
		}
	}

	/**
	 * メタデータを保存
	 * 
	 * @param int $post_id
	 * @param string $name
	 * @param mixed $value
	 */
	protected function add_post_meta( $post_id, $name, $value ) {
		do_action( SCF_Config::PREFIX . '-before-save-post', $post_id, $name, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-post', true, $post_id, $name, $value );
		if ( $is_valid ) {
			add_post_meta( $post_id, $name, $value );
		}
		do_action( SCF_Config::PREFIX . '-after-save-post', $post_id, $name, $value );
	}

	/**
	 * メタデータの取得
	 * 
	 * @param int $post_id
	 * @return array
	 */
	protected function get_post_custom( $post_id ) {
		$post_custom = $this->post_custom;
		if ( empty( $post_custom ) ) {
			$post_custom = get_post_custom( $post_id );
			if ( empty( $post_custom ) ) {
				return array();
			}
			$this->post_custom = $post_custom;
		}
		return $this->post_custom;
	}

	/**
	 * カスタムフィールドを出力するための配列を生成
	 * 
	 * @param array $groups カスタムフィールド設定ページで保存した設定
	 * @return array $tables カスタムフィールド表示用のテーブルを出力するための配列
	 */
	protected function get_tables( $post_id, $groups ) {
		$post_custom = $this->get_post_custom( $post_id );
		$repeat_multiple_data = SCF::get_repeat_multiple_data( $post_id );
		$tables = array();
		foreach ( $groups as $Group ) {
			// ループのときは、ループの分だけグループを追加する
			// ループだけどループがないとき（新規登録時とか）は1つだけ入れる
			if ( $Group->is_repeatable() === true ) {
				$loop_count = 1;
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					if ( isset( $post_custom[$field_name] ) && is_array( $post_custom[$field_name] ) ) {
						$post_meta       = $post_custom[$field_name];
						$post_meta_count = count( $post_meta );
						// 同名のカスタムフィールドが複数のとき（チェックボックス or ループ）
						if ( $post_meta_count > 1 ) {
							// チェックボックスの場合
							if ( is_array( $repeat_multiple_data ) && array_key_exists( $field_name, $repeat_multiple_data ) ) {
								$repeat_multiple_data_count = count( $repeat_multiple_data[$field_name] );
								if ( $loop_count < $repeat_multiple_data_count )
									$loop_count = $repeat_multiple_data_count;
							}
							// チェックボックス以外
							else {
								if ( $loop_count < $post_meta_count )
									$loop_count = $post_meta_count;
							}
						}
					}
				}
				if ( $loop_count >= 1 ) {
					for ( $i = $loop_count; $i > 0; $i -- ) {
						$tables[] = $Group;
					}
					continue;
				}
			}
			$tables[] = $Group;
		}
		return $tables;
	}

	/**
	 * 複数許可フィールドのメタデータを取得
	 * 
	 * @param int $post_id
	 * @param string $field_name
	 * @param int $index
	 * @return array or null
	 */
	protected function get_multiple_data_field_value( $post_id, $field_name, $index ) {
		$post_custom = $this->get_post_custom( $post_id );
		$repeat_multiple_data = SCF::get_repeat_multiple_data( $post_id );
		$value = null;
		if ( isset( $post_custom[$field_name] ) && is_array( $post_custom[$field_name] ) ) {
			$value = $post_custom[$field_name];
			// ループのとき
			if ( is_array( $repeat_multiple_data ) && array_key_exists( $field_name, $repeat_multiple_data ) ) {
				$now_num = 0;
				if ( isset( $repeat_multiple_data[$field_name][$index] ) ) {
					$now_num = $repeat_multiple_data[$field_name][$index];
				}

				// 自分（$index）より前の個数の合計が指す index が start
				$_temp = array_slice( $repeat_multiple_data[$field_name], 0, $index );
				$sum = array_sum( $_temp );
				$start = $sum;

				$value = null;
				if ( $now_num ) {
					$value = array_slice( $post_custom[$field_name], $start, $now_num );
				}
			}
		}
		return $value;
	}

	/**
	 * 非複数許可フィールドのメタデータを取得
	 * 
	 * @param int $post_id
	 * @param string $field_name
	 * @param int $index
	 * @return string or null
	 */
	protected function get_single_data_field_value( $post_id, $field_name, $index ) {
		$post_custom = $this->get_post_custom( $post_id );
		$value = null;
		if ( isset( $post_custom[$field_name][$index] ) ) {
			$value = $post_custom[$field_name][$index];
		}
		return $value;
	}

	/**
	 * カスタムフィールド表示 table で使用する各 tr を出力
	 * 
	 * @param int $post_id
	 * @param bool $is_repeat
	 * @param array $fields
	 * @param int, null $index
	 */
	protected function display_tr( $post_id, $is_repeat, $fields, $index = null ) {
		$btn_repeat = '';
		if ( $is_repeat ) {
			$btn_repeat  = sprintf(
				'<span class="%s"></span>',
				esc_attr( SCF_Config::PREFIX . 'icon-handle dashicons dashicons-menu' )
			);
			$btn_repeat .= '<span class="btn-add-repeat-group dashicons dashicons-plus-alt '.SCF_Config::PREFIX.'repeat-btn"></span>';
			$btn_repeat .= ' <span class="btn-remove-repeat-group dashicons dashicons-dismiss '.SCF_Config::PREFIX.'repeat-btn"></span>';
		}

		$style = '';
		if ( is_null( $index ) ) {
			$style = 'style="display: none;"';
		}

		printf(
			'<div class="%s" %s>%s<table>',
			esc_attr( SCF_Config::PREFIX . 'meta-box-table' ),
			$style,
			$btn_repeat
		);

		foreach ( $fields as $Field ) {
			$type         = $Field->get_attribute( 'type' );
			$display_name = $Field->get_attribute( 'display-name' );
			$default      = $Field->get( 'default' );
			$field_name   = $Field->get( 'name' );
			$field_label  = $Field->get( 'label' );
			if ( !$field_label ) {
				$field_label = $field_name;
			}

			// 複数値許可フィールドのとき
			$post_status = get_post_status( $post_id );
			if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
				$value = array();
				if ( !SCF::is_empty( $default ) && ( $post_status === 'auto-draft' || is_null( $index ) ) ) {
					$value = SCF::choices_eol_to_array( $default );
				}
				$_value = $this->get_multiple_data_field_value( $post_id, $field_name, $index );
			}
			// 複数不値許可フィールドのとき
			else {
				$value = '';
				if ( $post_status === 'auto-draft' || is_null( $index ) ) {
					if ( !SCF::is_empty( $default ) ) {
						$value = $default;
					}
				}
				$_value = $this->get_single_data_field_value( $post_id, $field_name, $index );
			}
			if ( !is_null( $_value ) ) {
				$value = $_value;
			}

			$notes = $Field->get( 'notes' );
			if ( !empty( $notes ) ) {
				$notes = sprintf(
					'<p class="description">%s</p>',
					esc_html( $notes )
				);
			}

			$form_field = $Field->get_field( $index, $value );
			printf(
				'<tr><th>%s</th><td>%s%s</td></tr>',
				esc_html( $field_label ),
				$form_field,
				$notes
			);
		}
		echo '</table></div>';
	}
}