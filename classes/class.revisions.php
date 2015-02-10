<?php
/**
 * Smart_Custom_Fields_Revisions
 * Version    : 1.0.2
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : February 10, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Revisions {

	/**
	 * __construct
	 */
	public function __construct() {
		add_filter( 'get_post_metadata', array( $this, 'get_post_metadata' ), 10, 4 );
		add_action( 'wp_restore_post_revision', array( $this, 'wp_restore_post_revision' ), 10, 2 );
		add_action( 'wp_insert_post', array( $this, 'wp_insert_post' ) );

		// Always auto save when click preview button.
		add_filter( '_wp_post_revision_fields', array( $this, '_wp_post_revision_fields' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );

		// Add custom fields preview in revision diff page.
		add_filter( '_wp_post_revision_field_' . SCF_Config::PREFIX . 'debug-preview', array( $this, '_wp_post_revision_field_debug_preview' ), 10, 3 );

		// Save revision when changing custom fields.
		add_filter( 'wp_save_post_revision_check_for_changes', array( $this, 'wp_save_post_revision_check_for_changes' ), 10, 3);
	}

	/**
	 * wp_restore_post_revision
	 * リビジョンから復元するときに呼び出される
	 * @param int $post_id
	 * @param int $revision_id
	 */
	public function wp_restore_post_revision( $post_id, $revision_id ) {
		$post      = get_post( $post_id );
		$revision  = get_post( $revision_id );
		$post_type = get_post_type();

		$settings = SCF::get_settings( $post_type );
		foreach ( $settings as $setting ) {
			foreach ( $setting as $group ) {
				foreach ( $group['fields'] as $field_name => $field ) {
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
	 * wp_insert_post
	 * @param int $post_id
	 */
	public function wp_insert_post( $post_id ) {
		if ( !isset( $_POST[SCF_Config::PREFIX . 'fields-nonce'] ) ) {
			return;
		}
		if ( !wp_verify_nonce( $_POST[SCF_Config::PREFIX . 'fields-nonce'], SCF_Config::NAME . '-fields' ) ) {
			return;
		}
		if ( !isset( $_POST[SCF_Config::NAME] ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			// 繰り返しフィールドのチェックボックスは、普通のチェックボックスと混ざって
			// 判別できなくなるのでわかるように保存しておく
			$repeat_multiple_data = array();

			$post_type = get_post_type();
			$settings = SCF::get_settings( $post_type );
			foreach ( $settings as $setting ) {
				foreach ( $setting as $group ) {
					$is_repeat = ( isset( $group['repeat'] ) && $group['repeat'] === true ) ? true : false;
					foreach ( $group['fields'] as $field_name => $field ) {
						delete_metadata( 'post', $post_id, $field_name );

						if ( $is_repeat && $field['allow-multiple-data'] ) {
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
	}

	/**
	 * get_post_metadata
	 * プレビューのときはプレビューのメタデータを返す
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
	 * get_preview_id
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
	 * _wp_post_revision_fields
	 * @param array $fields
	 * @return array $fields
	 */
	public function _wp_post_revision_fields( $fields ){
		$fields[SCF_Config::PREFIX . 'debug-preview'] = esc_html__( 'Smart Custom Fields', 'smart-custom-fields' );
		return $fields;
	}

	/**
	 * edit_form_after_title
	 */
	public function edit_form_after_title() {
		printf(
			'<input type="hidden" name="%1$s" value="%1$s" />',
			SCF_Config::PREFIX . 'debug-preview'
		);
	}

	/**
	 * _wp_post_revision_field_debug_preview
	 * @param $value
	 * @param $column
	 * @param array $post
	 * @return string
	 */
	public function _wp_post_revision_field_debug_preview( $value = '', $column = null, $post ) {
		if ( is_null( $column ) ) {
			$column = SCF_Config::PREFIX . 'debug-preview';
		}
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
	 * wp_save_post_revision_check_for_changes
	 * @return bool false ならリビジョンとして保存される。
	 */
	public function wp_save_post_revision_check_for_changes( $check_for_changes = true, $last_revision, $post ) {
		$post_meta = array();
		$p = get_post_custom( $post->ID );
		foreach ( $p as $key => $value ) {
			$v = SCF::get( $key );
			if ( !is_null( $v ) ) {
				$post_meta[$key][] = $v;
			}
		}

		if ( isset( $_POST[SCF_Config::NAME] ) && serialize( $post_meta ) != serialize( $_POST[SCF_Config::NAME] ) ) {
			return false;
		}
		return true;
	}
}
