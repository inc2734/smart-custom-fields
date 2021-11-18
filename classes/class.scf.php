<?php
/**
 * @package smart-custom-fields
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * SCF class.
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
	 * Getting all of the post meta data to feel good.
	 *
	 * @param int $post_id Post id.
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
	 * Getting the post meta data to feel good.
	 *
	 * @param string $name    Group name or field name.
	 * @param int    $post_id Post id.
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

		// Don't output meta data that not save in the SCF settings page.
		// Getting the settings data, judged to output meta data.
		return self::get_meta( get_post( $post_id ), $name );
	}

	/**
	 * Getting the user meta data to feel good.
	 *
	 * @param int    $user_id User id.
	 * @param string $name    Group name or field name.
	 * @return mixed
	 */
	public static function get_user_meta( $user_id, $name = null ) {
		if ( empty( $user_id ) ) {
			return;
		}

		// If $name is null, return the all meta data.
		if ( null === $name ) {
			return self::get_all_meta( get_userdata( $user_id ) );
		}

		// Don't output meta data that not save in the SCF settings page.
		// Getting the settings data, judged to output meta data.
		return self::get_meta( get_userdata( $user_id ), $name );
	}

	/**
	 * Getting the term meta data to feel good.
	 *
	 * @param int    $term_id       Term id.
	 * @param string $taxonomy_name Taxonomy name.
	 * @param string $name          Group name or field name.
	 * @return mixed
	 */
	public static function get_term_meta( $term_id, $taxonomy_name, $name = null ) {
		if ( empty( $term_id ) || empty( $taxonomy_name ) ) {
			return;
		}

		// If $name is null, return the all meta data.
		if ( null === $name ) {
			return self::get_all_meta( get_term( $term_id, $taxonomy_name ) );
		}

		// Don't output meta data that not save in the SCF settings page
		// Getting the settings data, judged to output meta data.
		return self::get_meta( get_term( $term_id, $taxonomy_name ), $name );
	}

	/**
	 * Getting the custom options page meta data to feel good.
	 *
	 * @param string $menu_slug custom options page slug.
	 * @param string $name group name or field name.
	 * @return mixed
	 */
	public static function get_option_meta( $menu_slug, $name = null ) {
		if ( empty( $menu_slug ) ) {
			return;
		}

		if ( ! isset( self::$options_pages[ $menu_slug ] ) ) {
			return;
		}

		$option = self::generate_option_object( $menu_slug );

		// If $name is null, return the all meta data.
		if ( null === $name ) {
			return self::get_all_meta( $option );
		}

		// Don't output meta data that not save in the SCF settings page
		// Getting the settings data, judged to output meta data.
		return self::get_meta( $option, $name );
	}

	/**
	 * Getting any meta data to feel good.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @param string                           $name   Group name or field name.
	 * @return mixed
	 */
	protected static function get_meta( $object, $name ) {
		$cache = Smart_Custom_Fields_Cache::get_instance();
		if ( $cache->get_meta( $object, $name ) ) {
			self::debug_cache_message( "use get cache. [name: {$name}]" );
			return $cache->get_meta( $object, $name );
		} else {
			self::debug_cache_message( "dont use get cache... [name: {$name}]" );
		}

		$settings = self::get_settings( $object );
		foreach ( $settings as $setting ) {
			// If $name matches the group name, returns fields in the group as array.
			$group = $setting->get_group( $name );
			if ( $group ) {
				$values_by_group = self::get_values_by_group( $object, $group );
				$cache->save_meta( $object, $name, $values_by_group );
				return $values_by_group;
			}

			// If $name doesn't matche the group name, returns the field that matches.
			$groups = $setting->get_groups();
			foreach ( $groups as $group ) {
				$field = $group->get_field( $name );
				if ( $field ) {
					$is_repeatable  = $group->is_repeatable();
					$value_by_field = self::get_value_by_field( $object, $field, $is_repeatable );
					$cache->save_meta( $object, $name, $value_by_field );
					return $value_by_field;
				}
			}
		}
	}

	/**
	 * Getting all of any meta data to feel good.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @return mixed
	 */
	protected static function get_all_meta( $object ) {
		$cache     = Smart_Custom_Fields_Cache::get_instance();
		$settings  = self::get_settings( $object );
		$post_meta = array();
		foreach ( $settings as $setting ) {
			$groups = $setting->get_groups();
			foreach ( $groups as $group ) {
				$is_repeatable = $group->is_repeatable();
				$group_name    = $group->get_name();
				if ( $is_repeatable && $group_name ) {
					$values_by_group = self::get_values_by_group( $object, $group );
					$cache->save_meta( $object, $group_name, $values_by_group );
					$post_meta[ $group_name ] = $values_by_group;
				} else {
					$fields = $group->get_fields();
					foreach ( $fields as $field ) {
						$field_name     = $field->get( 'name' );
						$value_by_field = self::get_value_by_field( $object, $field, $is_repeatable );
						$cache->save_meta( $object, $field_name, $value_by_field );
						$post_meta[ $field_name ] = $value_by_field;
					}
				}
			}
		}
		return $post_meta;
	}

	/**
	 * If in preview, return the preview post ID.
	 *
	 * @param int $post_id Post id.
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
	 * Getting the meta data of the group.
	 * When group, Note the point that returned data are repetition.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @param Smart_Custom_Fields_Group        $group  Group object.
	 * @return mixed
	 */
	protected static function get_values_by_group( $object, $group ) {
		$is_repeatable = $group->is_repeatable();
		$meta          = array();
		$fields        = $group->get_fields();

		foreach ( $fields as $field ) {
			if ( $field->get_attribute( 'allow-multiple-data' ) ) {
				$meta[0][ $field->get( 'name' ) ] = array();
			} else {
				$meta[0][ $field->get( 'name' ) ] = '';
			}
		}
		$default_meta = $meta[0];
		foreach ( $fields as $field ) {
			$value_by_field = self::get_value_by_field( $object, $field, $is_repeatable );
			foreach ( $value_by_field as $i => $value ) {
				$meta[ $i ][ $field->get( 'name' ) ] = $value;
			}
		}
		foreach ( $meta as $i => $value ) {
			$meta[ $i ] = array_merge( $default_meta, $value );
		}
		return $meta;
	}

	/**
	 * Getting the meta data of the field.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @param Smart_Custom_Fields_Field_Base   $field  Field object.
	 * @param bool                             $is_repeatable Whether the group that this field belongs is repetition.
	 * @return mixed $post_meta
	 */
	protected static function get_value_by_field( $object, $field, $is_repeatable ) {
		$field_name = $field->get( 'name' );
		if ( ! $field_name ) {
			return;
		}

		$meta = new Smart_Custom_Fields_Meta( $object );

		// In the case of multi-value items in the loop
		$field_type           = $field->get_attribute( 'type' );
		$repeat_multiple_data = self::get_repeat_multiple_data( $object );
		if ( is_array( $repeat_multiple_data ) && isset( $repeat_multiple_data[ $field_name ] ) ) {
			if ( $meta->is_saved_the_key( $field_name ) ) {
				$_meta = $meta->get( $field_name );
			} else {
				$_meta = self::get_default_value( $field );
			}
			$start      = 0;
			$meta_value = [];
			foreach ( $repeat_multiple_data[ $field_name ] as $repeat_multiple_key => $repeat_multiple_value ) {
				if ( 0 === $repeat_multiple_value ) {
					$value = array();
				} else {
					$value  = array_slice( $_meta, $start, $repeat_multiple_value );
					$start += $repeat_multiple_value;
				}
				$value                              = apply_filters( SCF_Config::PREFIX . 'validate-get-value', $value, $field_type );
				$meta_value[ $repeat_multiple_key ] = $value;
			}
		} else {
			// Other than that
			$single = true;
			if ( $field->get_attribute( 'allow-multiple-data' ) || $is_repeatable ) {
				$single = false;
			}
			if ( $meta->is_saved_the_key( $field_name ) ) {
				$meta_value = $meta->get( $field_name, $single );
			} else {
				$meta_value = self::get_default_value( $field, $single );
			}
			$meta_value = apply_filters( SCF_Config::PREFIX . 'validate-get-value', $meta_value, $field_type );
		}
		return $meta_value;
	}

	/**
	 * Return the default value.
	 *
	 * @param Smart_Custom_Fields_Field_Base $field  Field object.
	 * @param bool                           $single Whether to return a single value. This parameter has no effect if $key is not specified.
	 * @return array|strings
	 */
	public static function get_default_value( $field, $single = false ) {
		if ( ! is_a( $field, 'Smart_Custom_Fields_Field_Base' ) ) {
			if ( $single ) {
				return '';
			}
			return array();
		}

		$choices = $field->get( 'choices' );
		$default = $field->get( 'default' );

		if ( $field->get_attribute( 'allow-multiple-data' ) ) {
			$choices           = self::choices_eol_to_array( $choices );
			$default           = self::choices_eol_to_array( $default );
			$default_sanitized = array();

			if ( self::is_assoc( $choices ) ) {
				$_choices = array_flip( $choices );
			} else {
				$_choices = $choices;
			}
			foreach ( $default as $key => $value ) {
				if ( in_array( $value, $_choices, true ) ) {
					if ( preg_match( '/^\d+$/', $value ) ) {
						$value = (int) $value;
					}
					$default_sanitized[ $key ] = $value;
				}
			}
			return $default_sanitized;
		}

		if ( $single ) {
			// Return string
			return $default;
		} else {
			// Return array
			if ( is_array( $default ) ) {
				return $default;
			}
			if ( '' === $default || false === $default || null === $default ) {
				return array();
			}
			return (array) $default;
		}
	}

	/**
	 * Getting enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @return array
	 */
	public static function get_settings_posts( $object ) {
		$cache          = Smart_Custom_Fields_Cache::get_instance();
		$settings_posts = array();
		if ( null !== $cache->get_settings_posts( $object ) ) {
			self::debug_cache_message( 'use settings posts cache.' );
			return $cache->get_settings_posts( $object );
		} else {
			self::debug_cache_message( 'dont use settings posts cache...' );
		}

		$meta  = new Smart_Custom_Fields_Meta( $object );
		$types = $meta->get_types( false );

		switch ( $meta->get_meta_type() ) {
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

		$cache = Smart_Custom_Fields_Cache::get_instance();
		$cache->save_settings_posts( $object, $settings_posts );
		return $settings_posts;
	}

	/**
	 * Getting array of the Setting object.
	 *
	 * @param WP_Post|WP_User|WP_Term|Smart_Custom_Fields_Options_Mock $object Object meta object.
	 * @return array
	 */
	public static function get_settings( $object ) {
		$meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $meta->get_id();
		$type      = $meta->get_type( false );
		$types     = $meta->get_types( false );
		$meta_type = $meta->get_meta_type();

		// IF the post that has custom field settings according to post ID,
		// don't display because the post ID would change in preview.
		// So if in preview, re-getting post ID from original post (parent of the preview).
		if ( 'post' === $meta_type && 'revision' === $object->post_type ) {
			$object = get_post( $object->post_parent );
		}

		$settings = array();

		if ( ! empty( $types ) ) {
			$settings_posts = self::get_settings_posts( $object );
			if ( 'post' === $meta_type ) {
				$settings = self::get_settings_for_post( $object, $settings_posts );
			} elseif ( 'user' === $meta_type ) {
				$settings = self::get_settings_for_profile( $object, $settings_posts );
			} elseif ( 'term' === $meta_type ) {
				$settings = self::get_settings_for_term( $object, $settings_posts );
			} elseif ( 'option' === $meta_type ) {
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
	 * Getting the Setting object for post.
	 *
	 * @param WP_Term $object         WP_Term object.
	 * @param array   $settings_posts Settings.
	 * @return array
	 */
	protected static function get_settings_for_post( $object, $settings_posts ) {
		$cache    = Smart_Custom_Fields_Cache::get_instance();
		$settings = array();
		foreach ( $settings_posts as $settings_post ) {
			if ( $cache->get_settings( $settings_post->ID ) !== null ) {
				self::debug_cache_message( "use settings cache. [id: {$settings_post->ID}]" );
				$setting = $cache->get_settings( $settings_post->ID, $object );
				if ( $setting ) {
					$settings[ $settings_post->ID ] = $setting;
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
					$setting           = self::add_setting( $settings_post->ID, $settings_post->post_title );
					if ( (int) $object->ID === (int) $condition_post_id ) {
						$settings[ $settings_post->ID ] = $setting;
					}
					$post = get_post( $condition_post_id );
					if ( empty( $post ) ) {
						$post = self::generate_post_object( $condition_post_id );
					}
					$cache->save_settings( $settings_post->ID, $setting, $post );
				}
			} else {
				$setting                        = self::add_setting( $settings_post->ID, $settings_post->post_title );
				$settings[ $settings_post->ID ] = $setting;
				$cache->save_settings( $settings_post->ID, $setting );
			}
		}
		return $settings;
	}

	/**
	 * Getting the Setting object for user.
	 *
	 * @param WP_Term $object         WP_Term object.
	 * @param array   $settings_posts Settings.
	 * @return array
	 */
	protected static function get_settings_for_profile( $object, $settings_posts ) {
		$cache    = Smart_Custom_Fields_Cache::get_instance();
		$settings = array();
		foreach ( $settings_posts as $settings_post ) {
			if ( $cache->get_settings( $settings_post->ID ) !== null ) {
				self::debug_cache_message( "use settings cache. [id: {$settings_post->ID}]" );
				$settings[] = $cache->get_settings( $settings_post->ID );
				continue;
			}
			self::debug_cache_message( "dont use settings cache... [id: {$settings_post->ID}]" );
			$setting    = self::add_setting( $settings_post->ID, $settings_post->post_title );
			$settings[] = $setting;
			$cache->save_settings( $settings_post->ID, $setting );
		}
		return $settings;
	}

	/**
	 * Getting the Setting object for term.
	 *
	 * @param WP_Term $object         WP_Term object.
	 * @param array   $settings_posts Settings.
	 * @return array
	 */
	protected static function get_settings_for_term( $object, $settings_posts ) {
		return self::get_settings_for_profile( $object, $settings_posts );
	}

	/**
	 * Getting the Setting object for option.
	 *
	 * @param WP_Term $object         WP_Term object.
	 * @param array   $settings_posts Settings.
	 * @return array
	 */
	protected static function get_settings_for_option( $object, $settings_posts ) {
		return self::get_settings_for_profile( $object, $settings_posts );
	}

	/**
	 * Getting delimited identification data of the repeated multi-value items.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object Object meta object.
	 * @return array
	 */
	public static function get_repeat_multiple_data( $object ) {
		$cache                = Smart_Custom_Fields_Cache::get_instance();
		$repeat_multiple_data = array();
		if ( $cache->get_repeat_multiple_data( $object ) ) {
			return $cache->get_repeat_multiple_data( $object );
		}

		$meta                  = new Smart_Custom_Fields_Meta( $object );
		$_repeat_multiple_data = $meta->get( SCF_Config::PREFIX . 'repeat-multiple-data', true );
		if ( ! empty( $_repeat_multiple_data ) ) {
			$repeat_multiple_data = $_repeat_multiple_data;
		}

		$cache->save_repeat_multiple_data( $object, $repeat_multiple_data );
		return $repeat_multiple_data;
	}

	/**
	 * Return true if null or empty value.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function is_empty( &$value ) {
		if ( isset( $value ) ) {
			if ( is_null( $value ) || '' === $value ) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Whether the associative array or not.
	 *
	 * @see http://qiita.com/ka215/items/a14e53547e717d2a564f
	 *
	 * @param array   $data             This argument should be expected an array.
	 * @param boolean $multidimensional True if a multidimensional array is inclusion into associative array, the default value is false.
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
	 * Adding the available form field object.
	 *
	 * @param Smart_Custom_Fields_Field_Base $instance Field object.
	 */
	public static function add_form_field_instance( Smart_Custom_Fields_Field_Base $instance ) {
		$type = $instance->get_attribute( 'type' );
		if ( ! empty( $type ) ) {
			self::$fields[ $type ] = $instance;
		}
	}

	/**
	 * Getting the available form field object.
	 *
	 * @param string $type Type of the form field.
	 * @return Smart_Custom_Fields_Field_Base
	 */
	public static function get_form_field_instance( $type ) {
		if ( ! empty( self::$fields[ $type ] ) ) {
			return clone self::$fields[ $type ];
		}
	}

	/**
	 * Getting all available form field object.
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
	 * Getting custom fields that saved custo field settings page.
	 * Note that not return only one even define multiple fields with the same name of the field name.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object     Object meta object.
	 * @param string                           $field_name Field name.
	 * @return Smart_Custom_Fields_Field_Base|null
	 */
	public static function get_field( $object, $field_name ) {
		if ( '_locale' === $field_name || '_original_post' === $field_name ) {
			return null;
		}

		$settings = self::get_settings( $object );
		foreach ( $settings as $setting ) {
			$fields = $setting->get_fields();
			if ( ! empty( $fields[ $field_name ] ) ) {
				return $fields[ $field_name ];
			}
		}
	}

	/**
	 * Convert to array from newline delimiter $choices.
	 *
	 * @param string $choices Choices.
	 * @return array
	 */
	public static function choices_eol_to_array( $choices ) {
		if ( ! is_array( $choices ) ) {
			if ( '' === $choices || false === $choices || null === $choices ) {
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
	 * Return generated Setting object.
	 *
	 * @param string $id    Post ID of custom field settings page.
	 * @param string $title Title of custom field settings page.
	 * @return Smart_Custom_Fields_Setting
	 */
	public static function add_setting( $id, $title ) {
		return new Smart_Custom_Fields_Setting( $id, $title );
	}

	/**
	 * Adding custom options page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
	 *
	 * @param string $page_title The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string $menu_title The text to be used for the menu.
	 * @param string $capability The capability required for this menu to be displayed to the user.
	 * @param string $menu_slug  The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 * @param string $icon_url   The URL to the icon to be used for this menu.
	 * @param int    $position   The position in the menu order this item should appear.
	 * @return string
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
	 * Generate WP_Post object.
	 *
	 * @param int    $post_id   Post id.
	 * @param string $post_type Post type.
	 * @return WP_Post
	 */
	public static function generate_post_object( $post_id, $post_type = null ) {
		$post            = new stdClass();
		$post->ID        = $post_id;
		$post->post_type = $post_type;
		return new WP_Post( $post );
	}

	/**
	 * Generate option object.
	 *
	 * @param string $menu_slug Menu slug.
	 * @return stdClass
	 */
	public static function generate_option_object( $menu_slug ) {
		$options_pages = self::get_options_pages();
		if ( ! isset( $options_pages[ $menu_slug ] ) ) {
			return;
		}
		$option             = new stdClass();
		$option->menu_slug  = $menu_slug;
		$option->menu_title = $options_pages[ $menu_slug ];
		return $option;
	}

	/**
	 * Print cache usage.
	 *
	 * @param string $message Message.
	 */
	protected static function debug_cache_message( $message ) {
		if ( defined( 'SCF_DEBUG_CACHE' ) && SCF_DEBUG_CACHE === true ) {
			echo $message . '<br />';
		}
	}
}
