<?php
/**
 * Smart_Custom_Fields_Revisions
 * Version    : 1.1.0
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : February 27, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Revisions {

	/**
	 * __construct
	 */
	public function __construct() {
		add_filter(
			'_wp_post_revision_field_' . SCF_Config::PREFIX . 'debug-preview',
			array( $this, '_wp_post_revision_field_debug_preview' ),
			10,
			3
		);
		add_filter(
			'wp_save_post_revision_check_for_changes',
			array( $this, 'wp_save_post_revision_check_for_changes' ),
			10,
			3
		);
		add_filter( '_wp_post_revision_fields', array( $this, '_wp_post_revision_fields' ) );
		add_filter( 'get_post_metadata'       , array( $this, 'get_post_metadata' ), 10, 4 );
		add_action( 'edit_form_after_title'   , array( $this, 'edit_form_after_title' ) );
		add_action( 'wp_restore_post_revision', array( $this, 'wp_restore_post_revision' ), 10, 2 );
		add_action( 'wp_insert_post'          , array( $this, 'wp_insert_post' ) );
	}

	/**
	 * リビジョンから復元するときに呼び出される
	 *
	 * @param int $post_id
	 * @param int $revision_id
	 */
	public function wp_restore_post_revision( $post_id, $revision_id ) {
		$post      = get_post( $post_id );
		$revision  = get_post( $revision_id );
		$post_type = get_post_type();

		$settings = SCF::get_settings( $post_type );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					delete_post_meta( $post->ID, $field_name );
					$value = SCF::get( $field_name, $revision->ID );
					if ( is_array( $value ) ) {
						foreach ( $value as $val ) {
							add_post_meta( $post->ID, $field_name, $val );
						}
					} else {
						add_post_meta( $post->ID, $field_name, $value );
					}
				}
			}
		}

		$repeat_multiple_data_name = SCF_Config::PREFIX . 'repeat-multiple-data';
		delete_post_meta( $post->ID, $repeat_multiple_data_name );
		$repeat_multiple_data = get_post_meta( $revision->ID, $repeat_multiple_data_name, true );
		add_post_meta( $post->ID, $repeat_multiple_data_name, $repeat_multiple_data );
	}

	/**
	 * リビジョンデータを保存
	 * *_post_meta はリビジョンIDのときに自動的に本物IDに変換して処理してしまうので、*_metadata を使うこと
	 *
	 * @param int $post_id
	 */
	public function wp_insert_post( $post_id ) {
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}
		if ( !wp_is_post_revision( $post_id ) ) {
			return;
		}
		$settings = SCF::get_settings( get_post_type() );
		if ( !$settings ) {
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
		$settings = SCF::get_settings( $post_type );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					delete_metadata( 'post', $post_id, $field_name );
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

		delete_metadata( 'post', $post_id, SCF_Config::PREFIX . 'repeat-multiple-data' );
		if ( $repeat_multiple_data ) {
			update_metadata( 'post', $post_id, SCF_Config::PREFIX . 'repeat-multiple-data', $repeat_multiple_data );
		}

		foreach ( $_POST[SCF_Config::NAME] as $name => $values ) {
			foreach ( $values as $value ) {
				if ( in_array( $name, $multiple_data_fields ) && $value === '' )
					continue;
				if ( !is_array( $value ) ) {
					add_metadata( 'post', $post_id, $name, $value );
				} else {
					foreach ( $value as $val ) {
						add_metadata( 'post', $post_id, $name, $val );
					}
				}
			}
		}
	}

	/**
	 * プレビューのときはプレビューのメタデータを返す
	 * 
	 * @param mixed $value
	 * @param int $post_id
	 * @param string $meta_key
	 * @param bool $single
	 * @return mixed $value
	 */
	public function get_post_metadata( $value, $post_id, $meta_key, $single ) {
		if ( $preview_id = $this->get_preview_id( $post_id ) ) {
			if ( $post_id !== $preview_id ) {
				$value = get_post_meta( $preview_id, $meta_key, $single );
			}
		}
		return $value;
	}

	/**
	 * プレビューの Post ID を返す
	 * 
	 * @param int $post_id
	 * @return int $preview_id
	 */
	protected function get_preview_id( $post_id ) {
		global $post;
		$preview_id = 0;
		if ( isset( $post->ID ) && intval( $post->ID ) === intval( $post_id ) && is_preview() && $preview = wp_get_post_autosave( $post->ID ) ) {
			$preview_id = $preview->ID;
		}
		return $preview_id;
	}

	/**
	 * リビジョン比較画面でメタデータを表示させるためにキーを追加する
	 * 
	 * @param array $fields
	 * @return array $fields
	 */
	public function _wp_post_revision_fields( $fields ){
		$fields[SCF_Config::PREFIX . 'debug-preview'] = esc_html__( 'Smart Custom Fields', 'smart-custom-fields' );
		return $fields;
	}

	/**
	 * プレビュー時にメタデータを保存するためにキーとなる項目を出力する
	 */
	public function edit_form_after_title() {
		printf(
			'<input type="hidden" name="%1$s" value="%1$s" />',
			SCF_Config::PREFIX . 'debug-preview'
		);
	}

	/**
	 * リビジョン比較画面にメタデータを表示
	 * 
	 * @param $value
	 * @param $column
	 * @param array $post
	 * @return string
	 */
	public function _wp_post_revision_field_debug_preview( $value, $column, $post ) {
		$output = '';
		$values = SCF::gets( $post->ID );
		foreach ( $values as $key => $value ) {
			$output .= '[' . $key . ']' . "\n";
			if ( is_array( $value ) ) {
				if ( isset( $value[0] ) && is_array( $value[0] ) ) {
					foreach ( $value as $sub_field_values ) {
						foreach ( $sub_field_values as $sub_field_key => $sub_field_value ) {
							$output .= $sub_field_key . " : ";
							if ( is_array( $sub_field_value ) ) {
								$output .= implode( ', ', $sub_field_value ) . "\n";
							} else {
								$output .= $sub_field_value . "\n";
							}
						}
					}
				} else {
					$output .= implode( ', ', $value ) . "\n";
				}
			} else {
				$output .= $value . "\n";
			}
		}
		return $output;
	}

	/**
	 * false ならリビジョンとして保存される
	 * 
	 * @param bool $check_for_changes
	 * @param WP_Post $last_revision 最新のリビジョン
	 * @param WP_Post $post 現在の投稿
	 * @return bool
	 */
	public function wp_save_post_revision_check_for_changes( $check_for_changes, $last_revision, $post ) {
		$post_meta = array();
		$p = get_post_custom( $post->ID );
		foreach ( $p as $key => $value ) {
			$v = SCF::get( $key );
			if ( !is_null( $v ) ) {
				$post_meta[$key][] = $v;
			}
		}

		if ( isset( $_POST[SCF_Config::NAME] ) ) {
			$serialized_post_meta = serialize( $post_meta );
			$serialized_send_data = $_POST[SCF_Config::NAME];
			if ( $serialized_post_meta != $serialized_send_data ) {
				return false;
			}
		}
		return true;
	}
}
