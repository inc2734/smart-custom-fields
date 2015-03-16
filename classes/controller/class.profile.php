<?php
/**
 * Smart_Custom_Fields_Controller_Profile
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : March 16, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Controller_Profile extends Smart_Custom_Fields_Controller_Editor {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update' ) );
	}

	/**
	 * user_profile
	 */
	public function user_profile( $user ) {
		$settings = SCF::get_settings( SCF_Config::PROFILE, null );
		foreach ( $settings as $Setting ) {
			printf( '<h3>%s</h3>', esc_html( $Setting->get_title() ) );
			$callback_args['args'] = $Setting->get_groups();
			$this->display_meta_box( $user, $callback_args );
		}
	}

	/**
	 * 投稿画面のカスタムフィールドからのメタデータを保存
	 * 
	 * @param int $user_id
	 */
	public function update( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) ) {
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

		$post_type = SCF_Config::PROFILE;
		$settings  = SCF::get_settings( $post_type, null );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					delete_user_meta( $user_id, $field_name );
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

		delete_user_meta( $user_id, SCF_Config::PREFIX . 'repeat-multiple-data' );
		if ( $repeat_multiple_data ) {
			update_user_meta( $user_id, SCF_Config::PREFIX . 'repeat-multiple-data', $repeat_multiple_data );
		}

		foreach ( $_POST[SCF_Config::NAME] as $name => $values ) {
			foreach ( $values as $value ) {
				if ( in_array( $name, $multiple_data_fields ) && $value === '' ) {
					continue;
				}
				if ( !is_array( $value ) ) {
					$this->add_user_meta( $user_id, $name, $value );
				} else {
					foreach ( $value as $val ) {
						$this->add_user_meta( $user_id, $name, $val );
					}
				}
			}
		}
	}

	/**
	 * メタデータを保存
	 * 
	 * @param int $user_id
	 * @param string $name
	 * @param mixed $value
	 */
	protected function add_user_meta( $user_id, $name, $value ) {
		do_action( SCF_Config::PREFIX . '-before-save-profile', $user_id, $name, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-profile', true, $user_id, $name, $value );
		if ( $is_valid ) {
			add_user_meta( $user_id, $name, $value );
		}
		do_action( SCF_Config::PREFIX . '-after-save-profile', $user_id, $name, $value );
	}

	/**
	 * メタデータの取得
	 * 
	 * @param int $user_id
	 * @return array
	 */
	protected function get_post_custom( $user_id ) {
		$post_custom = $this->post_custom;
		if ( empty( $post_custom ) ) {
			$post_custom = get_user_meta( $user_id );
			if ( empty( $post_custom ) ) {
				return array();
			}
			$this->post_custom = $post_custom;
		}
		return $this->post_custom;
	}

	/**
	 * 投稿ステータスを返す（ユーザーにステータスは無いので必ず 'auto-draft' を返すこと）
	 *
	 * @param int $user_id
	 * @return string 'auto-draft'
	 */
	protected function get_post_status( $user_id ) {
		return 'auto-draft';
	}
}