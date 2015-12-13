<?php
/**
 * Smart_Custom_Fields_Meta
 * Version    : 1.2.2
 * Author     : inc2734
 * Created    : March 17, 2015
 * Modified   : December 13, 2015
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Meta {

	/**
	 * @var WP_Post|WP_User|WP_Term
	 */
	protected $object;

	/**
	 * What meta data
	 * @var string post or user or term
	 */
	protected $meta_type = 'post';

	/**
	 * Post ID or User ID or Term ID
	 * @var int
	 */
	protected $id;

	/**
	 * Post Type or Role or Taxonomy
	 * @var string
	 */
	protected $type;

	/**
	 * @param WP_Post|WP_User|WP_Term $object
	 */
	public function __construct( $object ) {
		if ( !function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		$this->object = $object;
		if ( is_a( $object, 'WP_Post' ) ) {
			$this->id   = $object->ID;
			$this->type = $object->post_type;
			$this->meta_type = 'post';
		}
		elseif ( is_a( $object, 'WP_User' ) ) {
			$this->id   = $object->ID;
			$this->type = $object->roles[0];
			$this->meta_type = 'user';
		}
		elseif ( isset( $object->term_id ) ) {
			$this->id   = $object->term_id;
			$this->type = $object->taxonomy;
			$this->meta_type = 'term';
		}
		elseif( empty( $object ) || is_wp_error( $object ) ) {
			$this->id   = null;
			$this->type = null;
			$this->meta_type = null;
		}
		else {
			throw new Exception( sprintf( 'Invalid $object type error. $object is "%s".', get_class( $object ) ) );
		}
	}

	/**
	 * Getting the meta type
	 *
	 * @return string post or user or term
	 */
	public function get_meta_type() {
		return $this->meta_type;
	}

	/**
	 * Getting object ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Getting type ( Post type or Role or Taxonomy )
	 *
	 * @param bool $accept_revision If post type, whether allow revision post type
	 * @return string
	 */
	public function get_type( $accept_revision = true ) {
		if ( $this->meta_type === 'post' && !$accept_revision ) {
			return $this->get_public_post_type( $this->id );
		}
		return $this->type;
	}

	/**
	 * Getting post type
	 * To feel good also Post ID of the revision
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
	 * Object with this meta data is whether saved
	 * Post ... If auto-draft, not saved (new posts in)
	 * Profile or Taxonomy ... Since not display only after saving.
	 *                         So if all of meta data is empty,
	 *                         It is determined that not saved
	 *
	 * @return bool
	 */
	public function is_saved() {
		if ( $this->meta_type === 'post' && get_post_status( $this->get_id() ) === 'auto-draft' ) {
			return false;
		}
		if ( !$this->get() ) {
			return false;
		}
		return true;
	}

	/**
	 * Getting the meta data
	 *
	 * @param string $key
	 * @param bool $single false ... return array, true ... return string
	 * @return string|array
	 */
	public function get( $key = '', $single = false ) {
		// under WP 4.4 compatibility
		$maybe_4_3_term_meta = false;
		if ( $this->meta_type === 'term' ) {
			$meta = get_metadata( $this->meta_type, $this->id );
			if ( !$meta ) {
				$maybe_4_3_term_meta = true;
			}
		}

		if ( _get_meta_table( $this->meta_type ) && !$maybe_4_3_term_meta ) {
			$meta = get_metadata( $this->meta_type, $this->id, $key, $single );

			if ( $key === SCF_Config::PREFIX . 'repeat-multiple-data' ) {
				return $meta;
			}

			$settings = SCF::get_settings( $this->object );
			if ( $key ) {
				foreach ( $settings as $Setting ) {
					$fields = $Setting->get_fields();
					if ( !empty( $fields[$key] ) ) {
						return $meta;
					}
				}
			} else {
				if ( is_array( $meta ) ) {
					foreach ( $settings as $Setting ) {
						$fields = $Setting->get_fields();
						foreach ( $meta as $meta_key => $meta_value ) {
							if ( isset( $fields[$meta_key] ) ) {
								$metas[$meta_key] = $meta[$meta_key];
							}
						}
					}
				}
			}
			if ( isset( $metas ) ) {
				return $metas;
			}
			if ( $single ) {
				return '';
			}
			return array();
		} else {
			$option = get_option( $this->get_option_name() );
			if ( $key !=='' && isset( $option[$key] ) ) {
				if ( $single && is_array( $option[$key] ) ) {
					if ( isset( $option[$key][0] ) ) {
						return $option[$key][0];
					}
				} else {
					return $option[$key];
				}
			}

			if ( $key === '' && $option !== false ) {
				return $option;
			}

			// get_metadata() return entry string, so this method also same behavior
			if ( $single ) {
				return '';
			}
			return array();
		}
	}

	/**
	 * Updating meta data. If the meta data not exist, adding it.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $prev_value If specified, it overwrites the only ones of this value
	 * @return int|false Meta ID
	 */
	public function update( $key, $value, $prev_value = '' ) {
		$return = false;
		do_action( SCF_Config::PREFIX . '-before-save-' . $this->meta_type, $this->id, $key, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-' . $this->meta_type, $this->id, $key, $value );
		if ( $is_valid ) {
			if ( _get_meta_table( $this->meta_type ) ) {
				$return = update_metadata( $this->meta_type, $this->id, $key, $value, $prev_value );
			} else {
				$option_name = $this->get_option_name();
				$option = get_option( $option_name );
				if ( isset( $option[$key] ) ) {
					if ( $prev_value !== '' ) {
						foreach( $option[$key] as $option_key => $option_value ) {
							if ( $prev_value === $option_value ) {
								$option[$key][$option_key] = $value;
								break;
							}
						}
					} else {
						foreach( $option[$key] as $option_key => $option_value ) {
							$option[$key][$option_key] = $value;
						}
					}
				} else {
					$option[$key][] = $value;
				}
				$option = stripslashes_deep( $option );
				$return = update_option( $option_name, $option, false );
			}
		}
		do_action( SCF_Config::PREFIX . '-after-save-' . $this->meta_type, $this->id, $key, $value );
		return $return;
	}

	/**
	 * Adding the meta data
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $unique Whether the key to the unique
	 * @return int|false Meta ID
	 */
	public function add( $key, $value, $unique = false ) {
		$return = false;
		do_action( SCF_Config::PREFIX . '-before-save-' . $this->meta_type, $this->id, $key, $value );
		$is_valid = apply_filters( SCF_Config::PREFIX . '-validate-save-' . $this->meta_type, $this->id, $key, $value );
		if ( $is_valid ) {
			if ( _get_meta_table( $this->meta_type ) ) {
				$return = add_metadata( $this->meta_type, $this->id, $key, $value, $unique );
			} else {
				$option_name = $this->get_option_name();
				$option = get_option( $option_name );
				if ( !$unique || !isset( $option[$key] ) ) {
					$option[$key][] = $value;
					$option = stripslashes_deep( $option );
					$return = update_option( $option_name, $option, false );
				}
			}
		}
		do_action( SCF_Config::PREFIX . '-after-save-' . $this->meta_type, $this->id, $key, $value );
		return $return;
	}

	/**
	 * Deleting the meta data
	 *
	 * @param string $key
	 * @param mixed $value If specified, it deletes the only ones of this value
	 * @return bool
	 */
	public function delete( $key = '', $value = '' ) {
		if ( _get_meta_table( $this->meta_type ) ) {
			if ( $key ) {
				return delete_metadata( $this->meta_type, $this->id, $key, $value );
			}
		} else {
			if ( !$key ) {
				return false;
			}

			$option_name = $this->get_option_name();
			$option = get_option( $option_name );

			if ( isset( $option[$key] ) && $value === '' ) {
				unset( $option[$key] );
				return update_option( $option_name, $option );
			}

			if ( isset( $option[$key] ) && $value !== '' ) {
				foreach ( $option[$key] as $option_key => $option_value ) {
					if ( $option_value === $value ) {
						unset( $option[$key][$option_key] );
					}
				}
				return update_option( $option_name, $option );
			}
		}
	}

	/**
	 * Delete all term meta for less than WordPress 4.3
	 */
	public function delete_term_meta_for_wp43() {
		$option_name = $this->get_option_name();
		return delete_option( $option_name );
	}

	/**
	 * Saving the meta data based on the posted data
	 *
	 * @param array $POST
	 */
	public function save( array $POST ) {
		// For repeated multi-value items identification
		$repeat_multiple_data = array();

		// Retruning empty value when multi-value is empty, it doesn't save
		$multiple_data_fields = array();

		switch ( $this->meta_type ) {
			case 'post' :
				$object = get_post( $this->id );
				break;
			case 'user' :
				$object = get_userdata( $this->id );
				break;
			case 'term' :
				$object = get_term( $this->id, $this->type );
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
	 * Restore the data from the revision
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
			$fields = $Setting->get_fields();
			foreach ( $fields as $Field ) {
				$field_name = $Field->get( 'name' );
				$this->delete( $field_name );
				$value = SCF::get( $field_name, $revision->ID );
				if ( is_array( $value ) ) {
					foreach ( $value as $val ) {
						if ( is_array( $val ) ) {
							foreach ( $val as $v ) {
								// Repeated multi-value items
								$this->add( $field_name, $v );
							}
						} else {
							// Repeated single-value items or Non repeated multi-value items
							$this->add( $field_name, $val );
						}
					}
				} else {
					// Non repeated single-value item
					$this->add( $field_name, $value );
				}
			}
		}

		$repeat_multiple_data = SCF::get_repeat_multiple_data( $revision );
		$repeat_multiple_data_name = SCF_Config::PREFIX . 'repeat-multiple-data';
		$this->delete( $repeat_multiple_data_name );
		$this->update( $repeat_multiple_data_name, $repeat_multiple_data );
	}

	/**
	 * Getting option name for saved options table
	 */
	protected function get_option_name() {
		return sprintf(
			'%s%s-%s-%d',
			SCF_Config::PREFIX,
			$this->meta_type,
			$this->type,
			$this->id
		);
	}
}
