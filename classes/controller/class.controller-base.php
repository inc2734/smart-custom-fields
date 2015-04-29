<?php
/**
 * Smart_Custom_Fields_Controller_Base
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : April 27, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Base {

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
	 * @param WP_Post|WP_User|object $object
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
	 * 送信されたデータを保存
	 *
	 * @param array $data
	 * @param WP_Post|WP_User|object $object
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
	 * カスタムフィールドを出力するための配列を生成
	 * 
	 * @param WP_Post|WP_User $object
	 * @param array $groups カスタムフィールド設定ページで保存した設定
	 * @return array $tables カスタムフィールド表示用のテーブルを出力するための配列
	 */
	protected function get_tables( $object, $groups ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();

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
					$meta = $Meta->get( $field_name );
					if ( is_array( $meta ) ) {
						$meta_count = count( $meta );
						// 同名のカスタムフィールドが複数のとき（チェックボックス or ループ）
						if ( $meta_count > 1 ) {
							// チェックボックスの場合
							if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
								if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[$field_name] ) ) {
									$repeat_multiple_data_count = count( $repeat_multiple_data[$field_name] );
									if ( $loop_count < $repeat_multiple_data_count ) {
										$loop_count = $repeat_multiple_data_count;
									}
								}
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
	 * @param Smart_Custom_Fields_Field_Base $Field
	 * @param int $index
	 * @return array or null
	 */
	public function get_multiple_data_field_value( $object, $Field, $index ) {
		$Meta       = new Smart_Custom_Fields_Meta( $object );
		$id         = $Meta->get_id();
		$field_name = $Field->get( 'name' );

		if ( is_null( $index ) ) {
			return SCF::get_default_value( $Field );
		}

		if ( $Meta->is_saved_by_key( $field_name ) || !$Meta->is_use_default_when_not_saved() ) {
			$value = $Meta->get( $field_name );
			if ( !isset( $value[$index] ) ) {
				return SCF::get_default_value( $Field );
			}
		} else {
			return SCF::get_default_value( $Field );
		}

		// ループのとき
		$repeat_multiple_data = SCF::get_repeat_multiple_data( $object );
		if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[$field_name] ) ) {
			$now_num = 0;
			if ( is_array( $repeat_multiple_data[$field_name] ) && isset( $repeat_multiple_data[$field_name][$index] ) ) {
				$now_num = $repeat_multiple_data[$field_name][$index];
			}

			// 自分（$index）より前の個数の合計が指す index が start
			$_temp = array_slice( $repeat_multiple_data[$field_name], 0, $index );
			$sum   = array_sum( $_temp );
			$start = $sum;

			if ( $now_num ) {
				$value = array_slice( $value, $start, $now_num );
			}
		}
		return $value;
	}

	/**
	 * 非複数許可フィールドのメタデータを取得
	 * 
	 * @param int $id 投稿ID or ユーザーID
	 * @param Smart_Custom_Fields_Field_Base $Field
	 * @param int $index
	 * @return string or null
	 */
	public function get_single_data_field_value( $object, $Field, $index ) {
		$Meta       = new Smart_Custom_Fields_Meta( $object );
		$id         = $Meta->get_id();
		$field_name = $Field->get( 'name' );

		if ( is_null( $index ) ) {
			return SCF::get_default_value( $Field, true );
		}

		if ( $Meta->is_saved_by_key( $field_name ) || !$Meta->is_use_default_when_not_saved() ) {
			$value = $Meta->get( $field_name );
			if ( isset( $value[$index] ) ) {
				return $value[$index];
			}
		}
		return SCF::get_default_value( $Field, true );
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
			$field_name   = $Field->get( 'name' );
			$field_label  = $Field->get( 'label' );
			if ( !$field_label ) {
				$field_label = $field_name;
			}

			// 複数値許可フィールドのとき
			if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
				$value = $this->get_multiple_data_field_value( $object, $Field, $index );
			}
			// 複数不値許可フィールドのとき
			else {
				$value = $this->get_single_data_field_value( $object, $Field, $index );
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
