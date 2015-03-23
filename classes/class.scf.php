<?php
/**
 * SCF
 * Version    : 1.1.3
 * Author     : Takashi Kitajima
 * Created    : September 23, 2014
 * Modified   : March 16, 2015
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class SCF {

	/**
	 * Smart Custom Fields に登録されているフォームフィールド（field）のインスタンスの配列
	 * @var array
	 */
	protected static $fields = array();

	/**
	 * データ取得処理は重いので、一度取得したデータは cache に保存する。
	 * キーに post_id を設定すること。
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * データ取得処理は重いので、一度取得した設定データは settings_posts_cache に保存する。
	 * キーに post_type を設定すること。
	 * @var array
	 */
	protected static $settings_posts_cache = array();

	/**
	 * データ取得処理は重いので、一度取得した設定データは cache に保存する。
	 * キーに post_type を設定すること。
	 * @var array
	 */
	public static $settings_cache = array();

	/**
	 * データ取得処理は重いので、一度取得した設定データは cache に保存する。
	 * キーに post_id を設定すること。
	 * @var array
	 */
	protected static $repeat_multiple_data_cache = array();

	/**
	 * 全てのキャッシュをクリア
	 */
	public static function clear_all_cache() {
		self::clear_cache();
		self::clear_settings_posts_cache();
		self::clear_settings_cache();
		self::clear_repeat_multiple_data_cache();
	}

	/**
	 * その投稿の全てのメタデータを良い感じに取得
	 * 
	 * @param int $post_id
	 * @return array
	 */
	public static function gets( $post_id = null ) {
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$post_id = self::get_real_post_id( $post_id );

		if ( empty( $post_id ) ) {
			return null;
		}

		// 設定画面で未設定のメタデータは投稿が保持していても出力しないようにしないといけないので
		// 設定データを取得して出力して良いか判別する
		return self::get_all_meta( get_post( $post_id ) );
	}

	/**
	 * その投稿の任意のメタデータを良い感じに取得
	 * 
	 * @param string $name グループ名もしくはフィールド名
	 * @param int $post_id
	 * @return mixed
	 */
	public static function get( $name, $post_id = null ) {
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$post_id = self::get_real_post_id( $post_id );

		if ( empty( $post_id ) ) {
			return;
		}

		// 設定画面で未設定のメタデータは投稿が保持していても出力しないようにしないといけないので
		// 設定データを取得して出力して良いか判別する
		return self::get_meta( get_post( $post_id ), $name );
	}

	/**
	 * そのユーザーの任意のメタデータを良い感じに取得
	 * 
	 * @param int $user_id
	 * @param string $name グループ名もしくはフィールド名
	 * @return mixed
	 */
	public static function get_user_meta( $user_id, $name = null ) {
		if ( empty( $user_id ) ) {
			return;
		}

		// $name が null のときは全てのメタデータを返す
		if ( $name === null ) {
			return self::get_all_meta( get_userdata( $user_id ) );
		}

		// 設定画面で未設定のメタデータはユーザーが保持していても出力しないようにしないといけないので
		// 設定データを取得して出力して良いか判別する
		return self::get_meta( get_userdata( $user_id ), $name );
	}

	/**
	 * 任意のメタデータを良い感じに取得
	 * 
	 * @param WP_Post|WP_User $object
	 * @param string $name グループ名もしくはフィールド名
	 * @return mixed
	 */
	protected static function get_meta( $object, $name ) {
		if ( self::get_cache( $object, $name ) ) {
			self::debug_cache_message( "use get cache. [name: {$name}]" );
			return self::get_cache( $object, $name );
		} else {
			self::debug_cache_message( "dont use get cache... [name: {$name}]" );
		}

		$settings = self::get_settings( $object );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				// グループ名と一致する場合はそのグループ内のフィールドを配列で返す
				$is_repeatable = $Group->is_repeatable();
				$group_name    = $Group->get_name();
				if ( $is_repeatable && $group_name && $group_name === $name ) {
					$values_by_group = self::get_values_by_group( $object, $Group );
					self::save_cache( $object, $group_name, $values_by_group );
					return $values_by_group;
				}
				// グループ名と一致しない場合は一致するフィールドを返す
				else {
					$fields = $Group->get_fields();
					foreach ( $fields as $Field ) {
						$field_name = $Field->get( 'name' );
						if ( $field_name === $name ) {
							$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
							self::save_cache( $object, $Field->get( 'name' ), $value_by_field );
							return $value_by_field;
						}
					}
				}
			}
		}
	}

	/**
	 * 全てのメタデータを良い感じに取得
	 * 
	 * @param WP_Post|WP_User $object
	 * @return mixed
	 */
	protected static function get_all_meta( $object ) {
		$settings  = self::get_settings( $object );
		$post_meta = array();
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$is_repeatable = $Group->is_repeatable();
				$group_name    = $Group->get_name();
				if ( $is_repeatable && $group_name ) {
					$values_by_group = self::get_values_by_group( $object, $Group );
					self::save_cache( $object, $group_name, $values_by_group );
					$post_meta[$group_name] = $values_by_group;
				}
				else {
					$fields = $Group->get_fields();
					foreach ( $fields as $Field ) {
						$field_name = $Field->get( 'name' );
						$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
						self::save_cache( $object, $Field->get( 'name' ), $value_by_field );
						$post_meta[$field_name] = $value_by_field;
					}
				}
			}
		}
		return $post_meta;
	}

	/**
	 * プレビューのときはそのプレビューの Post ID を返す
	 *
	 * @param int $post_id
	 * @return int
	 */
	protected static function get_real_post_id( $post_id ) {
		if ( is_preview() ) {
			$preview_post = wp_get_post_autosave( $post_id );
			if ( isset( $preview_post->ID ) ) {
				$post_id = $preview_post->ID;
			}
		}
		return $post_id;
	}

	/**
	 * キャシュに保存
	 * 
	 * @param WP_Post|WP_User $object
	 * @param string $name
	 * @param mixed $data
	 */
	protected static function save_cache( $object, $name, $data ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();
		$type = $Meta->get_type();
		if ( !empty( $id ) && !empty( $type ) ) {
			self::$cache[$type . '_' . $id][$name] = $data;
		}
	}

	/**
	 * キャッシュを取得
	 * 
	 * @param WP_Post|WP_User $object
	 * @param string $name
	 * @return mixed
	 */
	protected static function get_cache( $object, $name = null ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();
		$type = $Meta->get_type();
		if ( !empty( $id ) && !empty( $type ) ) {
			if ( is_null( $name ) ) {
				if ( isset( self::$cache[$type . '_' . $id] ) ) {
					return self::$cache[$type . '_' . $id];
				}
			} else {
				if ( isset( self::$cache[$type . '_' . $id][$name] ) ) {
					return self::$cache[$type . '_' . $id][$name];
				}
			}
		}
	}

	/**
	 * キャッシュをクリア
	 */
	public static function clear_cache() {
		self::$cache = array();
	}

	/**
	 * そのグループのメタデータを取得。グループの場合は必ず繰り返しになっている点に注意
	 * 
	 * @param WP_Post|WP_User $object
	 * @param Smart_Custom_Fields_Group $Group
	 * @return mixed
	 */
	protected static function get_values_by_group( $object, $Group ) {
		$is_repeatable = $Group->is_repeatable();
		$meta   = array();
		$fields = $Group->get_fields();
		$value_by_fields = array();
		foreach ( $fields as $Field ) {
			if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
				$meta[0][$Field->get( 'name' )] = array();
			} else {
				$meta[0][$Field->get( 'name' )] = '';
			}
		}
		$default_meta = $meta[0];
		foreach ( $fields as $Field ) {
			$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
			foreach ( $value_by_field as $i => $value ) {
				$meta[$i][$Field->get( 'name' )] = $value;
			}
		}
		foreach ( $meta as $i => $value ) {
			$meta[$i] = array_merge( $default_meta, $value );
		}
		return $meta;
	}

	/**
	 * そのフィールドのメタデータを取得
	 * 
	 * @param WP_Post|WP_User $object
	 * @param array $field
	 * @param bool $is_repeatable このフィールドが所属するグループが repeat かどうか
	 * @return mixed $post_meta
	 */
	protected static function get_value_by_field( $object, $Field, $is_repeatable ) {
		$field_name = $Field->get( 'name' );
		if ( !$field_name ) {
			return;
		}

		$Meta = new Smart_Custom_Fields_Meta( $object );

		// ループ内の複数値項目の場合
		$field_type = $Field->get_attribute( 'type' );
		$repeat_multiple_data = self::get_repeat_multiple_data( $object );
		if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[$field_name] ) ) {
			$_meta = $Meta->get( $field_name );
			$start = 0;
			foreach ( $repeat_multiple_data[$field_name] as $repeat_multiple_key => $repeat_multiple_value ) {
				if ( $repeat_multiple_value === 0 ) {
					$value = array();
				} else {
					$value  = array_slice( $_meta, $start, $repeat_multiple_value );
					$start += $repeat_multiple_value;
				}
				$value = apply_filters( SCF_Config::PREFIX . 'validate-get-value', $value, $field_type );
				$meta[$repeat_multiple_key] = $value;
			}
		}
		// それ以外
		else {
			if ( $Field->get_attribute( 'allow-multiple-data' ) || $is_repeatable ) {
				$meta = $Meta->get( $field_name );
			} else {
				$meta = $Meta->get( $field_name, true );
			}
			$meta = apply_filters( SCF_Config::PREFIX . 'validate-get-value', $meta, $field_type );
		}
		return $meta;
	}

	/**
	 * その投稿タイプ or ロールで有効になっている SCF をキャッシュに保存
	 *
	 * @param WP_Post|WP_User $object
	 * @param array $settings_posts
	 */
	protected static function save_settings_posts_cache( $object, $settings_posts ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type( false );
		self::$settings_posts_cache[$type] = $settings_posts;
	}

	/**
	 * その投稿タイプで有効になっている SCF のキャッシュを取得
	 *
	 * @param WP_Post|WP_User $object
	 * @return array|null
	 */
	public static function get_settings_posts_cache( $object ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type( false );
		if ( isset( self::$settings_posts_cache[$type] ) ) {
			return self::$settings_posts_cache[$type];
		}
	}

	/**
	 * SCF のキャッシュをクリア
	 */
	public static function clear_settings_posts_cache() {
		self::$settings_posts_cache = array();
	}

	/**
	 * その投稿タイプで有効になっている SCF を取得
	 * 
	 * @param WP_Post|WP_User $object
	 * @return array $settings
	 */
	public static function get_settings_posts( $object ) {
		$settings_posts = array();
		if ( self::get_settings_posts_cache( $object ) !== null ) {
			self::debug_cache_message( "use settings posts cache." );
			return self::get_settings_posts_cache( $object );
		} else {
			self::debug_cache_message( "dont use settings posts cache..." );
		}

		$Meta = new Smart_Custom_Fields_Meta( $object );
		$type = $Meta->get_type( false );

		switch ( $Meta->get_meta_type() ) {
			case 'post' :
				$key = SCF_Config::PREFIX . 'condition';
				break;
			case 'user' :
				$key = SCF_Config::PREFIX . 'roles';
				break;
			default :
				$key = '';
		}

		if ( !empty( $key ) && !empty( $type ) ) {
			$settings_posts = get_posts( array(
				'post_type'      => SCF_Config::NAME,
				'posts_per_page' => -1,
				'order'          => 'ASC',
				'order_by'       => 'menu_order',
				'meta_query'     => array(
					array(
						'key'     => $key,
						'compare' => 'LIKE',
						'value'   => $type,
					),
				),
			) );
		}
		self::save_settings_posts_cache( $object, $settings_posts );
		return $settings_posts;
	}

	/**
	 * Setting オブジェクトをキャッシュに保存
	 *
	 * @param int $settings_post_id
	 * @param WP_post|WP_User $object
	 * @param Smart_Custom_Fields_Setting $Setting
	 */
	protected static function save_settings_cache( $settings_post_id, $Setting, $object = null ) {
		if ( !is_null( $object ) ) {
			$Meta      = new Smart_Custom_Fields_Meta( $object );
			$id        = $Meta->get_id();
			$meta_type = $Meta->get_meta_type();
		}
		if ( !empty( $meta_type ) && !empty( $id ) ) {
			self::$settings_cache[$settings_post_id][$meta_type . '_' . $id] = $Setting;
		} else {
			self::$settings_cache[$settings_post_id][0] = $Setting;
		}
	}

	/**
	 * Setting オブジェクトキャッシュを取得
	 * その SCF が存在しないとき ... null
	 * その SCF が存在する
	 *     指定した $meta_type + $id のものが無い
	 *         全般のものがある ... Smart_Custom_Fields_Setting
	 *         全般のものが無い ... false
	 *     指定した $meta_type + $id のものがあるとき ... Smart_Custom_Fields_Setting
	 *
	 * @param int $settings_post_id
	 * @param WP_post|WP_User $object
	 * @return Smart_Custom_Fields_Setting|false|null
	 */
	public static function get_settings_cache( $settings_post_id, $object = null ) {
		if ( !is_null( $object ) ) {
			$Meta      = new Smart_Custom_Fields_Meta( $object );
			$id        = $Meta->get_id();
			$meta_type = $Meta->get_meta_type();
		}

		if ( isset( self::$settings_cache[$settings_post_id] ) ) {
			$settings_cache = self::$settings_cache[$settings_post_id];
			if ( !empty( $id ) && !empty( $meta_type ) && isset( $settings_cache[$meta_type . '_' . $id] ) ) {
				return $settings_cache[$meta_type . '_' . $id];
			}
			if ( isset( $settings_cache[0] ) ) {
				return $settings_cache[0];
			}
			return false;
		}
	}

	/**
	 * Setting オブジェクトキャッシュをクリア
	 */
	public static function clear_settings_cache() {
		self::$settings_cache = array();
	}

	/**
	 * Setting オブジェクトの配列を取得
	 *
	 * @param WP_Post|WP_User $object
	 * @return array $settings
	 */
	public static function get_settings( $object ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();
		$type = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();

		$settings = array();
		if ( !empty( $type ) ) {
			$settings_posts = self::get_settings_posts( $object );
			if ( $meta_type === 'post' ) {
				$settings = self::get_settings_for_post( $object, $settings_posts );
			}
			elseif ( $meta_type === 'user' ) {
				$settings = self::get_settings_for_profile( $object, $settings_posts );
			}
		}
		$settings = apply_filters( SCF_Config::PREFIX . 'register-fields', $settings, $type, $id, $meta_type );
		return $settings;
	}

	/**
	 * Setting オブジェクトの配列を取得（投稿用）
	 *
	 * @param WP_Post $object
	 * @param array $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_post( $object, $settings_posts ) {
		$settings = array();
		foreach ( $settings_posts as $settings_post ) {
			if ( self::get_settings_cache( $settings_post->ID ) !== null ) {
				self::debug_cache_message( "use settings cache. [id: {$settings_post->ID}]" );
				$Setting = self::get_settings_cache( $settings_post->ID, $object );
				if ( $Setting ) {
					$settings[$settings_post->ID] = $Setting;
				}
				continue;
			}
			self::debug_cache_message( "dont use settings cache... [SCF ID: {$settings_post->ID}] [post_type: {$object->post_type}] [Post ID: {$object->ID}]" );
			$condition_post_ids_raw = get_post_meta(
				$settings_post->ID,
				SCF_Config::PREFIX . 'condition-post-ids',
				true
			);
			if ( $condition_post_ids_raw ) {
				$condition_post_ids_raw = explode( ',', $condition_post_ids_raw );
				foreach ( $condition_post_ids_raw as $condition_post_id ) {
					$condition_post_id = trim( $condition_post_id );
					$Setting = SCF::add_setting( $settings_post->ID, $settings_post->post_title );
					if ( $object->ID == $condition_post_id ) {
						$settings[$settings_post->ID] = $Setting;
					}
					$Post = get_post( $condition_post_id );
					if ( empty( $Post ) ) {
						$Post = new stdClass();
						$Post->ID = $condition_post_id;
						$Post = new WP_Post( $Post );
					}
					self::save_settings_cache( $settings_post->ID, $Setting, $Post );
				}
			} else {
				$Setting = SCF::add_setting( $settings_post->ID, $settings_post->post_title );
				$settings[$settings_post->ID] = $Setting;
				self::save_settings_cache( $settings_post->ID, $Setting );
			}
		}
		return $settings;
	}

	/**
	 * Setting オブジェクトの配列を取得（プロフィール用）
	 *
	 * @param WP_User $object
	 * @param array $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_profile( $object, $settings_posts ) {
		$settings = array();
		foreach ( $settings_posts as $settings_post ) {
			if ( self::get_settings_cache( $settings_post->ID ) !== null ) {
				self::debug_cache_message( "use settings cache. [id: {$settings_post->ID}]" );
				$settings[] = self::get_settings_cache( $settings_post->ID );
				continue;
			}
			self::debug_cache_message( "dont use settings cache... [id: {$settings_post->ID}]" );
			$Setting    = SCF::add_setting( $settings_post->ID, $settings_post->post_title );
			$settings[] = $Setting;
			self::save_settings_cache( $settings_post->ID, $Setting );
		}
		return $settings;
	}

	/**
	 * 繰り返しに設定された複数許可フィールドデータの区切り識別用データをキャッシュに保存
	 *
	 * @param WP_Post|WP_User $object
	 * @param mixed $repeat_multiple_data
	 */
	protected static function save_repeat_multiple_data_cache( $object, $repeat_multiple_data ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();
		$type = $Meta->get_type();
		if ( !empty( $id ) && !empty( $type ) ) {
			self::$repeat_multiple_data_cache[$type . '_' . $id] = $repeat_multiple_data;
		}
	}

	/**
	 * 繰り返しに設定された複数許可フィールドデータの区切り識別用データをキャッシュから取得
	 *
	 * @param WP_Post|WP_User $object
	 * @return mixed
	 */
	protected static function get_repeat_multiple_data_cache( $object ) {
		$Meta = new Smart_Custom_Fields_Meta( $object );
		$id   = $Meta->get_id();
		$type = $Meta->get_type();
		if ( !empty( $id ) && !empty( $type ) ) {
			if ( isset( self::$repeat_multiple_data_cache[$type . '_' . $id] ) ) {
				return self::$repeat_multiple_data_cache[$type . '_' . $id];
			}
		}
	}

	/**
	 * 繰り返しに設定された複数許可フィールドデータの区切り識別用データのキャッシュをクリア
	 */
	public static function clear_repeat_multiple_data_cache() {
		self::$repeat_multiple_data_cache = array();
	}

	/**
	 * 繰り返しに設定された複数許可フィールドデータの区切り識別用データを取得
	 * 
	 * @param WP_Post|WP_User $object
	 * @return array
	 */
	public static function get_repeat_multiple_data( $object ) {
		$repeat_multiple_data = array();
		if ( self::get_repeat_multiple_data_cache( $object ) ) {
			return self::get_repeat_multiple_data_cache( $object );
		}

		$Meta = new Smart_Custom_Fields_Meta( $object );
		$_repeat_multiple_data = $Meta->get( SCF_Config::PREFIX . 'repeat-multiple-data', true );
		if ( !empty( $_repeat_multiple_data ) ) {
			$repeat_multiple_data = $_repeat_multiple_data;
		}

		self::save_repeat_multiple_data_cache( $object, $repeat_multiple_data );
		return $repeat_multiple_data;
	}

	/**
	 * null もしくは空値の場合は true
	 * 
	 * @param mixed $value
	 * @return bool
	 */
	public static function is_empty( &$value ) {
		if ( isset( $value ) ) {
			if ( is_null( $value ) || $value === '' ) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * 使用可能なフォームフィールドオブジェクトを追加
	 * 
	 * @param Smart_Custom_Fields_Field_Base $instance
	 */
	public static function add_form_field_instance( Smart_Custom_Fields_Field_Base $instance ) {
		$type = $instance->get_attribute( 'type' );
		if ( !empty( $type ) ) {
			self::$fields[$type] = $instance;
		}
	}

	/**
	 * 使用可能なフォームフィールドオブジェクトを取得
	 * 
	 * @param string $type フォームフィールドの type
	 * @param Smart_Custom_Fields_Field_Base
	 */
	public static function get_form_field_instance( $type ) {
		if ( !empty( self::$fields[$type] ) ) {
			return clone self::$fields[$type];
		}
	}

	/**
	 * 全ての使用可能なフォームフィールドオブジェクトを取得
	 *
	 * @return array
	 */
	public static function get_form_field_instances() {
		$fields = array();
		foreach ( self::$fields as $type => $instance ) {
			$fields[$type] = self::get_form_field_instance( $type );
		}
		return $fields;
	}

	/**
	 * 管理画面で保存されたフィールドを取得
	 * 同じ投稿タイプで、同名のフィールド名を持つフィールドを複数定義しても一つしか返らないので注意
	 * 
	 * @param string $post_type
	 * @param string $field_name
	 * @return Smart_Custom_Fields_Field_Base
	 */
	public static function get_field( $post_type, $field_name ) {
		$settings = self::get_settings( get_post( get_the_ID() ) );
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$fields = $Group->get_fields();
				foreach ( $fields as $Field ) {
					if ( !is_null( $Field ) && $Field->get( 'name' ) === $field_name ) {
						return $Field;
					}
				}
			}
		}
	}
	
	/**
	 * 改行区切りの $choices を配列に変換
	 * 
	 * @param string $choices
	 * @return array
	 */
	public static function choices_eol_to_array( $choices ) {
		if ( !is_array( $choices ) ) {
			$choices = str_replace( array( "\r\n", "\r", "\n" ), "\n", $choices );
			return explode( "\n", $choices );
		}
		return $choices;
	}

	/**
	 * Setting を生成して返す
	 *
	 * @param string $id
	 * @param string $title
	 */
	public static function add_setting( $id, $title ) {
		return new Smart_Custom_Fields_Setting( $id, $title );
	}

	/**
	 * キャッシュの使用状況を画面に表示
	 */
	protected static function debug_cache_message( $message ) {
		if ( defined( 'SCF_DEBUG_CACHE' ) && SCF_DEBUG_CACHE === true ) {
			echo $message . '<br />';
		}
	}
}
