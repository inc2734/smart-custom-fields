<?php
/**
 * SCF
 * Version    : 2.0.1
 * Author     : inc2734
 * Created    : September 23, 2014
 * Modified   : JAnuary 16, 2017
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class SCF {

	/**
	 * Array of the registered fields ( Smart_Custom_Fields_Field_Base )
	 *
	 * @var array
	 */
	protected static $fields = array();

	/**
	 * Array of the custom options pages.
	 *
	 * @var array
	 */
	protected static $options_pages = array();

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
	 * @param int    $post_id
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
	 * @param int    $user_id
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
	 * @param int    $term_id
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
	 * Getting the custom options page meta data to feel good
	 *
	 * @param string $menu_slug custom options page slug
	 * @param string $name group name or field name
	 * @return mixed
	 */
	public static function get_option_meta( $menu_slug, $name = null ) {
		if ( empty( $menu_slug ) ) {
			return;
		}

		if ( ! isset( self::$options_pages[ $menu_slug ] ) ) {
			return;
		}

		$Option = self::generate_option_object( $menu_slug );

		// If $name is null, return the all meta data.
		if ( $name === null ) {
			return self::get_all_meta( $Option );
		}

		// Don't output meta data that not save in the SCF settings page
		// Getting the settings data, judged to output meta data.
		return self::get_meta( $Option, $name );
	}

	/**
	 * Getting any meta data to feel good
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param string                           $name group name or field name
	 * @return mixed
	 */
	protected static function get_meta( $object, $name ) {
		$Cache = Smart_Custom_Fields_Cache::getInstance();
		if ( $Cache->get_meta( $object, $name ) ) {
			self::debug_cache_message( "use get cache. [name: {$name}]" );
			return $Cache->get_meta( $object, $name );
		} else {
			self::debug_cache_message( "dont use get cache... [name: {$name}]" );
		}

		$settings = self::get_settings( $object );
		foreach ( $settings as $Setting ) {
			// If $name matches the group name, returns fields in the group as array.
			$Group = $Setting->get_group( $name );
			if ( $Group ) {
				$values_by_group = self::get_values_by_group( $object, $Group );
				$Cache->save_meta( $object, $name, $values_by_group );
				return $values_by_group;
			}

			// If $name doesn't matche the group name, returns the field that matches.
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$Field = $Group->get_field( $name );
				if ( $Field ) {
					$is_repeatable  = $Group->is_repeatable();
					$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
					$Cache->save_meta( $object, $name, $value_by_field );
					return $value_by_field;
				}
			}
		}
	}

	/**
	 * Getting all of any meta data to feel good
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return mixed
	 */
	protected static function get_all_meta( $object ) {
		$Cache     = Smart_Custom_Fields_Cache::getInstance();
		$settings  = self::get_settings( $object );
		$post_meta = array();
		foreach ( $settings as $Setting ) {
			$groups = $Setting->get_groups();
			foreach ( $groups as $Group ) {
				$is_repeatable = $Group->is_repeatable();
				$group_name    = $Group->get_name();
				if ( $is_repeatable && $group_name ) {
					$values_by_group = self::get_values_by_group( $object, $Group );
					$Cache->save_meta( $object, $group_name, $values_by_group );
					$post_meta[ $group_name ] = $values_by_group;
				} else {
					$fields = $Group->get_fields();
					foreach ( $fields as $Field ) {
						$field_name     = $Field->get( 'name' );
						$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
						$Cache->save_meta( $object, $field_name, $value_by_field );
						$post_meta[ $field_name ] = $value_by_field;
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
	 * Getting the meta data of the group
	 * When group, Note the point that returned data are repetition
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param Smart_Custom_Fields_Group        $Group
	 * @return mixed
	 */
	protected static function get_values_by_group( $object, $Group ) {
		$is_repeatable   = $Group->is_repeatable();
		$meta            = array();
		$fields          = $Group->get_fields();
		$value_by_fields = array();
		foreach ( $fields as $Field ) {
			if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
				$meta[0][ $Field->get( 'name' ) ] = array();
			} else {
				$meta[0][ $Field->get( 'name' ) ] = '';
			}
		}
		$default_meta = $meta[0];
		foreach ( $fields as $Field ) {
			$value_by_field = self::get_value_by_field( $object, $Field, $is_repeatable );
			foreach ( $value_by_field as $i => $value ) {
				$meta[ $i ][ $Field->get( 'name' ) ] = $value;
			}
		}
		foreach ( $meta as $i => $value ) {
			$meta[ $i ] = array_merge( $default_meta, $value );
		}
		return $meta;
	}

	/**
	 * Getting the meta data of the field
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param array                            $field
	 * @param bool                             $is_repeatable Whether the group that this field belongs is repetition
	 * @return mixed $post_meta
	 */
	protected static function get_value_by_field( $object, $Field, $is_repeatable ) {
		$field_name = $Field->get( 'name' );
		if ( ! $field_name ) {
			return;
		}

		$Meta = new Smart_Custom_Fields_Meta( $object );

		// In the case of multi-value items in the loop
		$field_type           = $Field->get_attribute( 'type' );
		$repeat_multiple_data = self::get_repeat_multiple_data( $object );
		if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[ $field_name ] ) ) {
			if ( $Meta->is_saved_the_key( $field_name ) ) {
				$_meta = $Meta->get( $field_name );
			} else {
				$_meta = self::get_default_value( $Field );
			}
			$start = 0;
			foreach ( $repeat_multiple_data[ $field_name ] as $repeat_multiple_key => $repeat_multiple_value ) {
				if ( $repeat_multiple_value === 0 ) {
					$value = array();
				} else {
					$value  = array_slice( $_meta, $start, $repeat_multiple_value );
					$start += $repeat_multiple_value;
				}
				$value                        = apply_filters( SCF_Config::PREFIX . 'validate-get-value', $value, $field_type );
				$meta[ $repeat_multiple_key ] = $value;
			}
		}
		// Other than that
		else {
			$single = true;
			if ( $Field->get_attribute( 'allow-multiple-data' ) || $is_repeatable ) {
				$single = false;
			}
			if ( $Meta->is_saved_the_key( $field_name ) ) {
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
	 * @param bool                           $single
	 * @return array|strings
	 */
	public static function get_default_value( $Field, $single = false ) {
		if ( ! is_a( $Field, 'Smart_Custom_Fields_Field_Base' ) ) {
			if ( $single ) {
				return '';
			}
			return array();
		}

		$choices = $Field->get( 'choices' );
		$default = $Field->get( 'default' );

		if ( $Field->get_attribute( 'allow-multiple-data' ) ) {
			$choices           = self::choices_eol_to_array( $choices );
			$default           = self::choices_eol_to_array( $default );
			$default_sanitized = array();

			if ( self::is_assoc( $choices ) ) {
				$_choices = array_flip( $choices );
			} else {
				$_choices = $choices;
			}
			foreach ( $default as $key => $value ) {
				if ( in_array( $value, $_choices ) ) {
					if ( preg_match( '/^\d+$/', $value ) ) {
						$value = (int) $value;
					}
					$default_sanitized[ $key ] = $value;
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
			return (array) $default;
		}
	}

	/**
	 * Getting enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return array $settings
	 */
	public static function get_settings_posts( $object ) {
		$Cache          = Smart_Custom_Fields_Cache::getInstance();
		$settings_posts = array();
		if ( $Cache->get_settings_posts( $object ) !== null ) {
			self::debug_cache_message( 'use settings posts cache.' );
			return $Cache->get_settings_posts( $object );
		} else {
			self::debug_cache_message( 'dont use settings posts cache...' );
		}

		$Meta  = new Smart_Custom_Fields_Meta( $object );
		$types = $Meta->get_types( false );

		switch ( $Meta->get_meta_type() ) {
			case 'post':
				$key = SCF_Config::PREFIX . 'condition';
				break;
			case 'user':
				$key = SCF_Config::PREFIX . 'roles';
				break;
			case 'term':
				$key = SCF_Config::PREFIX . 'taxonomies';
				break;
			case 'option':
				$key = SCF_Config::PREFIX . 'options-pages';
				break;
			default:
				$key = '';
		}

		if ( ! empty( $key ) && ( ! empty( $types ) ) ) {
			$meta_query = array();
			foreach ( $types as $type ) {
				$meta_query[] = array(
					'key'     => $key,
					'value'   => sprintf( '"%s"', $type ),
					'compare' => 'LIKE',
				);
			}
			if ( $meta_query ) {
				$meta_query['relation'] = 'OR';
			}

			$args = array(
				'post_type'      => SCF_Config::NAME,
				'posts_per_page' => -1,
				'order'          => 'ASC',
				'order_by'       => 'menu_order',
				'meta_query'     => $meta_query,
			);

			$settings_posts = get_posts( $args );
		}

		$Cache = Smart_Custom_Fields_Cache::getInstance();
		$Cache->save_settings_posts( $object, $settings_posts );
		return $settings_posts;
	}

	/**
	 * Getting array of the Setting object
	 *
	 * @param WP_Post|WP_User|WP_Term|Smart_Custom_Fields_Options_Mock $object
	 * @return array $settings
	 */
	public static function get_settings( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type( false );
		$types     = $Meta->get_types( false );
		$meta_type = $Meta->get_meta_type();

		// IF the post that has custom field settings according to post ID,
		// don't display because the post ID would change in preview.
		// So if in preview, re-getting post ID from original post (parent of the preview).
		if ( $meta_type === 'post' && $object->post_type === 'revision' ) {
			$object = get_post( $object->post_parent );
		}

		$settings = array();

		if ( ! empty( $types ) ) {
			$settings_posts = self::get_settings_posts( $object );
			if ( $meta_type === 'post' ) {
				$settings = self::get_settings_for_post( $object, $settings_posts );
			} elseif ( $meta_type === 'user' ) {
				$settings = self::get_settings_for_profile( $object, $settings_posts );
			} elseif ( $meta_type === 'term' ) {
				$settings = self::get_settings_for_term( $object, $settings_posts );
			} elseif ( $meta_type === 'option' ) {
				$settings = self::get_settings_for_option( $object, $settings_posts );
			}
		}
		$settings = apply_filters(
			SCF_Config::PREFIX . 'register-fields',
			$settings,
			$type,
			$id,
			$meta_type,
			$types
		);
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		return $settings;
	}

	/**
	 * Getting the Setting object for post
	 *
	 * @param WP_Post $object
	 * @param array   $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_post( $object, $settings_posts ) {
		$Cache    = Smart_Custom_Fields_Cache::getInstance();
		$settings = array();
		foreach ( $settings_posts as $settings_post ) {
			if ( $Cache->get_settings( $settings_post->ID ) !== null ) {
				self::debug_cache_message( "use settings cache. [id: {$settings_post->ID}]" );
				$Setting = $Cache->get_settings( $settings_post->ID, $object );
				if ( $Setting ) {
					$settings[ $settings_post->ID ] = $Setting;
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
					$Setting           = self::add_setting( $settings_post->ID, $settings_post->post_title );
					if ( $object->ID == $condition_post_id ) {
						$settings[ $settings_post->ID ] = $Setting;
					}
					$Post = get_post( $condition_post_id );
					if ( empty( $Post ) ) {
						$Post = self::generate_post_object( $condition_post_id );
					}
					$Cache->save_settings( $settings_post->ID, $Setting, $Post );
				}
			} else {
				$Setting                        = self::add_setting( $settings_post->ID, $settings_post->post_title );
				$settings[ $settings_post->ID ] = $Setting;
				$Cache->save_settings( $settings_post->ID, $Setting );
			}
		}
		return $settings;
	}

	/**
	 * Getting the Setting object for user
	 *
	 * @param WP_User $object
	 * @param array   $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_profile( $object, $settings_posts ) {
		$Cache    = Smart_Custom_Fields_Cache::getInstance();
		$settings = array();
		foreach ( $settings_posts as $settings_post ) {
			if ( $Cache->get_settings( $settings_post->ID ) !== null ) {
				self::debug_cache_message( "use settings cache. [id: {$settings_post->ID}]" );
				$settings[] = $Cache->get_settings( $settings_post->ID );
				continue;
			}
			self::debug_cache_message( "dont use settings cache... [id: {$settings_post->ID}]" );
			$Setting    = self::add_setting( $settings_post->ID, $settings_post->post_title );
			$settings[] = $Setting;
			$Cache->save_settings( $settings_post->ID, $Setting );
		}
		return $settings;
	}

	/**
	 * Getting the Setting object for term
	 *
	 * @param WP_Term $object
	 * @param array   $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_term( $object, $settings_posts ) {
		return self::get_settings_for_profile( $object, $settings_posts );
	}

	/**
	 * Getting the Setting object for option
	 *
	 * @param WP_Term $object
	 * @param array   $settings_posts
	 * @return array
	 */
	protected static function get_settings_for_option( $object, $settings_posts ) {
		return self::get_settings_for_profile( $object, $settings_posts );
	}

	/**
	 * Getting delimited identification data of the repeated multi-value items
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return array
	 */
	public static function get_repeat_multiple_data( $object ) {
		$Cache                = Smart_Custom_Fields_Cache::getInstance();
		$repeat_multiple_data = array();
		if ( $Cache->get_repeat_multiple_data( $object ) ) {
			return $Cache->get_repeat_multiple_data( $object );
		}

		$Meta                  = new Smart_Custom_Fields_Meta( $object );
		$_repeat_multiple_data = $Meta->get( SCF_Config::PREFIX . 'repeat-multiple-data', true );
		if ( ! empty( $_repeat_multiple_data ) ) {
			$repeat_multiple_data = $_repeat_multiple_data;
		}

		$Cache->save_repeat_multiple_data( $object, $repeat_multiple_data );
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
	 * Whether the associative array or not
	 *
	 * @see http://qiita.com/ka215/items/a14e53547e717d2a564f
	 * @param array   $data This argument should be expected an array
	 * @param boolean $multidimensional True if a multidimensional array is inclusion into associative array, the default value is false
	 * @return boolean
	 */
	public static function is_assoc( $data, $multidimensional = false ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}
		$has_array = false;
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$has_array = true;
			}

			if ( ! is_int( $key ) ) {
				return true;
			}
		}
		return $multidimensional && $has_array ? true : false;
	}

	/**
	 * Adding the available form field object
	 *
	 * @param Smart_Custom_Fields_Field_Base $instance
	 */
	public static function add_form_field_instance( Smart_Custom_Fields_Field_Base $instance ) {
		$type = $instance->get_attribute( 'type' );
		if ( ! empty( $type ) ) {
			self::$fields[ $type ] = $instance;
		}
	}

	/**
	 * Getting the available form field object
	 *
	 * @param string                         $type type of the form field
	 * @param Smart_Custom_Fields_Field_Base
	 */
	public static function get_form_field_instance( $type ) {
		if ( ! empty( self::$fields[ $type ] ) ) {
			return clone self::$fields[ $type ];
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
			$fields[ $type ] = self::get_form_field_instance( $type );
		}
		return $fields;
	}

	/**
	 * Getting custom fields that saved custo field settings page
	 * Note that not return only one even define multiple fields with the same name of the field name
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param string                           $field_name
	 * @return Smart_Custom_Fields_Field_Base|null
	 */
	public static function get_field( $object, $field_name ) {
		$settings = self::get_settings( $object );
		foreach ( $settings as $Setting ) {
			$fields = $Setting->get_fields();
			if ( ! empty( $fields[ $field_name ] ) ) {
				return $fields[ $field_name ];
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
		if ( ! is_array( $choices ) ) {
			if ( $choices === '' || $choices === false || $choices === null ) {
				return array();
			}
			$_choices = str_replace( array( "\r\n", "\r", "\n" ), "\n", $choices );
			$_choices = explode( "\n", $_choices );
			$choices  = array();
			foreach ( $_choices as $_choice ) {
				$_choice = array_map( 'trim', explode( '=>', $_choice ) );
				if ( count( $_choice ) === 2 ) {
					$choices[ $_choice[0] ] = $_choice[1];
				} else {
					$choices = array_merge( $choices, $_choice );
				}
			}
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
	 * Adding custom options page
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $menu_slug
	 * @param string $icon_url
	 * @param int    $position
	 * @return $menu_slug
	 */
	public static function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $icon_url = '', $position = null ) {
		self::$options_pages[ $menu_slug ] = $menu_title;
		new Smart_Custom_Fields_Options_Page( $page_title, $menu_title, $capability, $menu_slug, $icon_url, $position );
		return $menu_slug;
	}

	/**
	 * Return array of custom options pages
	 *
	 * @return array
	 */
	public static function get_options_pages() {
		return self::$options_pages;
	}

	/**
	 * Generate WP_Post object
	 *
	 * @param int    $post_id
	 * @param string $post_type
	 * @return WP_Post
	 */
	public static function generate_post_object( $post_id, $post_type = null ) {
		$Post            = new stdClass();
		$Post->ID        = $post_id;
		$Post->post_type = $post_type;
		return new WP_Post( $Post );
	}

	/**
	 * Generate option object
	 *
	 * @param string $menu_slug
	 * @return stdClass
	 */
	public static function generate_option_object( $menu_slug ) {
		$options_pages = self::get_options_pages();
		if ( ! isset( $options_pages[ $menu_slug ] ) ) {
			return;
		}
		$Option             = new stdClass();
		$Option->menu_slug  = $menu_slug;
		$Option->menu_title = $options_pages[ $menu_slug ];
		return $Option;
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
