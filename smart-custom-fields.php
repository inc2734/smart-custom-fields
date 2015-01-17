<?php
/**
 * Plugin name: Smart Custom Fields
 * Plugin URI: https://github.com/inc2734/smart-custom-fields/
 * Description: Smart Custom Fields is a simple plugin that management custom fields.
 * Version: 1.1.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created: October 9, 2014
 * Modified: January 18, 2015
 * Text Domain: smart-custom-fields
 * Domain Path: /languages/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields {

	/**
	 * post_custom 格納用
	 * 何度も関数呼び出ししなくて良いように保存
	 */
	protected $post_custom = array();

	/**
	 * repeat_multiple_data
	 * 何度も関数呼び出ししなくて良いように保存
	 */
	protected $repeat_multiple_data = array();

	/**
	 * fields
	 * 各フォーム部品のオブジェクトを格納する配列
	 */
	protected $fields = array();

	/**
	 * __construct
	 */
	public function __construct() {
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.config.php';
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * plugins_loaded
	 */
	public function plugins_loaded() {
		do_action( SCF_Config::PREFIX . 'load' );
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.field-base.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.revisions.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class.scf.php';
		new Smart_Custom_Fields_Settings();
		new Smart_Custom_Fields_Revisions();
		new SCF();

		foreach ( glob( plugin_dir_path( __FILE__ ) . 'classes/fields/*.php' ) as $form_item ) {
			include_once $form_item;
			$basename = basename( $form_item, '.php' );
			$classname = preg_replace( '/^class\.field\-(.+)$/', 'Smart_Custom_Fields_Field_$1', $basename );
			if ( class_exists( $classname ) ) {
				new $classname();
			}
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * uninstall
	 */
	public static function uninstall() {
		$cf_posts = get_posts( array(
			'post_type'      => SCF_Config::NAME,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );
		foreach ( $cf_posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
		delete_post_meta_by_key( SCF_Config::PREFIX . 'repeat-multiple-data' );
	}

	/**
	 * admin_enqueue_scripts
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			$post_type = get_post_type();
			$settings = SCF::get_settings( $post_type );

			if ( empty( $settings ) )
				return;

			wp_enqueue_style(
				SCF_Config::PREFIX . 'editor',
				plugin_dir_url( __FILE__ ) . 'css/editor.css'
			);
			wp_enqueue_media();
			wp_enqueue_script(
				SCF_Config::PREFIX . 'editor',
				plugin_dir_url( __FILE__ ) . 'js/editor.js',
				array( 'jquery' ),
				null,
				true
			);
			wp_localize_script( SCF_Config::PREFIX . 'editor', 'smart_cf_uploader', array(
				'image_uploader_title' => esc_html__( 'Image setting', 'smart-custom-fields' ),
				'file_uploader_title'  => esc_html__( 'File setting', 'smart-custom-fields' ),
			) );
		}
	}

	/**
	 * add_meta_boxes
	 * 投稿画面にカスタムフィールドを表示
	 * @param stirng $post_type
	 * @param object $post
	 */
	public function add_meta_boxes( $post_type, $post ) {
		$cf_posts = SCF::get_settings_posts( $post_type );
		foreach ( $cf_posts as $post ) {
			setup_postdata( $post );
			$settings = get_post_meta( $post->ID, SCF_Config::PREFIX . 'setting', true );
			if ( !$settings )
				continue;
			add_meta_box(
				SCF_Config::PREFIX . 'custom-field-' . $post->ID,
				$post->post_title,
				array( $this, 'display_meta_box' ),
				$post_type,
				'normal',
				'default',
				$settings
			);
			wp_reset_postdata();
		}
	}

	/**
	 * display_meta_box
	 * @param object $post
	 * @param array $setings カスタムフィールドの設定情報
	 */
	public function display_meta_box( $post, $settings ) {
		$_settings = SCF::get_settings( get_post_type() );
		$groups = $_settings[$settings['id']];
		$tables = $this->get_tables( $post->ID, $groups );

		printf( '<div class="%s">', esc_attr( SCF_Config::PREFIX . 'meta-box' ) );
		$index = 0;
		foreach ( $tables as $group_key => $group ) {
			$btn_repeat = '';
			$is_repeat  = ( isset( $group['repeat'] ) && $group['repeat'] === true ) ? true : false;
			if ( $is_repeat ) {
				if ( $index === 0 ) {
					printf(
						'<div class="%s">',
						esc_attr( SCF_Config::PREFIX . 'meta-box-repeat-tables' )
					);
					$this->display_tr( $post->ID, $is_repeat, $group['fields'] );
				}
			}
			
			$this->display_tr( $post->ID, $is_repeat, $group['fields'], $index );

			// ループの場合は添字をカウントアップ
			// ループを抜けたらカウントをもとに戻す
			if ( $is_repeat &&
				 isset( $tables[$group_key + 1 ]['group-name'] ) &&
				 $tables[$group_key + 1 ]['group-name'] === $group['group-name'] ) {
				$index ++;
			} else {
				$index = 0;
			}
			if ( $is_repeat && $index === 0 ) {
				echo '</div>';
			}
		}
		printf( '</div>' );
		wp_nonce_field( SCF_Config::NAME . '-fields', SCF_Config::PREFIX . 'fields-nonce' );
	}

	/**
	 * save_post
	 * @param int $post_id
	 */
	public function save_post( $post_id ) {
		if ( !isset( $_POST[SCF_Config::PREFIX . 'fields-nonce'] ) ) {
			return;
		}
		if ( !wp_verify_nonce( $_POST[SCF_Config::PREFIX . 'fields-nonce'], SCF_Config::NAME . '-fields' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ){
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}

		// 繰り返しフィールドのチェックボックスは、普通のチェックボックスと混ざって
		// 判別できなくなるのでわかるように保存しておく
		$repeat_multiple_data = array();

		// チェックボックスが未入力のときは "" がくるので、それは保存しないように判別
		$multiple_data_fields = array();

		$post_type = get_post_type();
		$settings = SCF::get_settings( $post_type );
		foreach ( $settings as $setting ) {
			foreach ( $setting as $group ) {
				$is_repeat = ( isset( $group['repeat'] ) && $group['repeat'] === true ) ? true : false;
				foreach ( $group['fields'] as $field ) {
					delete_post_meta( $post_id, $field['name'] );

					if ( $field['allow-multiple-data'] ) {
						$multiple_data_fields[] = $field['name'];
					}

					if ( $is_repeat && $field['allow-multiple-data'] ) {
						$repeat_multiple_data_fields = $_POST[SCF_Config::NAME][$field['name']];
						foreach ( $repeat_multiple_data_fields as $values ) {
							if ( is_array( $values ) ) {
								$repeat_multiple_data[$field['name']][] = count( $values );
							} else {
								$repeat_multiple_data[$field['name']][] = 0;
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
				if ( in_array( $name, $multiple_data_fields ) && $value === '' )
					continue;
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
	 * add_post_meta
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
	 * get_post_custom
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
	 * get_repeat_multiple_data
	 * @param int $post_id
	 * @return array $this->repeat_multiple_data
	 */
	protected function get_repeat_multiple_data( $post_id ) {
		$repeat_multiple_data = $this->repeat_multiple_data;
		if ( empty( $repeat_multiple_data ) ) {
			$repeat_multiple_data = get_post_meta( $post_id, SCF_Config::PREFIX . 'repeat-multiple-data', true );
			if ( empty( $repeat_multiple_data ) ) {
				return array();
			}
			if ( is_serialized( $repeat_multiple_data ) ) {
				$repeat_multiple_data = maybe_unserialize( $repeat_multiple_data );
			}
			$this->repeat_multiple_data = $repeat_multiple_data;
		}
		return $this->repeat_multiple_data;
	}

	/**
	 * get_tables
	 * カスタムフィールドを出力するための配列を生成
	 * @param array $groups カスタムフィールド設定ページで保存した設定
	 * @return array $tables カスタムフィールド表示用のテーブルを出力するための配列
	 */
	protected function get_tables( $post_id, $groups ) {
		$post_custom = $this->get_post_custom( $post_id );
		$repeat_multiple_data = $this->get_repeat_multiple_data( $post_id );
		$tables = array();
		foreach ( $groups as $group ) {
			// ループのときは、ループの分だけグループを追加する
			// ループだけどループがないとき（新規登録時とか）は1つだけ入れる
			if ( isset( $group['repeat'] ) && $group['repeat'] === true ) {
				$loop_count = 1;
				foreach ( $group['fields'] as $field ) {
					if ( isset( $post_custom[$field['name']] ) && is_array( $post_custom[$field['name']] ) ) {
						$post_meta       = $post_custom[$field['name']];
						$post_meta_count = count( $post_meta );
						// 同名のカスタムフィールドが複数のとき（チェックボックス or ループ）
						if ( $post_meta_count > 1 ) {
							// チェックボックスの場合
							if ( is_array( $repeat_multiple_data ) && array_key_exists( $field['name'], $repeat_multiple_data ) ) {
								$repeat_multiple_data_count = count( $repeat_multiple_data[$field['name']] );
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
						$tables[] = $group;
					}
					continue;
				}
			}
			$tables[] = $group;
		}
		return $tables;
	}

	/**
	 * get_multiple_data_field_value
	 * @param int $post_id
	 * @param string $field_name
	 * @param int $index
	 * @return array or null
	 */
	protected function get_multiple_data_field_value( $post_id, $field_name, $index ) {
		$post_custom = $this->get_post_custom( $post_id );
		$repeat_multiple_data = $this->get_repeat_multiple_data( $post_id );
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
	 * get_single_data_field_value
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
	 * display_tr
	 * @param int $post_id
	 * @param bool $is_repeat
	 * @param array $fields
	 * @param int, null $index
	 */
	protected function display_tr( $post_id, $is_repeat, $fields, $index = null ) {
		$btn_repeat = '';
		if ( $is_repeat ) {
			$btn_repeat  = sprintf( '<span class="%s"></span>', esc_attr( SCF_Config::PREFIX . 'icon-handle' ) );
			$btn_repeat .= '<span class="button btn-add-repeat-group">+</span>';
			$btn_repeat .= ' <span class="button btn-remove-repeat-group">-</span>';
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

		foreach ( $fields as $field ) {
			$field_label = $field['label'];
			if ( !$field_label ) {
				$field_label = $field['name'];
			}

			// 複数値許可フィールドのとき
			$post_status = get_post_status( $post_id );
			if ( $field['allow-multiple-data'] ) {
				$value = array();
				if ( !SCF::is_empty( $field['default'] ) && ( $post_status === 'auto-draft' || is_null( $index ) ) ) {
					$value = SCF::get_field_instance( $field['type'] )->get_choices( $field['default'] );
				}
				$_value = $this->get_multiple_data_field_value( $post_id, $field['name'], $index );
			}
			// 複数不値許可フィールドのとき
			else {
				$value = '';
				if ( $post_status === 'auto-draft' || is_null( $index ) ) {
					if ( !SCF::is_empty( $field['default'] ) ) {
						$value = $field['default'];
					}
				}
				$_value = $this->get_single_data_field_value( $post_id, $field['name'], $index );
			}
			if ( !is_null( $_value ) ) {
				$value = $_value;
			}

			$notes = '';
			if ( !empty( $field['notes'] ) ) {
				$notes = sprintf(
					'<p class="description">%s</p>',
					esc_html( $field['notes'] )
				);
			}

			$form_field = SCF::get_field_instance( $field['type'] )->get_field( $field, $index, $value );
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
new Smart_Custom_Fields();
