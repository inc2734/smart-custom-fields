<?php
/**
 * Smart_Custom_Fields_Controller_Editor
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : March 16, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Editor {

	/**
	 * meta_data 格納用。何度も関数呼び出ししなくて良いように保存
	 * @var array
	 */
	protected $meta_data = array();

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
		$settings = SCF::get_settings( $post );
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
	 * @param WP_Post|WP_User $object
	 * @param array $callback_args カスタムフィールドの設定情報
	 */
	public function display_meta_box( $object, $callback_args ) {
		$groups = $callback_args['args'];
		$tables = $this->get_tables( $object, $groups );

		printf( '<div class="%s">', esc_attr( SCF_Config::PREFIX . 'meta-box' ) );
		$index = 0;
		foreach ( $tables as $group_key => $Group ) {
			$is_repeatable = $Group->is_repeatable();
			if ( $is_repeatable && $index === 0 ) {
				printf(
					'<div class="%s">',
					esc_attr( SCF_Config::PREFIX . 'meta-box-repeat-tables' )
				);
				$this->display_tr( $object, $is_repeatable, $Group->get_fields() );
			}
			$this->display_tr( $object, $is_repeatable, $Group->get_fields(), $index );

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

		$this->save( $_POST, get_post( $post_id ) );
	}

	/**
	 * 送信されたデータを保存
	 *
	 * @param array $data
	 * @param WP_Post|WP_User $object
	 */
	protected function save( $data, $object ) {
		check_admin_referer(
			SCF_Config::NAME . '-fields',
			SCF_Config::PREFIX . 'fields-nonce'
		);

		$Meta = new Smart_Custom_Fields_Meta( $object );
		$Meta->save( $_POST );
	}

	/**
	 * メタデータの取得
	 * 
	 * @param int $id 投稿ID or ユーザーID
	 * @return array
	 */
	protected function get_all_meta( $id ) {
		$meta_data = $this->meta_data;
		if ( empty( $meta_data ) ) {
			$meta_data = get_post_meta( $id );
			if ( empty( $meta_data ) ) {
				return array();
			}
			$this->meta_data = $meta_data;
		}
		return $this->meta_data;
	}

	/**
	 * カスタムフィールドを出力するための配列を生成
	 * 
	 * @param WP_Post|WP_User $object
	 * @param array $groups カスタムフィールド設定ページで保存した設定
	 * @return array $tables カスタムフィールド表示用のテーブルを出力するための配列
	 */
	protected function get_tables( $object, $groups ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();

		$meta_data = $this->get_all_meta( $id );
		$repeat_multiple_data = SCF::get_repeat_multiple_data( $object );
		$tables = array();
		foreach ( $groups as $Group ) {
			// ループのときは、ループの分だけグループを追加する
			// ループだけどループがないとき（新規登録時とか）は1つだけ入れる
			if ( $Group->is_repeatable() === true ) {
				$loop_count = 1;
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					if ( isset( $meta_data[$field_name] ) && is_array( $meta_data[$field_name] ) ) {
						$meta       = $meta_data[$field_name];
						$meta_count = count( $meta );
						// 同名のカスタムフィールドが複数のとき（チェックボックス or ループ）
						if ( $meta_count > 1 ) {
							// チェックボックスの場合
							if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[$field_name] ) ) {
								$repeat_multiple_data_count = count( $repeat_multiple_data[$field_name] );
								if ( $loop_count < $repeat_multiple_data_count )
									$loop_count = $repeat_multiple_data_count;
							}
							// チェックボックス以外
							else {
								if ( $loop_count < $meta_count ) {
									$loop_count = $meta_count;
								}
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
	 * @param WP_Post|WP_Post $object
	 * @param string $field_name
	 * @param int $index
	 * @return array or null
	 */
	protected function get_multiple_data_field_value( $object, $field_name, $index ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();

		$meta_data = $this->get_all_meta( $id );
		$repeat_multiple_data = SCF::get_repeat_multiple_data( $object );
		$value = null;
		if ( isset( $meta_data[$field_name] ) && is_array( $meta_data[$field_name] ) ) {
			$value = $meta_data[$field_name];
			// ループのとき
			if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[$field_name] ) ) {
				$now_num = 0;
				if ( is_array( $repeat_multiple_data[$field_name] ) && isset( $repeat_multiple_data[$field_name][$index] ) ) {
					$now_num = $repeat_multiple_data[$field_name][$index];
				}

				// 自分（$index）より前の個数の合計が指す index が start
				$_temp = array_slice( $repeat_multiple_data[$field_name], 0, $index );
				$sum   = array_sum( $_temp );
				$start = $sum;

				$value = null;
				if ( $now_num ) {
					$value = array_slice( $meta_data[$field_name], $start, $now_num );
				}
			}
		}
		return $value;
	}

	/**
	 * 非複数許可フィールドのメタデータを取得
	 * 
	 * @param int $id 投稿ID or ユーザーID
	 * @param string $field_name
	 * @param int $index
	 * @return string or null
	 */
	protected function get_single_data_field_value( $id, $field_name, $index ) {
		$meta_data = $this->get_all_meta( $id );
		$value = null;
		if ( isset( $meta_data[$field_name][$index] ) ) {
			$value = $meta_data[$field_name][$index];
		}
		return $value;
	}

	/**
	 * カスタムフィールド表示 table で使用する各 tr を出力
	 * 
	 * @param WP_Post|WP_User $object
	 * @param bool $is_repeat
	 * @param array $fields
	 * @param int, null $index
	 */
	protected function display_tr( $object, $is_repeat, $fields, $index = null ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();

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
			$display_name = $Field->get_attribute( 'display-name' );
			$default      = $Field->get( 'default' );
			$field_name   = $Field->get( 'name' );
			$field_label  = $Field->get( 'label' );
			if ( !$field_label ) {
				$field_label = $field_name;
			}

			// 複数値許可フィールドのとき
			$post_status = $this->get_post_status( $id );
			if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
				$value = array();
				if ( !SCF::is_empty( $default ) && ( $post_status === 'auto-draft' || is_null( $index ) ) ) {
					$value = SCF::choices_eol_to_array( $default );
				}
				$_value = $this->get_multiple_data_field_value( $object, $field_name, $index );
			}
			// 複数不値許可フィールドのとき
			else {
				$value = '';
				if ( $post_status === 'auto-draft' || is_null( $index ) ) {
					if ( !SCF::is_empty( $default ) ) {
						$value = $default;
					}
				}
				$_value = $this->get_single_data_field_value( $id, $field_name, $index );
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

	/**
	 * 投稿ステータスを返す
	 *
	 * @param int $post_id
	 * @return string
	 */
	protected function get_post_status( $post_id ) {
		return get_post_status( $post_id );
	}
}