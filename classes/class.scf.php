<?php
/**
 * SCF
 * Version    : 1.3.2
 * Author     : inc2734
 * Created    : September 23, 2014
 * Modified   : January 7, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class SCF {

	/**
	 * Array of the registered fields ( Smart_Custom_Fields_Field_Base )
	 * @var array
	 */
	protected static $fields = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $cache.
	 * Using post_id as key.
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $settings_posts_cache.
	 * Using post_type as key.
	 * @var array
	 */
	protected static $settings_posts_cache = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $settings_cache.
	 * Using post_type as key.
	 * @var array
	 */
	public static $settings_cache = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $repeat_multiple_data_cache.
	 * Using post_id as key.
	 * @var array
	 */
	protected static $repeat_multiple_data_cache = array();

	/**
	 * Clear all caches.
	 */
	public static function clear_all_cache() {
		self::clear_cache();
		self::clear_settings_posts_cache();
		self::clear_settings_cache();
		self::clear_repeat_multiple_data_cache();
	}

	/**
	 * Getting all of the post meta data to feel good
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

		// Don't output meta data that not save in the SCF settings page
		// Getting the settings data, judged to output meta data.
		return self::get_all_meta( get_post( $post_id ) );
	}

	/**
	 * Getting the post meta data to feel good
	 *
	 * @param string $name group name or field name
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
		
		// Don't output meta data that not save in the SCF settings page
		// Getting the settings data, judged to output meta data.
		return self::get_meta( get_post( $post_id ), $name );
	}

	/**
 	 * Getting the user meta data to feel good
	 *
	 * @param int $user_id
	 * @param string $name group name or field name
	 * @return mixed
	 */
	public static function get_user_meta( $user_id, $name = null ) {
		if ( empty( $user_id ) ) {
			return;
		}

		// If $name is null, return the all meta data.
		if ( $name === null ) {
			return self::get_all_meta( get_userdata( $user_id ) );
		}
		
		// Don't output meta data that not save in the SCF settings page.
		// Getting the settings data, judged to output meta data.
		return self::get_meta( get_userdata( $user_id ), $name );
	}

	/**
  	 * Getting the term meta data to feel good
	 *
	 * @param int $term_id
	 * @param string $taxonomy_name
	 * @param string $name group name or field name
	 * @return mixed
	 */
	public static function get_term_meta( $term_id, $taxonomy_name, $name = null ) {
		if ( empty( $term_id ) || empty( $taxonomy_name ) ) {
			return;
		}

		// If $name is null, return the all meta data.
		if ( $name === null ) {
			return self::get_all_meta( get_term( $term_id, $taxonomy_name ) );
		}
		
		// Don't output meta data that not save in the SCF settings page
		// Getting the settings data, judged to output meta data.
		return self::get_meta( get_term( $term_id, $taxonomy_name ), $name );
	}

	/**
	 * Getting any meta data to feel good
	 *
	 * @param WP_Post|WP_User|object $object
	 * @param string $name group name or field name
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
			// If $name matches the group name, returns fields in the group as array.
			$Group = $Setting->get_group( $name );
			if ( $Group ) {
				$values_by_group = self::get_values_by_group( $object, $Group );
				self::save_cache( $object, $name, $values_by_group );
				return $values_by_group;
			}
			
			// If $name doesn't matche the group name, returns the field that matches.
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$Field = $Group->get_field( $name );
				if ( $Field ) {
					$is_repeatable = $Group->is_repeatable();
					$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
					self::save_cache( $object, $name, $value_by_field );
					return $value_by_field;
				}
			}
		}
	}

	/**
 	 * Getting all of any meta data to feel good
	 *
	 * @param WP_Post|WP_User|WP_Term $object
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
						self::save_cache( $object, $field_name, $value_by_field );
						$post_meta[$field_name] = $value_by_field;
					}
				}
			}
		}
		return $post_meta;
	}

	/**
	 * If in preview, return the preview post ID
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
	 * Saving to cache
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @param string $name
	 * @param mixed $data
	 */
	protected static function save_cache( $object, $name, $data ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) && !empty( $meta_type ) ) {
			self::$cache[$meta_type . '_' . $type . '_' . $id][$name] = $data;
		}
	}

	/**
	 * Getting the cache
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @param string $name
	 * @return mixed
	 */
	protected static function get_cache( $object, $name = null ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) && !empty( $meta_type ) ) {
			if ( is_null( $name ) ) {
				if ( isset( self::$cache[$meta_type . '_' . $type . '_' . $id] ) ) {
					return self::$cache[$meta_type . '_' . $type . '_' . $id];
				}
			} else {
				if ( isset( self::$cache[$meta_type . '_' . $type . '_' . $id][$name] ) ) {
					return self::$cache[$meta_type . '_' . $type . '_' . $id][$name];
				}
			}
		}
	}

	/**
	 * Clear caches
	 */
	public static function clear_cache() {
		self::$cache = array();
	}

	/**
	 * Getting the meta data of the group
	 * When group, Note the point that returned data are repetition
	 *
	 * @param WP_Post|WP_User|WP_Term $object
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
	 * Getting the meta data of the field
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @param array $field
	 * @param bool $is_repeatable Whether the group that this field belongs is repetition
	 * @return mixed $post_meta
	 */
	protected static function get_value_by_field( $object, $Field, $is_repeatable ) {
		$field_name = $Field->get( 'name' );
		if ( !$field_name ) {
			return;
		}

		$Meta = new Smart_Custom_Fields_Meta( $object );

		// In the case of multi-value items in the loop
		$field_type = $Field->get_attribute( 'type' );
		$repeat_multiple_data = self::get_repeat_multiple_data( $object );
		if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[$field_name] ) ) {
			if ( $Meta->is_saved() ) {
				$_meta = $Meta->get( $field_name );
			} else {
				$_meta = self::get_default_value( $Field );
			}
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
		// Other than that
		else {
			$single = true;
			if ( $Field->get_attribute( 'allow-multiple-data' ) || $is_repeatable ) {
				$single = false;
			}
			if ( $Meta->is_saved() ) {
				$meta = $Meta->get( $field_name, $single );
			} else {
				$meta = self::get_default_value( $Field, $single );
			}
			$meta = apply_filters( SCF_Config::PREFIX . 'validate-get-value', $meta, $field_type );
		}
		return $meta;
	}

	/**
	 * Return the default value
	 *
	 * @param Smart_Custom_Fields_Field_Base $Field
	 * @param bool $single
	 * @return array|strings
	 */
	public static function get_default_value( $Field, $single = false ) {
		if ( !is_a( $Field, 'Smart_Custom_Fields_Field_Base' ) ) {
			if ( $single ) {
				return '';
			}
			return array();
		}

		$choices = $Field->get( 'choices' );
		$default = $Field->get( 'default' );

		if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
			$choices = SCF::choices_eol_to_array( $choices );
			$default = SCF::choices_eol_to_array( $default );
			$default_sanitized = array();
			foreach ( $default as $key => $value ) {
				if ( in_array( $value, $choices ) ) {
					$default_sanitized[$key] = $value;
				}
			}
			return $default_sanitized;
		}

		// Return string
		if ( $single ) {
			return $default;
		}
		// Return array
		else {
			if ( is_array( $default ) ) {
				return $default;
			}
			if ( $default === '' || $default === false || $default === null ) {
				return array();
			}
			return ( array ) $default;
		}
	}

	/**
	 * Saving to cache that enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @param array $settings_posts
	 */
	protected static function save_settings_posts_cache( $object, $settings_posts ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		self::$settings_posts_cache[$meta_type . '_' . $type] = $settings_posts;
	}

	/**
 	 * Getting cache that enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @return array|null
	 */
	public static function get_settings_posts_cache( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		if ( isset( self::$settings_posts_cache[$meta_type . '_' . $type] ) ) {
			return self::$settings_posts_cache[$meta_type . '_' . $type];
		}
	}

	/**
	 * Clear the $settings_posts_cache
	 */
	public static function clear_settings_posts_cache() {
		self::$settings_posts_cache = array();
	}

	/**
  	 * Getting enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term $object
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
			case 'term' :
				$key = SCF_Config::PREFIX . 'taxonomies';
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
						'value'   => sprintf( '"%s"', $type ),
					),
				),
			) );
		}
		self::save_settings_posts_cache( $object, $settings_posts );
		return $settings_posts;
	}

	/**
	 * Saving the Setting object to cache
	 *
	 * @param int $settings_post_id
	 * @param WP_post|WP_User|WP_term $object
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
	 * Getting the Setting object cache
	 * If there isn't the custom field settings ... null
	 * If there is custom field settings
	 *     If there is no data for the specified $ meta_type + $id
	 *         There is a thing of the General ... Smart_Custom_Fields_Setting
	 *         There isn't a thing of the General ... false
	 *     If there the data for the specified $meta_type + $id ... Smart_Custom_Fields_Setting
	 *
	 * @param int $settings_post_id
	 * @param WP_post|WP_User|WP_Term $object
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
	 * Clear the $settings_cache
	 */
	public static function clear_settings_cache() {
		self::$settings_cache = array();
	}

	/**
	 * Getting array of the Setting object
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @return array $settings
	 */
	public static function get_settings( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		
		// IF the post that has custom field settings according to post ID,
		// don't display because the post ID would change in preview.
		// So if in preview, re-getting post ID from original post (parent of the preview).
		if ( $meta_type === 'post' && $object->post_type === 'revision' ) {
			$object = get_post( $object->post_parent );
		}

		$settings = array();
		if ( !empty( $type ) ) {
			$settings_posts = self::get_settings_posts( $object );
			if ( $meta_type === 'post' ) {
				$settings = self::get_settings_for_post( $object, $settings_posts );
			}
			elseif ( $meta_type === 'user' ) {
				$settings = self::get_settings_for_profile( $object, $settings_posts );
			}
			elseif ( $meta_type === 'term' ) {
				$settings = self::get_settings_for_term( $object, $settings_posts );
			}
		}
		$settings = apply_filters(
			SCF_Config::PREFIX . 'register-fields',
			$settings,
			$type,
			$id,
			$meta_type
		);
		if ( !is_array( $settings ) ) {
			$settings = array();
		}
		return $settings;
	}

	/**
	 * Getting the Setting object for post
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
 	 * Getting the Setting object for user
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
  	 * Getting the Setting object for term
	 *
	 * @param WP_Term $object
	 * @param array $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_term( $object, $settings_posts ) {
		return self::get_settings_for_profile( $object, $settings_posts );
	}

	/**
	 * Saving the delimited identification data of the repeated multi-value items to cache
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @param mixed $repeat_multiple_data
	 */
	protected static function save_repeat_multiple_data_cache( $object, $repeat_multiple_data ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) && !empty( $meta_type ) ) {
			self::$repeat_multiple_data_cache[$meta_type . '_' . $type . '_' . $id] = $repeat_multiple_data;
		}
	}

	/**
	 * Getting delimited identification data of the repeated multi-value items from cache
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @return mixed
	 */
	protected static function get_repeat_multiple_data_cache( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( !empty( $id ) && !empty( $type ) ) {
			if ( isset( self::$repeat_multiple_data_cache[$meta_type . '_' . $type . '_' . $id] ) ) {
				return self::$repeat_multiple_data_cache[$meta_type . '_' . $type . '_' . $id];
			}
		}
	}

	/**
	 * Clear delimited identification data of the repeated multi-value items cache
	 */
	public static function clear_repeat_multiple_data_cache() {
		self::$repeat_multiple_data_cache = array();
	}

	/**
 	 * Getting delimited identification data of the repeated multi-value items
	 *
	 * @param WP_Post|WP_User|WP_Term $object
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
	 * Return true if null or empty value
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
	 * Adding the available form field object
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
	 * Getting the available form field object
	 *
	 * @param string $type type of the form field
	 * @param Smart_Custom_Fields_Field_Base
	 */
	public static function get_form_field_instance( $type ) {
		if ( !empty( self::$fields[$type] ) ) {
			return clone self::$fields[$type];
		}
	}

	/**
	 * Getting all available form field object
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
	 * Getting custom fields that saved custo field settings page
	 * Note that not return only one even define multiple fields with the same name of the field name
	 *
	 * @param WP_Post|WP_User|WP_Term $object
	 * @param string $field_name
	 * @return Smart_Custom_Fields_Field_Base|null
	 */
	public static function get_field( $object, $field_name ) {
		$settings = self::get_settings( $object );
		foreach ( $settings as $Setting ) {
			$fields = $Setting->get_fields();
			if ( !empty( $fields[$field_name] ) ) {
				return $fields[$field_name];
			}
		}
	}
	
	/**
	 * Convert to array from newline delimiter $choices
	 *
	 * @param string $choices
	 * @return array
	 */
	public static function choices_eol_to_array( $choices ) {
		if ( !is_array( $choices ) ) {
			if ( $choices === '' || $choices === false || $choices === null ) {
				return array();
			}
			$choices = str_replace( array( "\r\n", "\r", "\n" ), "\n", $choices );
			return explode( "\n", $choices );
		}
		return $choices;
	}

	/**
	 * Return generated Setting object
	 *
	 * @param string $id
	 * @param string $title
	 * @return Smart_Custom_Fields_Setting
	 */
	public static function add_setting( $id, $title ) {
		return new Smart_Custom_Fields_Setting( $id, $title );
	}

	/**
	 * Print cache usage
	 */
	protected static function debug_cache_message( $message ) {
		if ( defined( 'SCF_DEBUG_CACHE' ) && SCF_DEBUG_CACHE === true ) {
			echo $message . '<br />';
		}
	}
}
