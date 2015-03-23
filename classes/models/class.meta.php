<?php
/**
 * Smart_Custom_Fields_Meta
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Created    : March 17, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Meta {

	/**
	 * 投稿のメタデータを扱うか、ユーザーのメタデータを扱うか
	 * @var string post or user
	 */
	protected $meta_type = 'post';

	/**
	 * 投稿IDもしくはユーザーID
	 * @var int
	 */
	protected $id;

	/**
	 * 投稿タイプもしくはロール
	 * @var string
	 */
	protected $type;

	/**
	 * @param WP_Post|WP_User $object
	 */
	public function __construct( $object ) {
		if ( !function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		if ( is_a( $object, 'WP_Post' ) ) {
			$this->id   = $object->ID;
			$this->type = $object->post_type;
			$this->meta_type = 'post';
		} elseif ( is_a( $object, 'WP_User' ) ) {
			$this->id   = $object->ID;
			$this->type = $object->roles[0];
			$this->meta_type = 'user';
		} elseif( empty( $object ) ) {
			$this->id   = null;
			$this->type = null;
			$this->meta_type = null;
		} else {
			throw new Exception( sprintf( 'Invalid $object type error. $object is "%s".', get_class( $object ) ) );
		}
	}

	/**
	 * メタタイプを取得
	 *
	 * @return string post or user
	 */
	public function get_meta_type() {
		return $this->meta_type;
	}

	/**
	 * 投稿IDもしくはユーザーIDを取得
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * 投稿タイプもしくはロールを取得
	 *
	 * @param bool $accept_revision 投稿タイプだった場合に、投稿タイプ revision を許可
	 * @return string
	 */
	public function get_type( $accept_revision = true ) {
		if ( $this->meta_type === 'post' && !$accept_revision ) {
			return $this->get_public_post_type( $this->id );
		}
		return $this->type;
	}

	/**
	 * Post ID がリビジョンのものでも良い感じに投稿タイプを取得
	 * 
	 * @param int $post_id
	 * @return string
	 */
	protected function get_public_post_type( $post_id ) {
		if ( $public_post_id = wp_is_post_revision( $post_id ) ) {
			$post = get_post( $public_post_id );
		} else {
			$post = get_post( $post_id );
		}
		if ( !empty( $post->post_type ) ) {
			return $post->post_type;
		}
		return $this->type;
	}

	/**
	 * メタデータを取得
	 *
	 * @param string $key メタキー
	 * @param bool $single false だと配列で取得、true だと文字列で取得
	 * @return mixed
	 */
	public function get( $key = '', $single = false ) {
		return get_metadata( $this->meta_type, $this->id, $key, $single );
	}

	/**
	 * メタデータを更新。そのメタデータが存在しない場合は追加。
	 *
	 * @param string $key メタキー
	 * @param mixed $value 保存する値
	 * @param mixed $prev_value 指定された場合、この値のものだけを上書き
	 * @return int|false Meta ID
	 */
	public function update( $key, $value, $prev_value = '' ) {
		$return = false;
		do_action( SCF_Config::PREFIX . '-before-save-' . $this->meta_type, $this->id, $key, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-' . $this->meta_type, $this->id, $key, $value );
		if ( $is_valid ) {
			$return = update_metadata( $this->meta_type, $this->id, $key, $value, $prev_value );
		}
		do_action( SCF_Config::PREFIX . '-after-save-' . $this->meta_type, $this->id, $key, $value );
		return $return;
	}

	/**
	 * メタデータを追加
	 *
	 * @param string $key メタキー
	 * @param mixed $value 保存する値
	 * @param bool $unique キーをユニークにするかどうか
	 * @return int|false Meta ID
	 */
	public function add( $key, $value, $unique = false ) {
		$return = false;
		do_action( SCF_Config::PREFIX . '-before-save-' . $this->meta_type, $this->id, $key, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-' . $this->meta_type, $this->id, $key, $value );
		if ( $is_valid ) {
			$return = add_metadata( $this->meta_type, $this->id, $key, $value, $unique );
		}
		do_action( SCF_Config::PREFIX . '-after-save-' . $this->meta_type, $this->id, $key, $value );
		return $return;
	}

	/**
	 * メタデータを削除
	 *
	 * @param string $key メタキー
	 * @param mixed $value 指定した場合、その値をもつメタデータのみ削除
	 * @return bool
	 */
	public function delete( $key, $value = '' ) {
		return delete_metadata( $this->meta_type, $this->id, $key, $value );
	}

	/**
	 * 送信されたデータをもとにメタデータを保存
	 *
	 * @param array $POST $_POST を渡すこと
	 */
	public function save( array $POST ) {
		// 繰り返しフィールドのチェックボックスは、普通のチェックボックスと混ざって
		// 判別できなくなるのでわかるように保存しておく
		$repeat_multiple_data = array();

		// チェックボックスが未入力のときは "" がくるので、それは保存しないように判別
		$multiple_data_fields = array();

		switch ( $this->meta_type ) {
			case 'post' :
				$object = get_post( $this->id );
				break;
			case 'user' :
				$object = get_userdata( $this->id );
				break;
			default :
				$object = null;
		}

		if ( is_null( $object ) ) {
			return;
		}

		$this->delete( SCF_Config::PREFIX . 'repeat-multiple-data' );

		if ( !isset( $POST[SCF_Config::NAME] ) ) {
			return;
		}
		
		$settings = SCF::get_settings( $object );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					$this->delete( $field_name );
					if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
						$multiple_data_fields[] = $field_name;
					}
					if ( $Group->is_repeatable() && $Field->get_attribute( 'allow-multiple-data' ) ) {
						$repeat_multiple_data_fields = $POST[SCF_Config::NAME][$field_name];
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

		if ( $repeat_multiple_data ) {
			$this->update( SCF_Config::PREFIX . 'repeat-multiple-data', $repeat_multiple_data );
		}

		foreach ( $POST[SCF_Config::NAME] as $name => $values ) {
			foreach ( $values as $value ) {
				if ( in_array( $name, $multiple_data_fields ) && $value === '' ) {
					continue;
				}
				if ( !is_array( $value ) ) {
					$this->add( $name, $value );
				} else {
					foreach ( $value as $val ) {
						$this->add( $name, $val );
					}
				}
			}
		}
	}

	/**
	 * 渡されたリビジョンからデータをリストア
	 *
	 * @param WP_Post $revision
	 */
	public function restore( $revision ) {
		switch ( $this->meta_type ) {
			case 'post' :
				$object = get_post( $this->id );
				break;
			default :
				$object = null;
		}

		if ( is_null( $object ) || !is_a( $revision, 'WP_Post' ) ) {
			return;
		}

		$settings = SCF::get_settings( $object );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					$field_name = $Field->get( 'name' );
					$this->delete( $field_name );
					$value = SCF::get( $field_name, $revision->ID );
					if ( is_array( $value ) ) {
						foreach ( $value as $val ) {
							if ( is_array( $val ) ) {
								foreach ( $val as $v ) {
									// ループ内複数値項目
									$this->add( $field_name, $v );
								}
							} else {
								// ループ内単一項目 or ループ外複数値項目
								$this->add( $field_name, $val );
							}
						}
					} else {
						// ループ外単一項目
						$this->add( $field_name, $value );
					}
				}
			}
		}

		$repeat_multiple_data = SCF::get_repeat_multiple_data( $revision );
		$repeat_multiple_data_name = SCF_Config::PREFIX . 'repeat-multiple-data';
		$this->delete( $repeat_multiple_data_name );
		$this->update( $repeat_multiple_data_name, $repeat_multiple_data );
	}
}
