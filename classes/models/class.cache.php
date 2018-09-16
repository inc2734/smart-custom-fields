<?php
/**
 * Smart_Custom_Fields_Cache
 * Version    : 1.0.0
 * Author     : inc2734
 * Created    : Mau 31, 2016
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Smart_Custom_Fields_Cache {

	/**
	 * Singleton instance
	 *
	 * @var Smart_Custom_Fields_Cache
	 */
	private static $instance;

	/**
	 * Getting data proccesses is heavy. So saved getted data to $meta.
	 * Using post_id as key.
	 *
	 * @var array
	 */
	protected $meta = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $settings_posts.
	 * Using post_type as key.
	 *
	 * @var array
	 */
	protected $settings_posts = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $settings.
	 * Using post_type as key.
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Getting data proccesses is heavy. So saved getted data to $repeat_multiple_data.
	 * Using post_id as key.
	 *
	 * @var array
	 */
	protected $repeat_multiple_data = array();

	private function __construct() {}

	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new Smart_Custom_Fields_Cache();
		}
		return self::$instance;
	}

	/**
	 * Clear all caches.
	 */
	public function flush() {
		$this->clear_meta();
		$this->clear_settings_posts();
		$this->clear_settings();
		$this->clear_repeat_multiple_data();
	}

	/**
	 * Saving to cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param string                           $name
	 * @param mixed                            $data
	 */
	public function save_meta( $object, $name, $data ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( ! empty( $id ) && ! empty( $type ) && ! empty( $meta_type ) ) {
			$this->meta[ $meta_type . '_' . $type . '_' . $id ][ $name ] = $data;
		}
	}

	/**
	 * Getting the cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param string                           $name
	 * @return mixed
	 */
	public function get_meta( $object, $name = null ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( ! empty( $id ) && ! empty( $type ) && ! empty( $meta_type ) ) {
			if ( is_null( $name ) ) {
				if ( isset( $this->meta[ $meta_type . '_' . $type . '_' . $id ] ) ) {
					return $this->meta[ $meta_type . '_' . $type . '_' . $id ];
				}
			} else {
				if ( isset( $this->meta[ $meta_type . '_' . $type . '_' . $id ][ $name ] ) ) {
					return $this->meta[ $meta_type . '_' . $type . '_' . $id ][ $name ];
				}
			}
		}
	}

	/**
	 * Clear caches
	 */
	public function clear_meta() {
		$this->meta = array();
	}

	/**
	 * Saving to cache that enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param array                            $settings_posts
	 */
	public function save_settings_posts( $object, $settings_posts ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		$this->settings_posts[ $meta_type . '_' . $type ] = $settings_posts;
	}

	/**
	 * Getting cache that enabled custom field settings in the post type or the role or the term.
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return array|null
	 */
	public function get_settings_posts( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$type      = $Meta->get_type( false );
		$meta_type = $Meta->get_meta_type();
		if ( isset( $this->settings_posts[ $meta_type . '_' . $type ] ) ) {
			return $this->settings_posts[ $meta_type . '_' . $type ];
		}
	}

	/**
	 * Clear the $settings_posts
	 */
	public function clear_settings_posts() {
		$this->settings_posts = array();
	}

	/**
	 * Saving the Setting object to cache
	 *
	 * @param int                              $settings_post_id
	 * @param Smart_Custom_Fields_Setting      $Setting
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 */
	public function save_settings( $settings_post_id, $Setting, $object = null ) {
		if ( ! is_null( $object ) ) {
			$Meta      = new Smart_Custom_Fields_Meta( $object );
			$id        = $Meta->get_id();
			$meta_type = $Meta->get_meta_type();
		}
		if ( ! empty( $meta_type ) && ! empty( $id ) ) {
			$this->settings[ $settings_post_id ][ $meta_type . '_' . $id ] = $Setting;
		} else {
			$this->settings[ $settings_post_id ][0] = $Setting;
		}
	}

	/**
	 * Getting the Setting object cache
	 * If there isn't the custom field settings ... null
	 * If there is custom field settings
	 *     If there is no data for the specified $meta_type + $id
	 *         There is a thing of the General ... Smart_Custom_Fields_Setting
	 *         There isn't a thing of the General ... false
	 *     If there the data for the specified $meta_type + $id ... Smart_Custom_Fields_Setting
	 *
	 * @param int                              $settings_post_id
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return Smart_Custom_Fields_Setting|false|null
	 */
	public function get_settings( $settings_post_id, $object = null ) {
		if ( ! is_null( $object ) ) {
			$Meta      = new Smart_Custom_Fields_Meta( $object );
			$id        = $Meta->get_id();
			$meta_type = $Meta->get_meta_type();
		}

		if ( isset( $this->settings[ $settings_post_id ] ) ) {
			$settings = $this->settings[ $settings_post_id ];
			if ( ! empty( $id ) && ! empty( $meta_type ) && isset( $settings[ $meta_type . '_' . $id ] ) ) {
				return $settings[ $meta_type . '_' . $id ];
			}
			if ( isset( $settings[0] ) ) {
				return $settings[0];
			}
			return false;
		}
	}

	/**
	 * Clear the $settings
	 */
	public function clear_settings() {
		$this->settings = array();
	}

	/**
	 * Saving the delimited identification data of the repeated multi-value items to cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @param mixed                            $repeat_multiple_data
	 */
	public function save_repeat_multiple_data( $object, $repeat_multiple_data ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( ! empty( $id ) && ! empty( $type ) && ! empty( $meta_type ) ) {
			$this->repeat_multiple_data[ $meta_type . '_' . $type . '_' . $id ] = $repeat_multiple_data;
		}
	}

	/**
	 * Getting delimited identification data of the repeated multi-value items from cache
	 *
	 * @param WP_Post|WP_User|WP_Term|stdClass $object
	 * @return mixed
	 */
	public function get_repeat_multiple_data( $object ) {
		$Meta      = new Smart_Custom_Fields_Meta( $object );
		$id        = $Meta->get_id();
		$type      = $Meta->get_type();
		$meta_type = $Meta->get_meta_type();
		if ( ! empty( $id ) && ! empty( $type ) ) {
			if ( isset( $this->repeat_multiple_data[ $meta_type . '_' . $type . '_' . $id ] ) ) {
				return $this->repeat_multiple_data[ $meta_type . '_' . $type . '_' . $id ];
			}
		}
	}

	/**
	 * Clear delimited identification data of the repeated multi-value items cache
	 */
	public function clear_repeat_multiple_data() {
		$this->repeat_multiple_data = array();
	}
}
