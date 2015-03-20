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
	protected $type = 'post';

	/**
	 * @param string $type 投稿タイプもしくは smart-cf-profile
	 */
	public function __construct( $type ) {
		if ( !function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		if ( in_array( $type, get_post_types() ) ) {
			$this->type = 'post';
		} elseif ( in_array( $type, array_keys( get_editable_roles() ) ) ) {
			$this->type = 'user';
		} else {
			throw new Exception( sprintf( 'Invalid post type error. Type is "%s".', $type ) );
		}
	}

	/**
	 * タイプを取得
	 *
	 * @return string post or user
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * メタデータを取得
	 *
	 * @param int $id 投稿ID or ユーザーID
	 * @param string $key メタキー
	 * @param bool $single false だと配列で取得、true だと文字列で取得
	 * @return mixed
	 */
	public function get( $id, $key = '', $single = false ) {
		return get_metadata( $this->type, $id, $key, $single );
	}

	/**
	 * メタデータを更新。そのメタデータが存在しない場合は追加。
	 *
	 * @param int $id 投稿ID or ユーザーID
	 * @param string $key メタキー
	 * @param mixed $value 保存する値
	 * @param mixed $prev_value 指定された場合、この値のものだけを上書き
	 * @return int|false Meta ID
	 */
	public function update( $id, $key, $value, $prev_value = '' ) {
		$return = false;
		do_action( SCF_Config::PREFIX . '-before-save-' . $this->type, $id, $key, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-' . $this->type, $id, $key, $value );
		if ( $is_valid ) {
			$return = update_metadata( $this->type, $id, $key, $value, $prev_value );
		}
		do_action( SCF_Config::PREFIX . '-after-save-' . $this->type, $id, $key, $value );
		return $return;
	}

	/**
	 * メタデータを追加
	 *
	 * @param int $id 投稿ID or ユーザーID
	 * @param string $key メタキー
	 * @param mixed $value 保存する値
	 * @param bool $unique キーをユニークにするかどうか
	 * @return int|false Meta ID
	 */
	public function add( $id, $key, $value, $unique = false ) {
		$return = false;
		do_action( SCF_Config::PREFIX . '-before-save-' . $this->type, $id, $key, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-' . $this->type, $id, $key, $value );
		if ( $is_valid ) {
			$return = add_metadata( $this->type, $id, $key, $value, $unique );
		}
		do_action( SCF_Config::PREFIX . '-after-save-' . $this->type, $id, $key, $value );
		return $return;
	}

	/**
	 * メタデータを削除
	 *
	 * @param int $id 投稿ID or ユーザーID
	 * @param string $key メタキー
	 * @param mixed $value 指定した場合、その値をもつメタデータのみ削除
	 * @return bool
	 */
	public function delete( $id, $key, $value = '' ) {
		return delete_metadata( $this->type, $id, $key, $value );
	}
}
